<?php

namespace App\Http\Controllers;

use App\Model\AnalysisPlayerStage;
use App\Model\CaptchaInputTime;
use App\Model\Player;
use App\Model\Question;
use App\Model\QuestionBuffer;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Lumen\Routing\Controller as BaseController;

class Questions extends BaseController
{
    //

    public function getQuestionsPack(){


        $questions = Question::orderBy('sort')->get();
        foreach($questions as &$question){
            $question['playerAmount'] = count(QuestionBuffer::questionId($question['id'])->get());
            $question['correctPlayerAmount'] = count(QuestionBuffer::questionId($question['id'])->choose($question['randomtrue'])->get());
            $question['timeoutPlayerAmount'] = count(QuestionBuffer::questionId($question['id'])->timeout()->get());
        }
        return response()->json($questions);
    }

    public function getQuestionsPageFill(){
        Players::computePlayersStage();
        $this->computeQuestionsStatistic();

        $questions = Question::orderBy('sort')->get();
        foreach($questions as &$question){
            $question['playerAmount'] = count(QuestionBuffer::questionId($question['id'])->get());
            $question['correctPlayerAmount'] = count(QuestionBuffer::questionId($question['id'])->choose($question['randomtrue'])->get());
            $question['timeoutPlayerAmount'] = count(QuestionBuffer::questionId($question['id'])->timeout()->get());
        }
        $playerAmount = count(Player::lastActiveYear('2018')->get());
        return response()->json([
            "questionsPack"=>$questions,
            "playerAmount"=>$playerAmount
        ]);
    }

    public static function computeQuestionsStatistic(){
        $analysisQuestionsTableName = 'analysis_questions_statistic';

        // 判断问题数据分析表在不在，如果不在的话，就分析并创建这么一个表
        if(!app('db')->getSchemaBuilder()->hasTable($analysisQuestionsTableName)){
            app('db')->getSchemaBuilder()->create($analysisQuestionsTableName, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('question_id');
                $table->integer('player_amount');
                $table->integer('correct_player_amount');
                $table->integer('wrong_player_amount');
                $table->integer('timeout_player_amount');
                $table->integer('unjoin_player_amount');
                $table->float('average_player_score',5,2)->comment('玩家平均分');
                $table->float('average_player_ranking',5,2)->comment('玩家平均排名');
                $table->float('average_player_answer_time',5,2)->comment('玩家平均答题花费时间');
                $table->float('average_player_captcha_delay',5,2)->comment('玩家平均进入题目时延');
                $table->integer('max_player_score');
                $table->integer('max_player_ranking');
                $table->integer('max_player_captcha_delay')->comment('玩家进入题目最大时延');
                $table->integer('min_player_score');
                $table->integer('min_player_ranking');
                $table->integer('fast_player_id');
                $table->integer('first_correct_player_id');
                $table->integer('black_horse_player_id');
                $table->integer('high_hit_rate_player_id');
                $table->integer('players_choose_a')->comment('选A玩家数量');
                $table->integer('players_choose_b')->comment('选B玩家数量');
                $table->integer('players_choose_c')->comment('选C玩家数量');
                $table->integer('players_choose_d')->comment('选D玩家数量');
                $table->string('data_from_year');
            });

            // 取得所有年内的玩家
            $players = Player::lastActiveYear('2018')->get();
            // 取得所有问题
            $questions = Question::orderBy('sort')->get();

            foreach($questions as &$question){
                if(AnalysisPlayerStage::questionId($question['id'])->count() == 0 || QuestionBuffer::questionId($question['id'])->count() == 0){
                    continue;
                }
                $question_id = $question['id'];
                $playerAmount = QuestionBuffer::questionId($question['id'])->count();
                $correctPlayerAmount = QuestionBuffer::questionId($question['id'])->choose($question['randomtrue'])->count();
                $wrongPlayerAmount = QuestionBuffer::questionId($question['id'])->doesntChoose($question['randomtrue'])->count();
                $timeoutPlayerAmount = QuestionBuffer::questionId($question['id'])->timeout()->count();
                $unjoinPlayerAmount = count($players)-$playerAmount;
                $averagePlayersScore = AnalysisPlayerStage::questionId($question['id'])->avg('score');
                $averagePlayersRanking = AnalysisPlayerStage::questionId($question['id'])->avg('ranking');
                $averagePlayersAnswerTime = QuestionBuffer::selectRaw("avg(`done-time`-`time`) as answer_time")->questionId($question['id'])->first()['answer_time'];
                $averagePlayersCaptchaDelay = AnalysisPlayerStage::questionId($question['id'])->avg('captcha_input_delay');
                $maxPlayerScore = AnalysisPlayerStage::questionId($question['id'])->max('score');
                $minPlayerScore = AnalysisPlayerStage::questionId($question['id'])->min('score');
                $maxPlayerRanking = AnalysisPlayerStage::questionId($question['id'])->max('ranking');
                $minPlayerRanking = AnalysisPlayerStage::questionId($question['id'])->min('ranking');
                $maxPlayerCaptchaDelay = CaptchaInputTime::questionId($question['id'])->min('delay');
                $fastPlayerId = QuestionBuffer::questionId($question['id'])->oldest('time')->first()['peopleid'];
                $firstCorrectPlayerId = QuestionBuffer::questionId($question['id'])->choose($question['randomtrue'])->oldest('done-time')->first()['peopleid'];
                // 如果不存在最强黑马玩家就是-1
                $blackHorsePlayerId = -1;
                $tempBuilder = AnalysisPlayerStage::questionId($question['id'])->where('delta_ranking','>',60);
                if($tempBuilder->count()>0){
                    $blackHorsePlayerId = $tempBuilder->orderBy('delta_ranking','desc')->first()['player_id'];
                }
                $highHitRatePlayerId = AnalysisPlayerStage::questionId($question['id'])->orderBy('correct_rate','desc')->first()['player_id'];
                $playersChooseA = AnalysisPlayerStage::questionId($question['id'])->choose('A')->count();
                $playersChooseB = AnalysisPlayerStage::questionId($question['id'])->choose('B')->count();
                $playersChooseC = AnalysisPlayerStage::questionId($question['id'])->choose('C')->count();
                $playersChooseD = AnalysisPlayerStage::questionId($question['id'])->choose('D')->count();
                $dataFromYear = '2018';


                app('db')->table($analysisQuestionsTableName)->insert([
                    'question_id' => $question_id,
                    'player_amount' => $playerAmount,
                    'correct_player_amount' => $correctPlayerAmount,
                    'wrong_player_amount' => $wrongPlayerAmount,
                    'timeout_player_amount' => $timeoutPlayerAmount,
                    'unjoin_player_amount' => $unjoinPlayerAmount,
                    'average_player_score' => $averagePlayersScore,
                    'average_player_ranking' => $averagePlayersRanking,
                    'average_player_answer_time' => $averagePlayersAnswerTime,
                    'average_player_captcha_delay' => $averagePlayersCaptchaDelay,
                    'max_player_score' => $maxPlayerScore,
                    'max_player_ranking' => $maxPlayerRanking,
                    'max_player_captcha_delay' => $maxPlayerCaptchaDelay,
                    'min_player_score' => $minPlayerScore,
                    'min_player_ranking' => $minPlayerRanking,
                    'fast_player_id' => $fastPlayerId,
                    'first_correct_player_id' => $firstCorrectPlayerId,
                    'black_horse_player_id' => $blackHorsePlayerId,
                    'high_hit_rate_player_id' => $highHitRatePlayerId,
                    'players_choose_a' => $playersChooseA,
                    'players_choose_b' => $playersChooseB,
                    'players_choose_c' => $playersChooseC,
                    'players_choose_d' => $playersChooseD,
                    'data_from_year' => $dataFromYear,
                ]);
            }
        }
    }
}
