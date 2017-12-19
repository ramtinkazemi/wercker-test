<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    //
    protected $table = 'TaskLog';
    protected $connection = "mysql";
    protected $guarded = ['created_at'];
}
