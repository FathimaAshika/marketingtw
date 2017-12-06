<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     
        Schema::create('temp_students', function (Blueprint $table) {
            $table->increments('std_id');
            $table->string('title');
            $table->string('first_name');
            $table->string('family_name');
            $table->string('gender');
            $table->string('dob');
            $table->string('nation');
            $table->string('country');
            $table->string('email');
            $table->string('phone');
            $table->string('grade');
            $table->string('school');
            $table->string('intake_month');
            $table->string('school_type');
            $table->string('found_through');
            $table->rememberToken();
            $table->timestamps();
        });
    }
    /**
 
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('temp_students');
    }
}
