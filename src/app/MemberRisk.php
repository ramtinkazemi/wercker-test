<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberRisk extends Model
{
    protected $table = 'MemberRisk';
    protected $connection = "crutils";
    protected $fillable = [];
}
