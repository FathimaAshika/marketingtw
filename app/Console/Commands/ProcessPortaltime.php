<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

use DB;
use App\Student;
use Session;
use Carbon\Carbon;
use App\QuizModel;
use App\PerformReviewModel;


//php artisan process:time_on_portal

class ProcessPortaltime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:time_on_portal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct(PerformReviewModel $perform_model, QuizModel $quiz_model)
    {
        parent::__construct();                  
        $this->perform_model  = new $perform_model;
        $this->quiz_model     = new $quiz_model;    
    }

    /**
     * Execute the console command.
     * @return mixed
     */    
    
    function createDateRangeArray($strDateFrom, $strDateTo) {
        $aryRange = array();
        $iDateFrom = mktime(1,0,0,substr($strDateFrom,5,2), substr($strDateFrom,8,2),substr($strDateFrom,0,4));
        $iDateTo   = mktime(1,0,0,substr($strDateTo,5,2),   substr($strDateTo,8,2),substr($strDateTo,0,4));
        
        if ( $iDateTo >= $iDateFrom ) {
            array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo) {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('Y-m-d',$iDateFrom));
            }
        }
        return $aryRange;
    }
    
    
    public function getInterval( $st_date, $end_date ) {        
        $startDate    = Carbon::parse($st_date);
        $endDate      = Carbon::parse($end_date);        
        $diff_seconds = strtotime($endDate->toDateTimeString()) - strtotime($startDate->toDateTimeString());
        return $diff_seconds;
    }
    
    
    public function handle() {  
        $ecd       = Carbon::now()->toDateTimeString();
        $sess_arr  = $this->perform_model->getStudentsSessionData($ecd); 
        $formatArr = array();        
        //Format Data
        if($sess_arr) {
            foreach( $sess_arr as $dv ) {
               $formatArr[$dv->curriculum_id][$dv->grade_id][$dv->student_id][$dv->subject_id][$dv->id]["begin_at"] = $dv->begin_at;
               $formatArr[$dv->curriculum_id][$dv->grade_id][$dv->student_id][$dv->subject_id][$dv->id]["end_at"]   = $dv->end_at;           
            }            
            //Calculate Portal on time
            $insertArr = array();
            foreach( $formatArr as $cid => $cdata ) {
              foreach( $cdata as $gid => $gdata ) {
                 foreach( $gdata as $stu_id => $stdata ) {
                    foreach( $stdata as $sjid => $sjdata ) {
                        foreach($sjdata as $recid => $tdata ) {
                            $rangeData = $this->createDateRangeArray($tdata["begin_at"], $tdata["end_at"]);
                            $day_count = count($rangeData);
                            if( $day_count == 1 ) {
                               $interval = $this->getInterval($tdata["begin_at"], $tdata["end_at"] );
                               $insertArr[$cid][$gid][$stu_id][$sjid][$recid][$rangeData[0]] = $interval; 
                            } elseif($day_count > 1) {                                
                                $track_time = $rangeData[0]." 23:59:59";
                                $intl1      =  $this->getInterval($tdata["begin_at"], $track_time );
                                $insertArr[$cid][$gid][$stu_id][$sjid][$recid][$rangeData[0]] = $intl1;
                                
                                if( $day_count > 2 ) {
                                   for( $i=1; $i < $day_count-1; $i++ ) {
                                      $intl = 24*60*60;
                                      $insertArr[$cid][$gid][$stu_id][$sjid][$recid][$rangeData[$i]] = $intl;
                                   } 
                                }                                
                                $intl2      =  $this->getInterval( $rangeData[$day_count-1], $tdata["end_at"] );
                                $insertArr[$cid][$gid][$stu_id][$sjid][$recid][$rangeData[$day_count-1]] = $intl2;                              
                            } 
                            
                        }                        
                    }                     
                 } 
              }  
            }
            //Insert data----------------------------            
            foreach($insertArr as $curiId=>$curi_data ) {
                foreach( $curi_data as $grId=>$grade_data ) {
                    foreach( $grade_data as $stdtId=>$std_data ) {
                        foreach( $std_data as $subjId=>$subj_data ) {
                            foreach( $subj_data as $tmp_rec_id=>$date_data ) {
                                foreach( $date_data as $p_date=>$time_val) {
                                    if($time_val > 0 ) 
                                      $this->perform_model->insertPortalSpendTime($curiId, $grId, $stdtId, $subjId, $time_val, $p_date );                                                                  
                                }  
                                $this->perform_model->deleteProcessedRec($tmp_rec_id);
                            }
                        }
                    }
                }                
            }            
        }       
      
        $this->info('Successfully Completed!');
    }
    
        
    
    
}
