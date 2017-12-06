<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;
use App\Student;
use Carbon\Carbon;
use App\MailModel;
use App\CouponModel;


class CouponGenarator extends Command  {
   
    
    protected $signature   = 'genarate:coupons';
    protected $description = 'Command description';
    var $coupon_seed       = '0123456789';
    var $code_length       = 8; 
    
    
    public function __construct( CouponModel $coupon_model, MailModel $mail_model) {
        parent::__construct();                  
        $this->coupon_model = new $coupon_model; 
        $this->mail_model    = new $mail_model;  
    }
    
    
    
    public function handle() {        
        $start_time = Carbon::now()->toDateTimeString(); 
        $this->fl_comment("Genarate Coupon Process started at ".$start_time);
        
        $coupon_conf = $this->coupon_model->getEligibleCouponConfig();
        if($coupon_conf) {
            foreach( $coupon_conf as $conf ) {
                $stud_list = array();
                $stud_list = $this->coupon_model->getEligibleStudentList($conf);
                $this->createStudentCoupons($stud_list, $conf->id, $conf->discount);
                $this->sendCouponEmails();   
                $upd = array();
                $upd["status"] = "COMPLETED";
                $upd["last_mod_by"] = "SYSTEM";
                $upd["total_coupons"] = count($stud_list);                
                $upd["last_mod_date"] = Carbon::now()->toDateTimeString();
                $st = DB::table('coupon_header')->where('id', $conf->id)->update($upd);                
            }                
        } else {
            $this->fl_comment("No Coupon Configurations");
        }
        $end_time = Carbon::now()->toDateTimeString(); 
        $this->fl_comment("Genarate Coupon Process ended at ".$end_time);    
    }
    
    
    
    public function sendCouponEmails() {
        $coupon_data = $this->coupon_model->getCouponData(); 
        if( $coupon_data ) {
            foreach( $coupon_data as $cd ) {                
                $stud = $this->mail_model->getStudentDetails( $cd->student_id);
                $email_data = array();                
                $email_data["coupon_code"] = $cd->coupon_code;
                $email_data["discount"]    = $cd->discount;                 
                $email_data["full_name"]   = $stud[0]->full_name;
                $email_data["first_name"]  = $stud[0]->firstName; 
                $email_data["family_name"] = $stud[0]->familyName;                 
                $email_data["email_to"]    = $stud[0]->email; 
                //$email_data["email_to"]    = "lakshikasur@gmail.com"; 
                $email_data["email_from"]  = config("constants.REVIEW_EMAIL_FROM");
                
                $email_data["email_subj"]  = "Coupon Code";
                $view                      = "emails.coupon";                 
                $stat = Mail::send( $view, $email_data, function($message) use ($email_data) {             
                    $message->from( $email_data["email_from"], "Tutor Wizard Info");
                    $message->subject( $email_data["email_subj"]); 
                    $message->to( $email_data["email_to"]);            
                });   
                if($stat) {
                    $upd = array();
                    $upd["email_sent"]  = "1";
                    $upd["notified_at"] = DB::raw('NOW()');
                    $st = DB::table('coupon_student')->where('id', $cd->id)->update($upd);
                }
            }
        }      
    }
    
        
    
    
    public function fl_comment($msg) { 
        echo "\n $msg \n";
    }
    
    
    public function getCouponCode() {
        $rand = '';
        $seed = str_split($this->coupon_seed);
        shuffle($seed);        
        foreach( array_rand($seed, $this->code_length) as $k ) 
            $rand .= $seed[$k];
        
        return $rand;
    }    
   
    
    public function createStudentCoupons( $stud_list, $cconf_id, $discount ) {
        if( $stud_list ) {
            foreach( $stud_list as $stud_id ) {
                do {
                    $ccode    = $this->getCouponCode();
                    $is_avail = $this->coupon_model->isAssignedCoupon($ccode);                    
                } while( $is_avail );                
                $coupon_arr = array();
                $coupon_arr["coupon_code"]    = $ccode;
                $coupon_arr["student_id"]     = $stud_id;
                $coupon_arr["discount"]       = $discount;
                $coupon_arr["status"]         = "ACTIVE";
                $coupon_arr["coupon_conf_id"] = $cconf_id;                
                $res = DB::table("coupon_student")->insert($coupon_arr);               
            }
        }  
    }
    
  
    
    
}
