<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
//    protected $primaryKey="id";
    protected $table='user';
    protected $guarded =[];
    public $timestamps = false;
}
