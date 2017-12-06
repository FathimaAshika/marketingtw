<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;


class CouponModel extends Model {
    

    public function getSummary( $lmt_length, $lmt_start ) {
        //DB::enableQueryLog();
        $data = DB::table('coupon_header')                   
                 ->select( '*' ) 
                 ->orderBy( "created_date", "desc" )
                 ->offset( $lmt_start )
                  ->limit( $lmt_length)
                  ->get();
        //var_dump(DB::getQueryLog());
        return $data;
    }
    
    
    public function getEligibleCouponConfig() {
        $data = DB::table('coupon_header')                   
                ->select( '*' ) 
                ->where("status", "APPROVED" )
                ->get();
        return $data;
    }
        
        
    public function getEligibleStudentList($flt) {
        $relt  = array();
        $where = array();   
        if( $flt->coupon_type == "STUDENT" ) { 
            if( !empty($flt->student_id)  ) {
                $where[] = "s.std_id = '".$flt->student_id."'"; 
                $where[] = "u.status = 1";
                $where[] = "u.id = s.user_id";
                $sql = "SELECT s.std_id FROM students s, users u WHERE "
                         .implode(' AND ', $where);
                $data = DB::select($sql); 
                if($data) {
                    foreach($data as $d ) 
                        $relt[$d->std_id] = $d->std_id;                    
                }
            }
        } elseif ( $flt->coupon_type == "ALL" ) {
            $where[] = "u.status = 1";  
            $where[] = "s.user_id = u.id";
            $where[] = "s.package_id = pk.id";  
            $where[] = "s.std_id = py.std_id";
            
            if( isset($flt->curriculum_id) && !empty($flt->curriculum_id) ) 
                $where[]  = "pk.curriculum_id = '".$flt->curriculum_id."'";
            if( isset($flt->grade_id) && !empty($flt->grade_id) ) 
                $where[]  = "pk.grade_id = '".$flt->grade_id."'";  
            if( isset($flt->payment_type_id) && !empty($flt->payment_type_id) ) 
                $where[]  = "py.payment_type_id = '".$flt->payment_type_id."'";
            if( isset($flt->school) && !empty($flt->school) ) 
                $where[]  = "s.school = '".$flt->school."'";
            
            $sql = "SELECT s.std_id FROM students s, users u, payment py, package pk WHERE "
                    .implode(' AND ', $where);            
            $data = DB::select($sql);
            if( $data ) {
                foreach( $data as $d ) {
                    if( !empty($flt->payment_term_id) ) {
                        $check_payterm = $this->checkStudentPaymentTerm( $d->std_id, $flt->payment_term_id );
                        if( $check_payterm ) {
                            $relt[$d->std_id] = $d->std_id;
                        }
                    } else {
                        $relt[$d->std_id] = $d->std_id;
                    }                        
                }
            }
        } 
        return $relt;
    }  
    
    
    public function checkStudentPaymentTerm( $std_id, $payment_term_id ) {        
        $res = DB::table('student_package_price as spp')                    
                ->join('package_price as pp', 'spp.package_price_id', '=', 'pp.id') 
                ->join('package_type as pt',  'pp.package_type_id', '=', 'pt.id')                     
                ->select('spp.*')                    
                ->where( "pt.id", $payment_term_id )                    
                ->where("spp.student_id", $std_id )                    
                ->get();
            
       return $res; 
    }
    
    
    public function isAssignedCoupon($ccode) {
        $data = DB::table('coupon_student') 
                ->where("coupon_code", $ccode )
                ->value("coupon_code");
        return $data;
    }
    
    
    
    public function getCouponData() {
        return DB::table('coupon_student')->where("email_sent", 0 )->get();
    }
    
    
    
    
    /*
    public function getEligibleStudentList($flt) {
        $where = array();        
        if( $flt["student_gp"] == "all_students" ) {            
            $where[] = "u.status = 1";  
            $where[] = "s.user_id = u.id";
            $where[] = "s.package_id = pk.id";  
            $where[] = "s.std_id = py.std_id";
            
            if( isset($flt["grade_id"]) && !empty($flt["grade_id"]) ) 
                $where[] = "pk.grade_id = '".$flt["grade_id"]."'";            
            if( isset($flt["curriculum_id"]) && !empty($flt["curriculum_id"]) ) 
                $where[] = "pk.curriculum_id = '".$flt["curriculum_id"]."'";
            if( isset($flt["school"]) && !empty($flt["school"]) ) 
                $where[] = "s.school = '".$flt["school"]."'";
            if( isset($flt["payment_type_id"]) && !empty($flt["payment_type_id"]) ) 
                $where[] = "py.payment_type_id = '".$flt["payment_type_id"]."'";
            
            $sql = "SELECT s.std_id FROM students s, users u, payment py, package pk WHERE "
                    .implode(' AND ', $where);
            $data = DB::select($sql);
            
            
        } elseif( $flt["student_gp"] == "sel_student" ) {            
            if( isset($flt["student_ref"]) && !empty($flt["student_ref"]) ) 
                $where[] = "s.std_id = '".$flt["student_ref"]."'"; 
                $where[] = "u.status = 1";        
            
            $sql = "SELECT s.std_id FROM students s, users u WHERE ".implode(' AND ', $where);
            $data = DB::select($sql); 
        }        
        print_r($data);       
        
        /*
      

    [] => 3
    [payment_term_id] => 1
    [] => fgh
    [student_gp] => all_students
        */
     /*
    }
    */
    
}
