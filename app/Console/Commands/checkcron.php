<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;
use Carbon\Carbon;



class checkcron extends Command
{
    
    protected $signature   = 'check:cron';
    protected $description = 'Command description';

    
    public function __construct() {
        parent::__construct();
    }
    
   
    public function handle() {
        $start_time = Carbon::now()->toDateTimeString(); 
        $this->info("Process started at ".$start_time);
    }
    
    
}
