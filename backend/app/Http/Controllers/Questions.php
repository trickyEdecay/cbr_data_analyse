<?php

namespace App\Http\Controllers;

use App\Model\Player;
use App\Model\Question;
use App\Model\QuestionBuffer;
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
}
