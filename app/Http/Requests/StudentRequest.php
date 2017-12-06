<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class StudentRequest extends Request {

    public function authorize() {
        return true;
    }

    public function rules() {
        return [
//            "title" => "required",
//            "fname" => "required",
//            "family_name" => "required",
//            "gender" => "required",
//            "dd" => "required",
//            "mm" => "required",
//            "yy" => "required",
//            "nation" => "required",
//            "country" => "required",
//            "email" => "required",
//            "phone" => "required",
//            "grade" => "required",
//            "school" => "required",
//            "intake" => "required",
//            "school_type" => "required",
//            "find" => "required",
//            "username" => "required",
//            "password" => "required"
        ];
    }

}
