<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class MkContactPersonModel extends Model {
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'last_mod_date';
    
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table      = 'mrk_contact_person';    
    protected $fillable   = [ "cmp_id", "cmp_type", "title",
                              "person_name", "role", "department",
                              "lead_source", "house_no", "street_1",
                              "street_2", "city", "state",
                              "country", "primary_email", "other_emails",
                              "primary_mobile", "other_mobile", "primary_phone"
                            ];  
    
    
     public function getSummary( $qry, $page_lenth, $start, $ordbcol, $ordby ) {      
        $qry->select('*');
        $qry->orderBy($ordbcol, $ordby);       
        $qry->offset($start);
        $qry->limit($page_lenth);
        
        return $qry->get();  
    }

    
    public function  getSummaryCount($qry) {
        return $qry->count();        
    }
}
