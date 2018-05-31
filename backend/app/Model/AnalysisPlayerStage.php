<?php
/**
 * Created by PhpStorm.
 * User: trickyedecay
 * Date: 2018/5/20
 * Time: 下午11:30
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class AnalysisPlayerStage extends Model
{
    protected $table = 'analysis_player_stage';

    public $timestamps = false;

    public function scopeQuestionId($query,$questionId){
        return $query->where('question_id','=',$questionId);
    }
    public function scopePlayerId($query,$playerId){
        return $query->where('player_id','=',$playerId);
    }
    public function scopeChoose($query,$choose){
        return $query->where('choose','=',$choose);
    }
}