<?php

return [


    'driver' => env('MAIL_DRIVER'),

    'host' => env('MAIL_HOST'),
    'port' => env( 'MAIL_PORT'),

    'from' => array('address' => 'info@tutorwizard.lk', 'name' => 'Tutor Wizard'),

    'encryption' => env('MAIL_ENCRYPTION'),

    'username' => env('MAIL_USERNAME'),

    'password' => env('MAIL_PASSWORD'),
    'sendmail' => '/usr/sbin/sendmail -bs',

];
