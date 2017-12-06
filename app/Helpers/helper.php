<?php

function distinguishEmail( $emails_arr ) {
    $eml["primary_email"] = "";
    $eml["other_emails"]  = "";  
    
    if($emails_arr && is_array($emails_arr) ) {
        $other_emails = array();
        foreach( $emails_arr as $em ) 
            if($em["isPrimary"] == "T") 
                $eml["primary_email"] = $em["email"];
             elseif($em["isPrimary"] == "F") 
                $other_emails[] = $em["email"];
        if($other_emails) 
            $eml["other_emails"] = implode(",", $other_emails);      
    } 
    return $eml;
}


function distinguishPhone($phone_arr) {
    $phn["primary_phone"] = "";
    $phn["other_phone"]   = "";     
    if( $phone_arr && is_array($phone_arr) ) { 
        $other_phone = array();
        foreach( $phone_arr as $ph ) 
            if( $ph["isPrimary"] == "T") 
                $phn["primary_phone"] = $ph['phone'];
             elseif($ph["isPrimary"] == "F") 
                $other_phone[] = $ph['phone'];             
        if($other_phone) 
            $phn["other_phone"] = implode(",", $other_phone);
    }  
    return $phn;
}


function distinguishMobile($mobile_arr) {
    $mob["primary_mobile"] = "";
    $mob["other_mobile"]   = "";      
    if( $mobile_arr && is_array($mobile_arr) ) {
        $other_mobile = array(); 
        foreach( $mobile_arr as $mo ) 
            if($mo["isPrimary"] == "T") 
                $mob["primary_mobile"] = $mo["mobile"];
            elseif($mo["isPrimary"] == "F") 
                $other_mobile[] = $mo["mobile"];
        if($other_mobile) 
            $mob["other_mobile"] = implode(",", $other_mobile);
    }
    return $mob;      
}


function distinguishFax( $fax_arr ) {
    $fax["primary_fax"] = "";
    $fax["other_fax"]   = ""; 
    if( $fax_arr && is_array($fax) ) { 
        $other_fax = array(); 
        foreach( $fax_arr as $fx ) 
            if($fx["isPrimary"] == "T") 
                $fax["primary_fax"] = $fx["fax"];
             elseif($fx["isPrimary"] == "F") 
                $other_fax[] = $fx["fax"];
        if($other_fax) 
            $fax["other_fax"] = implode(",", $other_fax); 
    }
    return $fax;   
}



function convertStringToArr($fld, $separater="," ) {
    if($fld)
        return explode( $separater, $fld);   
    else 
        return array();
}


function mergeEmails( $primary_email, $other_emails) {
    $email_arr = array();    
    if( $primary_email ) 
        $email_arr[] = array("email"=>$primary_email, "isPrimary"=>"T");     
    if($other_emails) 
        foreach( convertStringToArr($other_emails) as $vl ) 
            $email_arr[] = array("email"=>$vl, "isPrimary"=>"F");  
    
    return $email_arr;  
}
                    


function mergePhone( $primary_phone, $other_phone ) {
    $phone_arr = array();     
    if( $primary_phone ) 
        $phone_arr[] = array("phone"=>$primary_phone, "isPrimary"=>"T");
    if($other_phone) 
        foreach( convertStringToArr($other_phone) as $vl ) 
            $phone_arr[] = array("phone"=>$vl, "isPrimary"=>"F");  
    
    return $phone_arr;
}



function mergeMobile( $primary_mobile, $other_mobile ) {
    $mob_arr = array();     
    if( $primary_mobile ) 
        $mob_arr[] = array("mobile"=>$primary_mobile, "isPrimary"=>"T");
    if($other_mobile) 
        foreach( convertStringToArr($other_mobile) as $vl ) 
            $mob_arr[] = array("mobile"=>$vl, "isPrimary"=>"F");  
    
    return $mob_arr;
}


function mergeFax($primary_fax, $other_fax) {
    $fax_arr = array();     
    if( $primary_fax ) 
        $fax_arr[] = array("fax"=>$primary_fax, "isPrimary"=>"T");
    if($other_fax) 
        foreach( convertStringToArr($other_fax) as $vl ) 
            $fax_arr[] = array("fax"=>$vl, "isPrimary"=>"F");  
    
    return $fax_arr;
}


function judges() {
    $data = array('test_helper'=>'ashikas test helper ');
    return response()->json($data)->header('foo', 'bar')->send();
}
   
function getCountryName($country_id) {
    return DB::table('country')->where('id', $country_id)->value("country_name"); 
}



function notifyAdmin( $client, $token, $entity_id, $entity_type, $msg, $action_by, $c_date  ) {
    //$client = new Client();
    $url    = config('globals.sck_admin_notif');
    $param  = array( "form_params" => 
                   array( 
                       "token"       => $token,
                       "entity_id"   => $entity_id,                       
                       "entity_type" => $entity_type,
                       "msg"         => $msg,
                       "action_by"   => $action_by,
                       "c_date"      => $c_date
                    )
              );
    $res = $client->request('POST', $url, $param);  
    //echo $res->getHeader('content-type');
    //echo $res->getBody();
    return $res->getStatusCode();
}
    
    
?>
