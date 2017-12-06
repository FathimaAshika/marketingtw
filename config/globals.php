<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Defined Variables
    |--------------------------------------------------------------------------
    |
    | This is a set of variables that are made specific to this application
    | that are better placed here rather than in .env file.
    | Use config('your_key') to get the values.
    |
    */
    'login_url'  => 'http://35.154.18.131:8003/login/',
    'signup_url' =>'http://35.154.18.131:8003/sign-up/',
    "star_types" => array("SUBJECT_TEST", 
                             "MODULE_TEST", 
                             "TUTOR_CHALLENGE",
                             "SECOND_FORM_FILL_UP",
                             "REPORT_ABUSE"),
    'sck_admin_notif' => 'http://192.168.1.51:8005'.'/admin/reminder',

];