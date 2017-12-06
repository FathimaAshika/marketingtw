<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;


class MkLeadModel extends Model {
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'last_mod_date';
    
    
    protected $connection = 'mysql';
    protected $primaryKey = 'lead_id';
    protected $table      = 'mrk_leads';
    protected $fillable   = [ "lead_id", "lead_name", "gender", 
                              "role", "dob", "residence_country",
                              "primary_email", "other_email", "primary_phone",
                              "other_phone", "primary_mobile", "other_mobile",
                              "last_mod_by", "last_mod_date", "created_by",
                              "created_date"                                                  
                            ];

    
    
    public function getSummary( $qry, $page_lenth, $start, $ordbcol, $ordby ) {
       // DB::enableQueryLog();   
        $qry->select('*');
        $qry->orderBy($ordbcol, $ordby);       
        $qry->offset($start);
        $qry->limit($page_lenth);
        $leads = $qry->get();      
        //var_dump(DB::getQueryLog());
        return $leads;        
    }

    
    public function  getSummaryCount($qry) {
        return $qry->count();        
    }


}
