<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password', 'insta_name', 'fb_name', 'tw_name',
    //     'yt_name', 'engagement', 'niche', 'niche', 'growth', 'age', 'height', 
    //     'country'
    // ];
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function instagram(){
        return $this->hasMany(Instagram::class, 'user_id', 'id');
    }
}
