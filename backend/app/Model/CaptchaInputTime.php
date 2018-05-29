<?php
/**
 * Created by PhpStorm.
 * User: trickyedecay
 * Date: 2018/5/20
 * Time: 下午11:30
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class CaptchaInputTime extends Model
{
    protected $table = 'question_idcinputtime';

    public $timestamps = false;

    public function scopeQuestionId($query,$questionId){
        return $query->where('questionid','=',$questionId);
    }

    public function scopePlayerId($query, $playerId){
        return $query->where('peopleid','=',$playerId);
    }

    public function scopeFastPlayerOfQuestion($query,$questionId,$choose){
        return $query->where('questionid','=',$questionId)->orderBy('idcinputtime','asc');
    }

}