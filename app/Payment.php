<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table        =   'payment';
    
    public    $timestamps   =   false;
    
    protected $fillable = [
        
        "amount", 
        'status', 
        'payment_type_id',
        'std_id', 
        'balance',
        'student_id',        
        'date',
        'next_payment_date', 
        'first_paid',
        'valid_start',
        'valid_end',        
        'balance',        
        'expire_extened',
        'late_paid'

    ];
    
}
