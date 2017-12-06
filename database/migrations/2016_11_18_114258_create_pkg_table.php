<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePkgTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('package', function (Blueprint $table) {
            $table->increments('pkg_id');
            $table->string('std_id');
            $table->string('syllabus');
            $table->string('student_grade');
            $table->string('payment_method');
            $table->string('stream');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::drop('package');
    }

}
