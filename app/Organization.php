<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
     protected $table        =   'mrk_organization';
    
    public    $timestamps   =   false;
    
    protected $fillable = [        
        "name", 
        'type', 
        'member_of',
        'industry', 
        'no_of_employee',
        'relationship',        
        'primary_email',
        'secondary_email', 
        'primary_phone',
        'secondary_phone',
        'primary_mobile',        
        'secondary_mobile',        
        'fax',
        'website',
        'facebook',
        'house_no',
        'street_1',
        'street_2',
        'city',
        'state',
        'country',
        'add_date',
        'add_by',
        'modified_date',
        'modified_by'

    ];
}
