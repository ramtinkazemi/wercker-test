<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnvVariables extends Model
{
    protected $connection = 'mysql';
    protected $table = 'EnvVariables';
    protected $guarded = ['created_at'];
}
