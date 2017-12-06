<?php
namespace App\Libraries;
use DB;
//type-----------------------------
    //1-admin
    //2-student
    //3-tutor
    //4-system user
    //6-test user

class Profile {
    
    var $is_valid_token = false;
    var $user_info      = array(); 
    var $is_validUser   = false;
    var $user_id;
    
    
    public function setToken($token)  { $this->token  = $token; }
    public function getToken($token)  { return $this->token; }
    
    
    public function is_valid_token() {
        return $this->is_valid_token;
    }
   
        
    public function checkToken($token)  { 
        $user_id = DB::table('temp_users')->where('token',$token)->value("user_id"); 
        if($user_id) {
            $this->user_id      = $user_id;
            $this->is_valid_token = true;
            $this->is_validUser   = true;
        }
        return $this->user_id;        
    }    
    
    
    public function initUser($token) {
        $this->checkToken($token);
        if( $this->is_valid_token ) {
            $this->user_info = DB::table("users")->where("id", $this->user_id)->first();
        }
        return $this->user_info;
    }
    
   
    public function is_Admin() { 
        if( $this->user_info->type = 1 )
            return true;
        else 
           return false;
    }    
    
    public function is_Student() { 
        if( $this->user_info->type = 2 )
            return true;
        else 
           return false;         
    } 
 
    public function is_Tutor() { 
        if( $this->user_info->type = 3 )
            return true;
        else 
           return false;         
    } 
   
    public function is_SystemUser() { 
        if( $this->user_info->type = 4 )
            return true;
        else 
           return false;         
    } 
    
    
    public function is_DemoUser() { 
        if( $this->user_info->type = 6 )
            return true;
        else 
           return false;         
    } 
    
    
   
}

