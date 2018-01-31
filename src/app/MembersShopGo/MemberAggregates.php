<?php

namespace App\MembersShopGo;

use Illuminate\Database\Eloquent\Model;

class MemberAggregates extends Model
{
    //
    protected $table = 'MemberAggregates';
    protected $guarded = ['created_at'];
    protected $connection = "mysql";
}
