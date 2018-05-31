<?php
/**
 * Created by PhpStorm.
 * User: trickyedecay
 * Date: 2018/5/20
 * Time: 下午11:30
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class QuestionBuffer extends Model
{
    protected $table = 'question_buffer';

    public $timestamps = false;

    public function scopeQuestionId($query,$questionId){
        return $query->where('questionid','=',$questionId);
    }

    public function scopePlayerId($query, $playerId){
        return $query->where('peopleid','=',$playerId);
    }

    public function scopeChoose($query,$choose){
        return $query->where('choose','=',$choose);
    }
    public function scopeDoesntChoose($query,$choose){
        return $query->where('choose','<>',$choose)->whereNotNull('choose');
    }

    public function scopeTimeout($query){
        return $query->where('state','=','timeout');
    }
}