<?php

namespace App\Console;

//use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    
    
    var $log_file = array(
                        "TIME_ON_PORTAL_LOG" => "logs/process_time_on_portal.log",
                        "PRIZES_LOG"         => "logs/prizes_review.log",
                        "PAYMENT_NOTIF_LOG"  => "logs/payment_notification.log",
                        "WEEKLY_REVIEW_LOG"  => "logs/weekly_review.log",
                        "MONTHLY_REVIEW_LOG" => "logs/monthly_review.log",
                        "YEARLY_REVIEW_LOG"  => "logs/yearly_review.log",        
                        "BIRTHDAY_LOG"       => "logs/send_birthday.log",                         
                        "PLANNER_NOTIF_LOG"  => "logs/planner_notif.log",  
                        "COUPON_LOG"         => "logs/coupon.log",
                    );
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [        
        //'App\Console\Commands\WeeklyMailout',
        //'App\Console\Commands\MonthlyReviewEmail',
        //'App\Console\Commands\AnnualReview',  
        'App\Console\Commands\ProcessPortaltime',
        'App\Console\Commands\calculatePrize',        
        'App\Console\Commands\NotificationEmails',
        'App\Console\Commands\StudentWeeklyReview', 
        'App\Console\Commands\StudentMonthlyReview',
        'App\Console\Commands\StudentYearlyReview',
        'App\Console\Commands\sendBirthdayWishes',
        'App\Console\Commands\remindPlanner',
        'App\Console\Commands\CouponGenarator',
        // Commands\Inspire::class,
    ];
    

    
    /**
     * Define the application's command schedule.
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {           
        /*                
        $schedule->command('remind:planner_notif')
                            ->sendOutputTo($this->log_file["PLANNER_NOTIF_LOG"])
                            ->cron('* * * * *')
                            ->withoutOverlapping(); 
        */
        //checked----------------------------------
        /*
        $schedule->command('check:cron')
                           ->sendOutputTo($this->log_file["CRON_CHECK_LOG"])                           
                           ->cron('* * * * *');
        */
        $schedule->command('process:time_on_portal')
                           ->sendOutputTo($this->log_file["TIME_ON_PORTAL_LOG"])
                           ->emailOutputTo('lakshikasur@gmail.com')
                           //->cron('* * * * *');
                           ->dailyAt('23:00'); 
        
        $schedule->command('calc:student_prizes')
                           ->sendOutputTo($this->log_file["PRIZES_LOG"])
                           ->dailyAt('24:00');
        
        $schedule->command('sendmail:notification')
                          ->sendOutputTo($this->log_file["PAYMENT_NOTIF_LOG"])
                          ->dailyAt('1:00');                     
       
        //Perfomanace Review Reports
        $schedule->command('review:send_weekly')
                           ->sendOutputTo($this->log_file["WEEKLY_REVIEW_LOG"]) 
                           ->cron('59 23 * * 7');
        
         $schedule->command('send:birthdaywishes')
                           ->sendOutputTo($this->log_file["BIRTHDAY_LOG"])
                           ->emailOutputTo('lakshikasur@gmail.com')
                           ->dailyAt('2:00'); 
        
         
        //Perfomanace Review Reports - need to fix times
        //----------------------------------------------
        /*        
        $schedule->command('review:send_monthly')->monthly();
        $schedule->command('review:send_yearly')->cron('* * * * *');
        */          
        //->sendOutputTo('/home/st_lara_portal_log.log')          
        //$schedule->command('sendmail:weeklyreport')->cron('11:45');
        //$schedule->command('sendmail:monthlyreview')->cron('* * * * *');
        //$schedule->command('sendmail:annualreview')->cron('* * * * *'); 
        //$schedule->command('sendmail:notification')->cron('* * * * *');
        // $schedule->command('inspire')->hourly();
    }
}
