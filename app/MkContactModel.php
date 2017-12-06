<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;


class MkContactModel extends Model {
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'last_mod_date';
    
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table      = 'mrk_contacts';    
    protected $fillable   = [ "cmp_id", 
                              "cmp_type", 
                              "contact_name", 
                              "house_no",
                              "street_1",
                              "street_2",
                              "city",
                              "state",
                              "country",
                              "primary_email",
                              "other_emails",
                              "primary_phone",
                              "other_phone",
                              "primary_fax",
                              "other_fax",
                              "website",
                              "facebook",
                              "last_mod_by",
                              "created_by"                                                                  
                           ];  
    

    
    
    
}
