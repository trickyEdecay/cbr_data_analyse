<?php

namespace App\Http\Controllers;

use App\Model\Player;
use App\Model\Question;
use App\Model\QuestionBuffer;
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

        }

        return response()->json([
            "player"=>$player,
            "questions"=>$questions
        ]);
    }
}
