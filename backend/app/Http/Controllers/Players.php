<?php

namespace App\Http\Controllers;

use App\Model\Player;
use App\Model\Question;
use App\Model\QuestionBuffer;
use Laravel\Lumen\Routing\Controller as BaseController;

class Players extends BaseController
{


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
}
