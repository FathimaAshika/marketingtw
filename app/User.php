<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Request;

class User extends Authenticatable {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       
        "email",
        "fullname",
        "mobile",       
        "score",
        "time_taken",
        "clicks",
        "competition_done"
    
    ];
    public    $timestamps = false;


}
