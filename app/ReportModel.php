<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class ReportModel extends Model {
    
    
    public function getPortalStatus( $date_range, $extra_query, $status ) {   
        $result = array();
        $count  = DB::table('students as s')
                   ->join( 'users as u', 'u.id', '=', 's.user_id') 
                   ->join( 'transaction as t', 't.student_id', '=', 's.std_id')  
                   ->join( 'action_log as a', 'a.student_id', '=', 's.std_id') 
                   ->whereBetween( "a.created_date", $date_range) 
                   ->where( "a.type", $status) 
                   ->count();            
        $data =  DB::table('students as s')
                   ->join( 'users as u', 'u.id', '=', 's.user_id') 
                   ->join( 'transaction as t', 't.student_id', '=', 's.std_id')  
                   ->join( 'action_log as a', 'a.student_id', '=', 's.std_id') 
                   ->select( 's.*', 't.*', 'u.*' )
                   ->whereBetween( "a.created_date", $date_range) 
                   ->where( "a.type", $status) 
                   ->orderBy( $extra_query["ordby_col"], $extra_query["ordby_type"] )
                    ->offset( $extra_query["lmt_start"] )
                    ->limit( $extra_query["lmt_length"])
                    ->get();
        $result["count"] = $count; 
        $result["data"]  = $data;
        //var_dump(DB::getQueryLog());exit; 
        return $result; 
    }
    
    
    
    public function getCanceledSubcriptionsReport( $date_range, $extra_query ) {
        $result = array();
        $count  = DB::table('students as s')
                   ->join( 'users as u', 'u.id', '=', 's.user_id') 
                   ->join( 'transaction as t', 't.student_id', '=', 's.std_id')  
                   ->join( 'action_log as a', 'a.student_id', '=', 's.std_id') 
                   ->whereBetween( "a.created_date", $date_range) 
                   ->where( "a.type", "User unsubscription") 
                   ->count();            
        $data =  DB::table('students as s')
                   ->join( 'users as u', 'u.id', '=', 's.user_id') 
                   ->join( 'transaction as t', 't.student_id', '=', 's.std_id')  
                   ->join( 'action_log as a', 'a.student_id', '=', 's.std_id') 
                   ->select( 's.*', 't.*', 'u.*' )
                   ->whereBetween( "a.created_date", $date_range) 
                   ->where( "a.type", "User unsubscription") 
                   ->orderBy( $extra_query["ordby_col"], $extra_query["ordby_type"] )
                    ->offset( $extra_query["lmt_start"] )
                    ->limit( $extra_query["lmt_length"])
                    ->get();
        $result["count"] = $count; 
        $result["data"]  = $data;
        
        return $result; 
    }
    
    
    
    public function getUpgradesReport( $date_range, $extra_query ) {
        $result = array();
        $count  = DB::table('students as s')
                   ->join( 'users as u', 'u.id', '=', 's.user_id') 
                   ->join( 'transaction as t', 't.student_id', '=', 's.std_id')  
                   ->join( 'action_log as a', 'a.student_id', '=', 's.std_id') 
                   ->whereBetween( "a.created_date", $date_range) 
                   ->where( "a.type", "Package updation") 
                   ->count();            
        $data =  DB::table('students as s')
                   ->join( 'users as u', 'u.id', '=', 's.user_id') 
                   ->join( 'transaction as t', 't.student_id', '=', 's.std_id')  
                   ->join( 'action_log as a', 'a.student_id', '=', 's.std_id') 
                   ->select( 's.*', 't.*', 'u.*' )
                   ->whereBetween( "a.created_date", $date_range) 
                   ->where( "a.type", "Package updation") 
                   ->orderBy( $extra_query["ordby_col"], $extra_query["ordby_type"] )
                    ->offset( $extra_query["lmt_start"] )
                    ->limit( $extra_query["lmt_length"])
                    ->get();
        $result["count"] = $count; 
        $result["data"]  = $data;
        
        return $result; 
    }
    
            
    
    public function getMadePaymentReport( $date_range, $extra_query ) {
        $result = array();
        $count  = DB::table('students as s')
                    ->join( 'users as u', 'u.id', '=', 's.user_id') 
                    ->leftJoin( 'transaction as t', 's.std_id', '=', 't.student_id')  
                    //->select( 's.*','t.*', 'u.*' )
                    ->whereBetween( "t.date", $date_range)
                    ->where( "t.payment_method", "!=", "Free Trial") 
                    ->count(); 
        //DB::enableQueryLog(); 
        $data = DB::table('students as s')
               ->join( 'users as u', 'u.id', '=', 's.user_id')    
               ->leftJoin( 'transaction as t', 's.std_id', '=', 't.student_id' ) 
               ->select( 's.*', 't.*', 'u.email', 'u.type', 'u.status','u.created_at AS created_date' )
               ->whereBetween( "u.created_at", $date_range) 
               ->where( "t.payment_method", "!=", "Free Trial") 
               ->orderBy( $extra_query["ordby_col"], $extra_query["ordby_type"] )
               ->offset( $extra_query["lmt_start"] )
               ->limit( $extra_query["lmt_length"])
               ->get();  
        $result["count"] = $count; 
        $result["data"]  = $data;        
       // var_dump(DB::getQueryLog());
       // exit;
        return $result; 
    }
    
    
    
    
    public function getSignUpReport( $date_range, $extra_query ) {
        $result = array();
        $count  = DB::table('students as s')
                    ->join( 'users as u', 'u.id', '=', 's.user_id')            
                    ->select( 's.*','u.*' )
                    ->whereBetween( "u.created_at", $date_range) 
                    ->count();            
        $data = DB::table('students as s')
               ->join( 'users as u', 'u.id', '=', 's.user_id')            
               ->select( 's.*','u.*' )
               ->whereBetween( "u.created_at", $date_range) 
               ->orderBy( $extra_query["ordby_col"], $extra_query["ordby_type"] )
               ->offset( $extra_query["lmt_start"] )
               ->limit( $extra_query["lmt_length"])
               ->get();  
        if($data) {
            foreach( $data as $k => $val) {
                $payment_arr = $this->getInitialPaymentByStudentId($val->std_id);                
                $val->payment_term   = isset($payment_arr[0]->payment_term)    ? $payment_arr[0]->payment_term : "" ;
                $val->payment_method = isset($payment_arr[0]->payment_method) ? $payment_arr[0]->payment_method : "" ;
                $val->paid_amount    = isset($payment_arr[0]->paid_amount)     ? $payment_arr[0]->paid_amount : "" ;                
                $val->package_amount = isset($payment_arr[0]->package_amount)  ? $payment_arr[0]->package_amount : "" ;
                $val->valid_end       = isset($payment_arr[0]->valid_end)          ? $payment_arr[0]->valid_end : "" ;
             }
        }
        $result["count"] = $count; 
        $result["data"]  = $data;       
        
        return $result;    
    }
    
    
    public function getInitialPaymentByStudentId($stud_id) {
        return DB::table('transaction')
                ->where('student_id', $stud_id)
                ->orderBy("created_at", "ASC")
                ->limit(1)                
                ->get();
    }
    
    
    public function getStudentMobile($std_id) {
        return DB::table('student_mobile')
                ->where('std_id', $std_id)
                ->where('status', 1)
                ->value("mobile");
    }
    
    
    public function getTest() {
        $data = DB::table('students as s')
                ->join( 'users as u', 'u.id', '=', 's.user_id')            
                ->select( 's.*','u.*' ) 
                ->limit(55)
                ->get();
        return $data;
    }    

    
}
