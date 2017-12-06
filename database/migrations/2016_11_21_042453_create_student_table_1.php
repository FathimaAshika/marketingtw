<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentTable1 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('students', function (Blueprint $table) {
            $table->increments('std_id');
            $table->foreign('user_id')->references('id')->on('users');
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

    public function down() {
        Schema::drop('students');
    }

}
