<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StarConfigModel extends Model
{
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'star_prize_config';
    protected $fillable = [ "id",
                            "curriculum_id",
                            "grade_id",
                            "subject_id",
                            "price_start_date",
                            "price_end_date",
                            "status",
                            "created_date",
                            "created_by",
                            "last_mod_by",
                            "last_mod_date"  
                        ];
    
    
}
