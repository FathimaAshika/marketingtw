<?php
namespace App\Traits;
use App;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;


trait PortalSummary {       
    
    public function getInterval( $st_date, $end_date, $cutoff_sdate, $cutoff_edate ) {        
        $startDate    = Carbon::parse($st_date);
        $endDate      = Carbon::parse($end_date);
        $cutoff_sDate = Carbon::parse($cutoff_sdate);
        $cutoff_eDate = Carbon::parse($cutoff_edate);
        
        if( $cutoff_eDate->lt($endDate) )       
            $endDate = $cutoff_eDate;
        if( $cutoff_sDate->gt($startDate) )       
            $startDate = $cutoff_sDate;        
        $diff_seconds = strtotime($endDate->toDateTimeString()) - strtotime($startDate->toDateTimeString());        
        
        return $diff_seconds;
    }
    
    
    public function secondsToTime($seconds) {
        $hours = floor($seconds / (60 * 60));        
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
        $divisor_for_seconds = $divisor_for_minutes % 60;        
        $seconds = ceil($divisor_for_seconds);
        $obj = array(
            "h" => (int) $hours,
            "m" => (int) $minutes,
            "s" => (int) $seconds,
        );
        return $obj;
    }
    
    
    public function format_time($tobj) {
        if($tobj['h'] < 10)
            $tobj['h'] = "0".$tobj['h'];
        if($tobj['m'] < 10)
            $tobj['m'] = "0".$tobj['m'];
        if($tobj['s'] < 10)
            $tobj['s'] = "0".$tobj['s'];
        
       return $tobj['h'].":".$tobj['m'].":".$tobj['s'];
    }
    
    
    public function format_time_nosec($tobj) {
        if($tobj['h'] < 10)
            $tobj['h'] = "0".$tobj['h'];
        if($tobj['m'] < 10)
            $tobj['m'] = "0".$tobj['m'];
       return $tobj['h'].":".$tobj['m'];
    }
    
    
    public function processStars($stars, $avl_sujs) {
        $fmt_star    = array();
        $str_sum     = array();
        $total_stars = 0;
        $avl_sujs    = $this->mail_model->loadResultSet($avl_sujs, "subject_id");
        
        if($stars) {
            foreach( $stars as $st ) {
                $fmt_star[$st->subject_id][$st->id] = $st->stars + $st->time_bonus;
            }            
            $star_sum = array();
            foreach( $fmt_star as $subid => $sj_stars ) {
                $subj_stars = 0;
                foreach( $sj_stars as $kid=>$sval) {
                    $subj_stars  = $subj_stars + $sval;
                    $total_stars = $total_stars + $sval;
                }
                $star_sum[$subid] = $subj_stars;
            }
        }        
        $i = 0;
        foreach( $avl_sujs as $subid => $sj ) {
           $str_sum["subject_star"][$i]["subject_id"]= $subid;  
           $str_sum["subject_star"][$i]["subject"]   = $avl_sujs[$subid]->subject_name;  
           $str_sum["subject_star"][$i]["color"]     = $avl_sujs[$subid]->color; 
           $str_sum["subject_star"][$i]["stars"]     = isset($star_sum[$subid]) ? $star_sum[$subid]:0;         
           $i++; 
        }
        $str_sum["total_star"] = $total_stars;
        
        return $str_sum;  
    }    

    
    public function getTimeOnPortal($stud_id, $st_date, $ed_date, $cur_id, $grade_id, $pkg_id ) {    
        $processData  = array();
        $tempData = array(); 
        $darr = $this->perform_model->filterTimeOnPortalByDateRange($stud_id, $st_date, $ed_date, $cur_id, $grade_id ); 
        if($darr) {
            foreach($darr as $st) { 
                if( isset($tempData[$st->subject_id]["temp_time"]) ) {
                   $time_val = $tempData[$st->subject_id]["temp_time"]; 
                } else {
                   $time_val = 0;
                }                    
                $interval = $this->getInterval( $st->begin_at, $st->end_at, $st_date, $ed_date );                  
                $tempData[$st->subject_id]["temp_time"] = $interval + $time_val;  
            } 
        }                 
        $processed_arr = $this->perform_model->getProcessedTimeOnPortal($stud_id, $st_date, $ed_date, $cur_id, $grade_id); 
        if($processed_arr) {
            foreach( $processed_arr as $pv ) {
                if(isset($processData[$pv->subject_id]["process_time"])) 
                   $time_val = $processData[$pv->subject_id]["process_time"];
                else 
                  $time_val = 0;                    
                $processData[$pv->subject_id]["process_time"] = $pv->on_portal_secs + $time_val ;                            
            }
        }  
        $i = 0;
        $total_val  = 0;
        $final_arr  = array();
        $return_arr = array();
        $avail_subj = DB::select('CALL spGetPackageSubject(?)', array($pkg_id)); 
         
        foreach( $avail_subj as $subj) {
            $time_temp     = 0;
            $process_time  = 0;
            $full_time     = 0;
            $time_str      = "00:00:00";
            
            if( isset($tempData[$subj->subject_id]["temp_time"] ) ) 
                $time_temp = $tempData[$subj->subject_id]["temp_time"];                
            if( isset($processData[$subj->subject_id]["process_time"]) ) 
                $process_time = $processData[$subj->subject_id]["process_time"];
            
            $full_time = $time_temp + $process_time;
            $total_val = $total_val + $full_time;
           // if($full_time)
               $time_str = $this->format_time_nosec($this->secondsToTime($full_time));                       

            $final_arr[$i]["subject_id"]   = $subj->subject_id;
            $final_arr[$i]["subject_name"] = $subj->subject_name;
            $final_arr[$i]["color"]        = $subj->color;  
            $final_arr[$i]["portal_time"]  = $time_str; 
            $final_arr[$i]["portal_secs"]  = $full_time; 
            $final_arr[$i]["portal_hours"] = round($full_time/3600, 0); 
            $i++;
        }
        $return_arr["total_time"]   = $this->format_time_nosec($this->secondsToTime($total_val));  
        $return_arr["subject_time"] = $final_arr;
        
        return $return_arr;
    }    
    
    
    public function getMostActiveDay($stu_id, $st_dt, $ed_dt, $curr_id, $grade_id) {      
        $sql = "SELECT time_on_portal_date, SUM(on_portal_secs) as ptime  FROM learning_session "
                ." WHERE curriculum_id=".$curr_id." AND grade_id=".$grade_id
                ." AND time_on_portal_date >= '".$st_dt
                ."' AND time_on_portal_date <= '".$ed_dt."' AND student_id =".$stu_id
                ." GROUP BY time_on_portal_date ORDER BY ptime DESC LIMIT 1";
        $data = DB::select($sql);       
        return isset($data[0])?$data[0]:array();
    } 
    
    
    public function processModuleTestData($mscores, $avl_sujs) {
        $mt_arr = array();
        $avl_sujs    = $this->mail_model->loadResultSet($avl_sujs, "subject_id");
        if($mscores) {
            $format_mdl = array();
            foreach( $mscores as $mdata ) {
               $format_mdl[$mdata->subject_id][$mdata->module_id][$mdata->id] = $mdata->full_marks;               
            }
            $i = 0;
            foreach( $format_mdl as $subid => $sval ) {                
                foreach( $sval as $modId => $modval ) {
                    $module_detail = $this->mail_model->getModuleDetails($modId);                    
                    $total_marks = 0;
                    foreach( $modval as $id => $marks ) {
                        $total_marks = $total_marks + $marks;
                    }  
                    $test_count    = count($modval);
                    if( $test_count > 0 ) {
                        $average_marks = $total_marks/$test_count;
                    } else {
                        $average_marks = 0;
                    }
                    
                    $mt_arr[$i]["subject_id"]    = $subid;
                    $mt_arr[$i]["subject_name"]  = $avl_sujs[$subid]->subject_name;
                    $mt_arr[$i]["color"]         = $avl_sujs[$subid]->color;
                    $mt_arr[$i]["module_name"]   = isset($module_detail[0]->name)?$module_detail[0]->name:'';
                    $mt_arr[$i]["times_taken"]   = $test_count;                    
                    $mt_arr[$i]["average_marks"] = $this->format_marks($average_marks);
                    $i++;
                } 
            }
        }  
        return $mt_arr;        
    }         
    
    
    
    public function format_marks($marks) {
        return number_format((float)$marks, 0, '.', ''); 
    }
    
    
    public function getSubjectsTestResults($stu_id, $st_date, $end_date, $grade_id, $curr_id) {       
        $final_data = array();        
        if( $this->availsubjects ) {
            $scount = 0;
            foreach( $this->availsubjects as $sj ) { 
                $avg_marks  = 0;
                $subj_total = 0;
                $t_count    = 0;
                $subj_test  = $this->mail_model->getLatestSubjectTests($stu_id, $sj->subject_id, $st_date, $end_date, $grade_id, $curr_id);
                $format     = array();
                foreach( $subj_test as $res ) {
                   $format[$t_count] = $res->full_marks;
                   $subj_total       =  $subj_total + $res->full_marks;
                   $t_count++;                    
                } 
                $test_count = count($subj_test);
                if( $test_count > 0 ) 
                    $avg_marks = $subj_total/$test_count;
                else 
                    $avg_marks = 0;

                $final_data[$scount]["subject_name"]  = $sj->subject_name;
                $final_data[$scount]["subject_color"] = $sj->color;
                $final_data[$scount]["avg_marks"]     = number_format($avg_marks, 0);
                $final_data[$scount]["latest_test"]   = $format;
                $scount++;               
            } 
        } 
        return $final_data;
    } 
    
    
    public function getSpeedBonus($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
        $total_bonus = 0;
        $formatSubj = array();
        $bonus_arr  = array();
        $all_test = $this->mail_model->getAllTestDataByStudentId($stu_id, $st_date, $end_date, $grade_id, $curr_id);
         
        if($all_test) {
            foreach( $all_test as $qval ) {
                $formatSubj[$qval->subject_id][$qval->id] = $qval->speed_bonus;
            }
            foreach( $formatSubj as $subj_id => $sd ) {
                $time_bonus = 0;
                foreach( $sd as $sbval) {
                    $time_bonus = $time_bonus + $sbval;
                }                 
                $total_bonus = $total_bonus + $time_bonus;
                $bonus_arr[$subj_id] = $time_bonus;
            }
        }
        $count = 0;                
        $node  = array();
        $final = array();
        foreach($this->availsubjects as $sj ) {
            $spbonus = isset($bonus_arr[$sj->subject_id])? $bonus_arr[$sj->subject_id]:0;
            $node[$count]["subject_id"]   = $sj->subject_id;
            $node[$count]["subject_name"] = $sj->subject_name;
            $node[$count]["color"]        = $sj->color;          
            $node[$count]["bonus"]        = $spbonus;
            $count ++;
        } 
        $final["overall_bonus"] = $total_bonus;
        $final["subject_bonus"] = $node; 
        
        return $final;        
    }
    
           
    public function getBestTest( $stu_id, $st_date, $end_date, $grade_id, $curr_id) {
        $all_test  = $this->mail_model->getBestTestByStudentId($stu_id, $st_date, $end_date, $grade_id, $curr_id);
        $final_arr = array();
        if($all_test) {
            $subj = DB::select('CALL spGetSubjectById(?)', array($all_test->subject_id));
            if( $all_test->type == "SUBJECT_TEST" ) {
                $name_val = $subj[0]->name;
            } elseif( $all_test->type == "MODULE_TEST" ) {
                $mod = $this->mail_model->getModuleDetails($all_test->module_id);
                $name_val = $mod[0]->name;
            } else {
                $name_val = "";
            }            
            $final_arr["full_marks"] = $all_test->full_marks ;
            $final_arr["time_taken"] = Carbon::createFromFormat('H:i:s', $all_test->time_taken)->format('H:i');
            $final_arr["name_value"] = $name_val;
            $final_arr["color"]      = $subj[0]->color;
        }
        return $final_arr;
    }       
     
     
    function print_x($arr , $text ) {
        echo "------------".$text."-----Start";
        print_r($arr);
        echo "------------".$text."-----end";
    }


    public function getStrengthWeekness($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
        $sw_arr = $this->mail_model->getAllSubjectTests($stu_id, $st_date, $end_date, $grade_id, $curr_id);
        if($sw_arr) {
            $format_data = array();
            foreach( $sw_arr as $swv) {
                $format_data[$swv->subject_id][$swv->id] = $swv->full_marks;
            }              
        }
        $count = 0; 
        $node  = array();                    
        foreach($this->availsubjects as $sj ) {
            $total_mark = 0;
            $avg_mark   = 0;
            if(isset($format_data[$sj->subject_id])) {
                $text_count = count($format_data[$sj->subject_id]);
                foreach( $format_data[$sj->subject_id] as $marks ) {
                    $total_mark = $total_mark + $marks;
                }
                if( $text_count > 0) {
                    $avg_mark = $total_mark/$text_count;
                } else {
                    $avg_mark = 0;
                }
                  
                if($avg_mark >= 70) {
                    $status_code = 3; 
                } elseif( $avg_mark >= 40 && $avg_mark < 70 ) {
                    $status_code = 2;
                } else {
                    $status_code = 1;
                } 
            } 
            $avg_mark = isset($avg_mark)?$avg_mark:"0"; 
            $node[$count]["subject_id"]   = $sj->subject_id;
            $node[$count]["subject_name"] = $sj->subject_name;
            $node[$count]["color"]        = $sj->color; 
            $node[$count]["avg_marks"]    = number_format($avg_mark, 0);
            $node[$count]["status_code"]  = isset($status_code)?$status_code:"1"; 
            $count++;
        } 
        return $node;
    }
                    
        
    public function getBestStreak($stud_id, $st_date, $ed_date, $grade_id, $curr_id) {          
        $conseq_arr = array();
        $darr = $this->perform_model->getProcessedTimeOnPortal($stud_id, $st_date, $ed_date, $curr_id, $grade_id ); 
        if( $darr) {
            $uniq_dates = array();
            foreach( $darr as $dv) {
                $uniq_dates[$dv->time_on_portal_date] = strtotime($dv->time_on_portal_date);
            }
            $dates = array();
            foreach($uniq_dates as $dts ) {
                $dates[] = $dts;
            }              
            $conseq = array();
            $x      = 0;
            $max    = count($dates);
            for($i = 0; $i < count($dates); $i++) {
                $conseq[$x][] = date('Y-m-d',$dates[$i]);
                if($i + 1 < $max) {
                    $dif = $dates[$i + 1] - $dates[$i];
                    if($dif >= 90000) {
                        $x++;
                    } 
                }
            }            
            $conseq_cnt = array();
            foreach( $conseq as $ky => $cds ) {
                $conseq_cnt[$ky] = count($cds);
            }
            $count_conseq_days = max($conseq_cnt);
            $conseq_days_index = array_search($count_conseq_days, $conseq_cnt);
            $conseq_days       = $conseq[$conseq_days_index];
            $numof_ele         = count($conseq_days);
            
            $conseq_arr["conseq_days"]       = $count_conseq_days;
            $conseq_arr["conseq_start_date"] = $conseq_days[0];
            $conseq_arr["conseq_end_date"]   = $conseq_days[$numof_ele-1];
        } else {
            $conseq_arr["conseq_days"]       = "";
            $conseq_arr["conseq_start_date"] = "";
            $conseq_arr["conseq_end_date"]   = "";
        }
         
        return $conseq_arr;          
    }        
      

    public function getMonthlyTimeOnPortal($stu_id, $sty_dt, $edy_dt, $curr_id, $grade_id, $pkid ) {
        $monthArr = array(1,2,3,4,5,6,7,8,9,10,11,12);         
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $sty_dt);         
        $month_time = array();
        foreach( $monthArr as $mth ) {
            $month_strt_dt    = $dt->startOfMonth()->toDateTimeString();
            $month_end_dt     = $dt->endOfMonth()->toDateTimeString();
            
            $fdt = $dt;           
            $month_time[$mth-1]["month_name"] =  $fdt->format('M' );
            $month_time[$mth-1]["month_data"] = $this->getTimeOnPortal( $stu_id, $month_strt_dt, $month_end_dt, $curr_id, $grade_id, $pkid );
            $dt->addday(); 
        }
        return $month_time;
    } 
                      
     
    public function getBestMonthPortalTime($monthly_data) {
        $monthArr = array( 1 => "January", 2 => "February", 3 => "March",
                           4 => "Aprial",  5 => "May",      6 => "June",
                           7 => "July",    8 => "Agust",    9 => "September",
                           10 => "Octomber",11 => "November",12 => "December");   
        $month_data = array();
        foreach( $monthly_data as $mth => $mdata ) {   
            $month_tot_time = 0;
            if( isset($mdata["month_data"]["subject_time"]) ) {
                foreach( $mdata["month_data"]["subject_time"]  as $sk => $subj) {
                    $month_tot_time = $month_tot_time + $subj["portal_secs"];
                }
            } else {
                $month_tot_time = 0;
            }
            $month_data[$mth] = $month_tot_time;
        }         
        $max_month_time = max($month_data);          
        $max_month      = array_search($max_month_time, $month_data);
        
        if( $max_month_time > 0)
           return $monthArr[$max_month];
        else 
            return false;
    }
     
     
    public function getBestWorstMonths($stars) {  
        $max_month      = ""; 
        $min_month      = "";
        $max_month_star = 0;  
        $min_month_star = 0;          
        $returnArr      = array();
        $monthArr       = array( "01", "02", "03", "04", "05", "06",
                                 "07", "08", "09", "10", "11", "12"); 
        if($stars ) {
            $star_mth = array();
            foreach( $stars as $star_arr ) {                 
                $month_val = date('m', strtotime($star_arr->star_add_date));
                $star_mth[$month_val][] = $star_arr->stars + $star_arr->time_bonus;
            }
            
            $month_tot = array();
            foreach( $monthArr as $mnt ) {
                if( isset($star_mth[$mnt])) 
                    $month_tot[$mnt] = array_sum($star_mth[$mnt]);
                else
                    $month_tot[$mnt] = 0;
            }            
            
            /*            
            $month_tot = array();
            foreach( $star_mth as $mnth => $mdata ) {
                $month_tot[$mnth] = array_sum($mdata);
            }
            */            
            $max_month_star = max($month_tot);          
            $max_month      = array_search($max_month_star, $month_tot);            
            $min_month_star = min($month_tot);          
            $min_month      = array_search($min_month_star, $month_tot);    
            
            $max_month = date("F", strtotime("2017-".$max_month."-01"));
            $min_month = date("F", strtotime("2017-".$min_month."-01"));
        }         
        $returnArr["max_month_star"] = $max_month_star;
        $returnArr["max_month"]      = $max_month;
        $returnArr["min_month_star"] = $min_month_star;
        $returnArr["min_month"]      = $min_month;
        
        return $returnArr;         
    }
     
     
    public function getStudentPrizes($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id, $grades) { 
        $formated_prize = array();
        $subject_arr = array();
        foreach( $this->availsubjects as $sj ) {
            $subject_arr[$sj->subject_id]["subject_name"]  = $sj->subject_name;
            $subject_arr[$sj->subject_id]["subject_color"] = $sj->color;
        }
        $prize_arr = $this->mail_model->getPrizesByStudentId($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id);
       
        $x = 0;
        if( $prize_arr ) {
            foreach( $prize_arr as $pz ) {
                $formated_prize[$x]["grade"]      = str_replace( "Year", "Grade", $grades[$grade_id]);
                $formated_prize[$x]["date_range"] = date("F", strtotime($pz->price_start_date))." - ".date("F", strtotime($pz->price_end_date));
                
                $prize_type = "";
                $subj_color = "";
                if($pz->prize_type == "GRADE_PRIZE") {
                    $prize_type = "Overall";
                    $subj_color = "#014b70";
                } elseif( $pz->prize_type == "SUBJECT_PRIZE" ) {
                    $prize_type = isset($subject_arr[$pz->subject_id]) ? $subject_arr[$pz->subject_id]["subject_name"] : "";
                    $subj_color = isset($subject_arr[$pz->subject_id]) ? $subject_arr[$pz->subject_id]["subject_color"] : "";
                }
                
                $formated_prize[$x]["prize_type"]    = $prize_type;
                $formated_prize[$x]["subject_color"] = $subj_color;
                
                if( $pz->rank == 1 || $pz->rank == 2 || $pz->rank == 3 ) {
                    $formated_prize[$x]["rank"]  = "No ".$pz->rank;
                } elseif( $pz->rank > 3 && $pz->rank <= 10 ) {
                    $formated_prize[$x]["rank"] = "Top 10";
                } elseif( $pz->rank > 10 && $pz->rank <= 20 ) {
                    $formated_prize[$x]["rank"] = "Top 20";
                } else {
                    $formated_prize[$x]["rank"]  = "";
                    $formated_prize[$x]["grade"] = "";
                }
                $x++;
            }
        }
        
        $final_data = array();
        $num_of_prizes  = count($formated_prize);        
        $final_data["prize_summary"] = $num_of_prizes;  
        $final_data["prize_data"]    = $formated_prize;
                
        return $final_data;          
    }
     
     
    public function getBestWorstTest($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id) {
        $best_test  = $this->mail_model->getBestTestByStudent($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id);
        $worst_test = $this->mail_model->getWorstTestByStudent($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id);
        
        $finalData = array();
        $finalData["best_test"]["test_name"]  = isset($best_test->quiz_name) ? $best_test->quiz_name : "";
        $finalData["best_test"]["full_marks"] = isset($best_test->full_marks) ? $best_test->full_marks : "";
        $finalData["best_test"]["duration"]   = isset($best_test->time_taken) ? substr($best_test->time_taken, 0,-3) : "";
        
        $finalData["worst_test"]["test_name"]  = isset($worst_test->quiz_name) ? $worst_test->quiz_name : "";
        $finalData["worst_test"]["full_marks"] = isset($worst_test->full_marks) ? $worst_test->full_marks : "";
        $finalData["worst_test"]["duration"]   = isset($worst_test->time_taken) ? substr($worst_test->time_taken, 0, -3) : "";
       
        return $finalData;
    }   
     
     
    public function getReviewEmailToken($code) {
        return sha1($code.microtime(true));
    }
      
      
    public function sendReviewEmail($data, $viewblade) {           
        $stat = Mail::send($viewblade, $data, function($message) use ($data) {             
            $message->from($data["email_from"], "Tutor Wizard Info");
            $message->subject($data["email_subject"]); 
            $message->to($data["email_to"]);            
        }); 
        return $stat;
    }     
    
          
    public function getMostTakenTest($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id) {
        $max_subj_times = 0;
        $max_modl_times = 0;
        $subj_test = $this->mail_model->getSubjectTestCount($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id);
        $sub_test_count = array();
        if($subj_test) {
           foreach( $subj_test as $st ) 
              $sub_test_count[$st->subject_id] = $st->suj_test_count; 

           $max_subj_times = max($sub_test_count);
           $max_sub_id     = array_keys($sub_test_count, max($sub_test_count));
        }
        
        $mdl_test_count = array();
        $modl_test = $this->mail_model->getnModuleTestCount($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id);
        if($modl_test) {
            foreach( $modl_test as $mt ) 
                $mdl_test_count[$mt->module_id] = $mt->mod_test_count; 
            $max_modl_times = max($mdl_test_count);
            $max_mdl_id     = array_keys($mdl_test_count, max($mdl_test_count));
        }       
                        
        $max_test = array();
        if( ($max_subj_times != 0) && ($max_subj_times >= $max_modl_times) ) {
            $subj = $this->mail_model->getSubjectDetails($max_sub_id[0]);
            $max_test["test_times"] = $max_subj_times;
            $max_test["test_name"]  = $subj[0]->name;
        } elseif( ($max_modl_times != 0) && ($max_modl_times > $max_subj_times) ) {
            $mod_data = $this->mail_model->getModuleDetails($max_mdl_id[0]);
            $max_test["test_times"] = $max_modl_times;
            $max_test["test_name"]  = $mod_data[0]->name;
        } else {
            $max_test["test_times"] = "";
            $max_test["test_name"]  = "";
        }
        return $max_test;        
    } 
    
        
    public function getLeastTest($stu_id, $st_dt, $ed_dt, $grade_id, $curr_id, $pkid) { 
        $sub_test_count = array();
        $mdl_test_count = array();
        $subj_test = $this->mail_model->getSubjectTestCount( $stu_id, $st_dt, $ed_dt, $grade_id, $curr_id);   
        $modl_test = $this->mail_model->getnModuleTestCount( $stu_id, $st_dt, $ed_dt, $grade_id, $curr_id);
        
        if($subj_test) {
           foreach( $subj_test as $st ) 
              $sub_test_count[$st->subject_id] = $st->suj_test_count;   
        } 
        if($modl_test) {
           foreach( $modl_test as $mt ) 
              $mdl_test_count[$mt->module_id] = $mt->mod_test_count;            
        } 
       
        $modules       = array();
        $avail_modules = array();
        $avail_subjs   = DB::select('CALL spGetPackageSubject(?)', array($pkid));         
        foreach($avail_subjs as $subj ) {
            $modules[$subj->subject_id] = DB::select(("CALL spGetModule(?,?)"), array( $pkid, $subj->subject_id )); 
            if( !isset($sub_test_count[$subj->subject_id]) ) {
               $sub_test_count[$subj->subject_id]  = 0;
            }
        }            
        foreach( $modules as $mdata ) {
            foreach( $mdata as $mv ) {
                $avail_modules[$mv->module_id] = $mv->module_name;    
                if( !isset($mdl_test_count[$mv->module_id]) ) {
                    $mdl_test_count[$mv->module_id] = 0;
                }
            }         
        }          
        $min_subj_times = min($sub_test_count);
        $min_sub_id     = array_keys($sub_test_count, min($sub_test_count)); 
        $min_modl_times = min($mdl_test_count);
        $min_mdl_id     = array_keys($mdl_test_count, min($mdl_test_count));        
        
        $least_test = array();
        if( $min_subj_times >= $min_modl_times) {            
            $least_test["test_times"] = $min_modl_times;
            $least_test["test_name"]  = $avail_modules[$min_mdl_id[0]];
        } else {
            $subj = $this->mail_model->getSubjectDetails($min_sub_id[0]);
            $least_test["test_times"] = $min_subj_times;
            $least_test["test_name"]  = $subj[0]->name;
        }
        
        return $least_test;  
    }
     
         
    public function getCompetencyTestByStudent($stu_id, $st_date, $end_date, $grade_id, $curr_id) { 
        $return_arr = array();
        $format     = array();
        $subj_competency = array();
        $comp_data  = $this->mail_model->getCompetencyTestData($stu_id, $st_date, $end_date, $grade_id, $curr_id);
                 
        if($comp_data) {
            foreach( $comp_data as $cv ) 
               $format[$cv->subject_id] = number_format($cv->avg_marks, 0);
        }           
        $i = 0;
        $avg_count  = 0;
        $test_total = 0;
        foreach( $this->availsubjects as $subj ) {
            $subj_competency[$i]["subject_name"]  = $subj->subject_name;
            $subj_competency[$i]["subject_color"] = $subj->color;
            if( isset($format[$subj->subject_id]) ) {
                $subj_competency[$i]["avg_marks"] = $format[$subj->subject_id];
                $avg_count++;
                $test_total = $format[$subj->subject_id] + $test_total;
            } else {
                 $subj_competency[$i]["avg_marks"] = 0;
            }
            $i++; 
        } 
        if( $avg_count ) {
           $return_arr["total_competency"] = $test_total/$avg_count;
        } else {
           $return_arr["total_competency"] = 0;
        }
                 
        $return_arr["subject_competency"] = $subj_competency; 
        return $return_arr;
    }
     
          
    public function getStudentSubjectLeaderboard($stu_id, $st_date, $end_date, $grade_id, $curr_id) {           
        $subj_rank = array();
        if($this->availsubjects) {
            $subj_count = 0;
            foreach( $this->availsubjects as $subj) { 
                $subj_lbp = $this->mail_model->getLeaderBoardBySubject($subj->subject_id, $st_date, $end_date, $grade_id, $curr_id);                 
                if( $subj_lbp) {
                    $rank = 1;
                    foreach($subj_lbp as $sval) {                       
                        if( $stu_id == $sval->student_id) {
                            $subj_rank[$subj_count]["subject_rank"]  = $rank;
                            $subj_rank[$subj_count]["subject_stars"]  = $sval->all_stars;
                        } else {
                            $subj_rank[$subj_count]["subject_rank"]   = 0;
                            $subj_rank[$subj_count]["subject_stars"]  = 0;
                        }
                        $subj_rank[$subj_count]["subject_name"]  = $subj->subject_name;
                        $subj_rank[$subj_count]["subject_color"] = $subj->color;
                        $rank++;
                    }
                }
                $subj_count++;
            }
        }         
        return $subj_rank;        
    }
             
     
    public function getOverallLeaderboard($stu_id, $st_date, $end_date, $grade_id, $curr_id) {
        $overall_rank = array();
        $overallLB = $this->mail_model->getOverallLeaderBoard($st_date, $end_date, $grade_id, $curr_id);
        if($overallLB) {
            $rank = 1;
            foreach( $overallLB as $oval ) {
                if( $stu_id == $oval->student_id) {
                    $overall_rank["overall_rank"]  = $rank;
                    $overall_rank["overall_stars"] = $oval->all_stars;
                }
                $rank++;
            }
        } 
        return $overall_rank;  
    }   
            
     /*
     public function getLeastTest($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id) {
         $final_data = array();
         $mod_test_count  = 0;
         $subj_test_count = 0;
         $moduleTests  = $this->mail_model->getAllModuleTests($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id);
         $subjectTests = $this->mail_model->getAllSubjectTests($stu_id, $sty_dt, $edy_dt, $grade_id, $curr_id);
         
         if($moduleTests) {
             $mod_arr   = array();
             $mod_count = array();
             foreach($moduleTests as $mtest) {
                $mod_arr[$mtest->module_id][$mtest->id] = $mtest->id;                 
             }
             foreach( $mod_arr as $mid=>$mt_data ) {
                $mod_count[$mid] = count($mt_data);                 
             }             
             asort($mod_count);
             $mod_id   = key($mod_count);
             $mod_data = $this->mail_model->getModuleDetails($mod_id);
             $mod_name = $mod_data[0]->name;             
             $mod_test_count = current($mod_count);
         }
         
         if($subjectTests) {
             $subj_tests = array();
             $subj_count = array();
             foreach( $subjectTests as $stest ) {
                $subj_tests[$stest->subject_id][$stest->id] = $stest->id;
             }
             foreach( $subj_tests as $sid => $sj_data ) {
                $subj_count[$sid] = count($sj_data);                 
             } 
             asort($subj_count);
             $subj_id = key($subj_count);
             
             $subj = $this->mail_model->getSubjectDetails($subj_id);
             $subj_name = $subj[0]->name;
             $subj_test_count = current($subj_count);
         }
       
         if( $subj_test_count > $mod_test_count ) {
             if( $mod_test_count == 0 ) {
                $final_data["test_name"]  = $subj_name;
                $final_data["test_times"] = $subj_test_count;
             } else{
                $final_data["test_name"]  = $mod_name;
                $final_data["test_times"] = $mod_test_count;
             }
         } elseif( $subj_test_count < $mod_test_count ) {
              if( $subj_test_count == 0 ) {
                  $final_data["test_name"]  = $mod_name;
                  $final_data["test_times"] = $mod_test_count;
              } else {
                  $final_data["test_name"]  = $subj_name;
                  $final_data["test_times"] = $subj_test_count;
              }             
         } elseif( $subj_test_count < $mod_test_count ) {
              $final_data["test_name"]  = $mod_name;
              $final_data["test_times"] = $mod_test_count;
         }else{
             $final_data["test_name"] =  "";
             $final_data["test_times"] = "";
         }
         
         return $final_data; 
     }
     
     */
     
    public function getPortalUrl() {
        $tw_portal_url = '';
        if (App::environment('local')) {
            $tw_portal_url = 'http://192.168.1.51:8003/portal#';
        } elseif (App::environment('production')) {
            $tw_portal_url = 'http://tutorwizard.org/portal#';
        }
        return $tw_portal_url;
    }
    
   
    public function getReviewEmailList( $student_id ) {
        $rst = array();
        $data = $this->mail_model->getReviewEmailListByStudentId($student_id);
        if( $data ) 
            foreach( $data as $v ) 
                $rst[] = $v->email;            
        
        return $rst;
    }
    
    
    public function getMonthTutorChal( $stu_id, $mth_st_dt, $mth_ed_dt, $curr_id, $grade_id, $pkid ) {
        $format_data = array();
        $count_arr   = array();  
        $final_arr   = array();
        $tc_data = $this->mail_model->getCompletedTutorChal( $stu_id, $mth_st_dt, $mth_ed_dt, $curr_id, $grade_id );
        if($tc_data) {
            foreach( $tc_data as $tval) {
                $subj_id = $this->mail_model->getSubjectIdByUnitId($tval->unit_id, $pkid );
                if($subj_id) {
                    $format_data[$subj_id][$tval->ts_qid]["stars"] = $tval->stars;
                    $format_data[$subj_id][$tval->ts_qid]["marks"] = $tval->full_marks;
                }
            }
        }
        if($format_data) {
            foreach( $format_data as $sbj_id => $tq ) {
                $star_cnt = 0;
                $marks    = 0;
                foreach( $tq as $val ) {
                    $star_cnt = $star_cnt + $val["stars"];
                    $marks    = $marks    + $val["marks"];
                }
                $count_arr[$sbj_id]["star_count"] = $star_cnt;
                $count_arr[$sbj_id]["full_marks"] = $marks;
            }
        } 
        $i = 0;
        foreach( $this->availsubjects as $subj ) {
            $final_arr[$i]["subject_name"]  = $subj->subject_name;
            $final_arr[$i]["subject_color"] = $subj->color; 
            if( isset($count_arr[$subj->subject_id]) ) {
                $final_arr[$i]["stars"]      = $count_arr[$subj->subject_id]["star_count"];
                $final_arr[$i]["full_marks"] = $count_arr[$subj->subject_id]["full_marks"];                
            } else {
                $final_arr[$i]["stars"]      = 0;
                $final_arr[$i]["full_marks"] = 0;
            }
            $i++; 
        } 
        
        return $final_arr;        
    }
    
    
    public function getMonthlyTutorChallenge($stu_id, $sty_dt, $edy_dt, $curr_id, $grade_id, $pkid ) {
        $monthArr = array(1,2,3,4,5,6,7,8,9,10,11,12);         
        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $sty_dt);
        $month_time = array();
        foreach( $monthArr as $mth ) {
            $mth_st_dt = $dt->startOfMonth()->toDateTimeString();
            $mth_ed_dt = $dt->endOfMonth()->toDateTimeString();
            $fdt = $dt;           
            $month_time[$mth-1]["month_name"] = $fdt->format('M' );
            $month_time[$mth-1]["month_data"] = $this->getMonthTutorChal( $stu_id, $mth_st_dt, $mth_ed_dt, $curr_id, $grade_id, $pkid );           
            $dt->addday(); 
        }
        
        return $month_time;       
    }  
                       
      
}