<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Instagram extends Model
{
    protected $table = 'instagram'; 
   
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}