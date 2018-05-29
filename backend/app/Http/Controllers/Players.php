<?php

namespace App\Http\Controllers;

use App\Model\CaptchaInputTime;
use App\Model\Player;
use App\Model\Question;
use App\Model\QuestionBuffer;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Lumen\Routing\Controller as BaseController;

class Players extends BaseController
{

    // 读取玩家统计页面信息
    public function getPlayersPageFill(){

        $players = Player::lastActiveYear('2018')->get();
        foreach($players as &$player){
            if($player['rightcount']+$player['wrongcount'] == 0){
                $player['correctRate'] = 0.00;
            }else{
                $player['correctRate'] = round($player['rightcount']/($player['rightcount']+$player['wrongcount'])*10000)/10000;
            }
            $player['joinQuestionCount'] = QuestionBuffer::playerId($player['id'])->count();
        }
        $playerAmount = Player::lastActiveYear('2018')->count();
        return response()->json([
            "players"=>$players,
            "playerAmount"=>$playerAmount
        ]);
    }

    // 获取某个具体玩家的数据页面信息
    public function getPlayerPageFill($playerId){
        $player = Player::playerId($playerId)->first();

        $questions = Question::orderBy('sort')->get();
        foreach($questions as &$question){
            $question['playerAmount'] = count(QuestionBuffer::questionId($question['id'])->get());
            $buffer = QuestionBuffer::questionId($question['id'])->playerId($playerId)->first();
            if($buffer == null){
                $question['playerState'] = "unjoin";
            }else{
                $question['playerState'] = $buffer['state'];
                if($buffer['state'] == "done"){
                    $question['isCorrect'] = $buffer['choose'] == $question['randomtrue'];
                }
                // 最快手速玩家id
                $question['theFastPlayerId'] = QuestionBuffer::questionId($question['id'])->orderBy('time','asc')->first()['peopleid'];
                // 最先答对玩家id
                $question['theFirstCorrectPlayerId'] = QuestionBuffer::questionId($question['id'])->choose($question['randomtrue'])->orderBy('done-time','asc')->first()['peopleid'];
                // 最先答对玩家id
                $question['theFirstCorrectPlayerId'] = QuestionBuffer::questionId($question['id'])->choose($question['randomtrue'])->orderBy('done-time','asc')->first()['peopleid'];
                // 计算那一题的最强黑马

            }
        }

        $this->computePlayersStage();

        return response()->json([
            "player"=>$player,
            "questions"=>$questions
        ]);
    }

    // 计算所有玩家在每一道题目时的数据
    public function computePlayersStage(){
        $analysisPlayersStageTableName = 'analysis_player_stage';

        // 判断玩家数据分析表在不在，如果不在的话，就分析并创建这么一个表
        if(!app('db')->getSchemaBuilder()->hasTable($analysisPlayersStageTableName)){
            app('db')->getSchemaBuilder()->create($analysisPlayersStageTableName, function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('player_id');
                $table->integer('question_id');
                $table->integer('right_count');
                $table->integer('wrong_count');
                $table->float('correct_rate',4,2);
                $table->integer('score');
                $table->integer('delta_score')->comment('这一次的分数减去上一次的分数');
                $table->integer('ranking');
                $table->integer('delta_ranking')->comment('这一次的排名减去上一次的排名');
                $table->string('choose');
                $table->dateTime('join_question_time');
                $table->dateTime('question_done_time');
                $table->dateTime('captcha_input_time');
                $table->integer('captcha_input_delay');
                $table->string('question_state');
                $table->string('data_from_year');
            });

            // 取得所有年内的玩家
            $players = Player::lastActiveYear('2018')->get();
            // 取得所有问题
            $questions = Question::orderBy('sort')->get();
            foreach($players as &$player){
                $historyScoreArray = explode(",",$player['historyscore']);
                // 过滤掉空的项目
                array_filter($historyScoreArray,function($item){
                    if($item == ""){
                        return false;
                    }
                });
                $historyRankingArray = explode(",",$player['historyranking']);
                // 过滤掉空的项目
                array_filter($historyRankingArray,function($item){
                    if($item == ""){
                        return false;
                    }
                });

                // 答对的数量和答错的数量记录
                $rightCount = 0;
                $wrongCount = 0;
                $playerChoose = '';
                $joinQuestionTime = '';
                $questionDoneTime = '';
                $questionState = 'unjoin';
                $captchaInputTime = '';
                $captchaInputDelay = '';
                // 根据题目分数数据来记录每一道题目的数据
                for($i=0;$i<count($historyScoreArray);$i++){

                    $buffer = QuestionBuffer::questionId($questions[$i]['id'])->playerId($player['id'])->first();
                    if(!$buffer==null){

                        // 玩家在这道题选择了什么
                        $playerChoose = $buffer['choose'] == null ? '' : $buffer['choose'];
                        // 记录相关的数据
                        $joinQuestionTime = $buffer['time'];
                        $questionDoneTime = $buffer['done-time'];
                        $questionState = $buffer['state'];
                        // 记录验证码相关资料
                        $captchaInfo = CaptchaInputTime::questionId($questions[$i]['id'])->playerId($player['id'])->first();
                        $captchaInputTime = $captchaInfo['idcinputtime'];
                        $captchaInputDelay = $captchaInfo['delay'];

                        // 当玩家有选择东西的时候才能计算答对或答错
                        if($playerChoose != null && $playerChoose != ""){
                            if($playerChoose == $questions[$i]['randomtrue']){
                                $rightCount++;
                            }else{
                                $wrongCount++;
                            }
                        }
                    }

                    $correctRate = 0;
                    if($rightCount+$wrongCount > 0){
                        $correctRate = round($rightCount/($rightCount+$wrongCount),2);
                    }

                    $nowScore = $historyScoreArray[$i];
                    $nowRanking = $historyRankingArray[$i];
                    if($i==0){
                        $deltaScore = 0;
                        $deltaRanking = 0;
                        $questionId =  -1;
                    }else{
                        $deltaScore= $historyScoreArray[$i]-$historyScoreArray[$i-1];
                        $deltaRanking= $historyRankingArray[$i]-$historyRankingArray[$i-1];
                        $questionId = $questions[$i]['id'];
                    }

                    // 将玩家的每一道题的数据插入到数据库中
                    app('db')->table($analysisPlayersStageTableName)->insert([
                        'name' => $player['name'],
                        'player_id' => $player['id'],
                        'question_id' => $questionId,
                        'right_count' => $rightCount,
                        'wrong_count' => $wrongCount,
                        'correct_rate' => $correctRate,
                        'score' => $nowScore,
                        'delta_score' => $deltaScore,
                        'ranking' => $nowRanking,
                        'delta_ranking' => $deltaRanking,
                        'choose' => $playerChoose,
                        'join_question_time' => $joinQuestionTime,
                        'question_done_time' => $questionDoneTime,
                        'captcha_input_time' => $captchaInputTime,
                        'captcha_input_delay' => $captchaInputDelay,
                        'question_state' => $questionState,
                        'data_from_year' => '2018',
                    ]);
                }

            }
        }
        return ;
    }
}
