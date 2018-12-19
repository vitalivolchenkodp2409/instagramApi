<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();            
            $table->integer('followers');
            $table->integer('avg_followers')->default(0);
            $table->integer('following');
            $table->integer('avg_following')->default(0);
            $table->integer('likes');
            $table->integer('avg_likes')->default(0);
            $table->integer('comments');
            $table->integer('avg_comments')->default(0);
            $table->integer('post');
            $table->integer('avg_post')->default(0);
            $table->float('engagement', 8, 2);
            $table->float('avg_engagement', 8, 2)->default(0);
            $table->string('avatar');
            $table->string('l_image');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instagram');
    }
}
