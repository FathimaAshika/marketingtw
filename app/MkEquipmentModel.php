<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class MkEquipmentModel extends Model {
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'last_mod_date';
    
    protected $connection = 'mysql';
    protected $primaryKey = 'equipment_id';
    protected $table      = 'mrk_equipment';
    protected $fillable   = [ "cmp_id", "cmp_type", "title",
                              "eq_cost", "eq_type", "payment_type",
                               "vendor_name", "vendor_address", "vendor_email",
                               "vendor_phone", "contact_person", "contact_person_email",
                               "contact_person_phone", "issue_date", "expire_date",
                               "warranty_date", "status", "last_mod_by",
                               "last_mod_date", "created_by", "created_date"                                                    
                            ];
    
     public function getSummary( $filter, $page_limit, $st_limit, $ordbcol, $ordby ) {
       //DB::enableQueryLog();        
        $data = DB::table('mrk_equipment')
                    ->where($filter)
                    ->orderBy($ordbcol, $ordby)
                    ->offset($st_limit)
                    ->limit($page_limit)
                    ->get(); 
       //var_dump(DB::getQueryLog());
       return $data;        
    }
    
    
    public function getSummaryCount($filter) {
        return DB::table('mrk_equipment')->where($filter)->count(); 
    }
    

}
