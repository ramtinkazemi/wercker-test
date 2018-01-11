<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @codeCoverageIgnore
 */
class Task extends Model
{
    //
    protected $table = 'Task';
    protected $connection = "mysql";
    protected $guarded = ['created_at'];
}
