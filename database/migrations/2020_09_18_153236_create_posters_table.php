<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 1024);
            $table->mediumText('description');
            $table->string('link', 1024);
            $table->integer('price');
            $table->integer('code');
            $table->date('update_date');
            $table->string('contact_face', 255);
            $table->string('email', 255);
            $table->string('adress', 1024);
            $table->string('city', 1024);
            $table->string('region', 1024);
            $table->string('subway', 1024);
            $table->integer('price_id');

            $table->foreign('price_id')->references('id')->on('prices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posters');
    }
}
