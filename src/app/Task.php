<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $table = 'Task';
    protected $connection = "mysql";
    protected $guarded = ['created_at'];
    public $incrementing = false;
    public $keyType = "string";
    protected $primaryKey = 'ServiceName';
}
