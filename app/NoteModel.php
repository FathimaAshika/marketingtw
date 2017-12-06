<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NoteModel extends Model {
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'last_mod_date';
    
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table      = 'crm_notes';
    protected $fillable   = [    "id",        
                                 "entity_id",
                                 "entity_type",
                                 "notes",
                                 "last_mod_by",
                                 "last_mod_date",
                                 "created_by",
                                 "created_date"                                                  
                            ];
    
    
	



}
