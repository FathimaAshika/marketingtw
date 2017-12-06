<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class Transaction extends Model {

    protected $table = 'transaction';
    
    public    $timestamps = false;
    
    protected $fillable = [
        "invoiceNo", 'date', 'expire_date', 'paid_amount', 'balance', 'student_id',
        'payment_method', 'cardholder_name', 'card_no','valid_start','valid_end','package_amount','prev_balance','payment_term','total_amount_payable','previous_payments','discount_amount',
        'curriculum','grade','package','late_payment'
    ];

}
