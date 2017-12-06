<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MkOrganization extends Model {
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'last_mod_date';
    
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table      = 'mrk_organization'; 
    protected $fillable = [ "org_name", "org_type", "member_of",
                            "industry", "no_of_employee", "relationship",
                            "primary_email", "other_email", "primary_phone",
                            "other_phone", "primary_mobile", "other_mobile",
                            "primary_fax", "other_fax", "website",
                            "facebook", "house_no", "street_1",
                            "street_2", "city", "state",
                            "country", "status"
                        ];
    
    
    
}
