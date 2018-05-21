<?php
/**
 * Created by PhpStorm.
 * User: trickyedecay
 * Date: 2018/5/20
 * Time: 下午11:30
 */

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'question';

    public $timestamps = false;
}