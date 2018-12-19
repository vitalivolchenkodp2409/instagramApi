<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique()->nullable();
            $table->bigInteger('insta_id')->unique()->nullable();            
            $table->string('access_token')->unique()->nullable();            
            $table->string('email')->unique()->nullable();            
            $table->string('password')->nullable();
            $table->string('insta_name')->unique()->nullable();
            $table->string('fb_name')->unique()->nullable();
            $table->string('tw_name')->unique()->nullable();
            $table->string('yt_name')->unique()->nullable();
            $table->string('engagement')->nullable();
            $table->string('niche')->nullable();
            $table->string('growth')->nullable();
            $table->integer('age')->nullable();
            $table->integer('height')->nullable();
            $table->string('country')->nullable();          
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
