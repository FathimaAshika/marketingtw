<?php
namespace App\Traits;
use App;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;


trait Notes {       
     
    
    
    
    
    public function deleteNoteById( $note_id ) {       
    } 
    
    public function getNoteById( $req ) {       
    }
    
   
    
    
    
    
    //*****************************************************
    
    
     public function editNote( $note_id, $user_id, $note ) {  
        $data["notes"]         = $note;
        $data["last_mod_by"]   = $user_id;                            
        $data["last_mod_date"] = Carbon::now()->toDateTimeString();        
        return DB::table("crm_notes")->where("id", $note_id)->update($data); 
    }
    
    
    public function getNotes( $entity_id, $entity_type ) {   
        $rslt = array();
        $data = DB::table('crm_notes')
                ->where('entity_id', $entity_id)
                ->where('entity_type', $entity_type)
                ->get();
        if($data) {
            foreach( $data as $d ) {
                $d->created_by  = $this->getSysUserById($d->created_by);
                $d->last_mod_by = $this->getSysUserById($d->last_mod_by);
            }
        }        
        return $data;
    }    
    
    
    public function deleteNote( $entity_id, $entity_type ) {  
        $res = DB::table('crm_notes')
                ->where('entity_id', $entity_id)
                ->where('entity_type', $entity_type)
                ->delete();
        return $res;
    }    
    
    
    public function addNote( $entity_id, $entity_type, $user_id, $note ) {       
        $data = array();                    
        $data["entity_id"]     = $entity_id;
        $data["entity_type"]   = $entity_type;
        $data["notes"]         = $note;        
        $data["created_by"]    = $user_id;
        $data["last_mod_by"]   = $user_id;
        $data["created_date"]  = Carbon::now()->toDateTimeString();                     
        $data["last_mod_date"] = Carbon::now()->toDateTimeString();
        
        return DB::table("crm_notes")->insertGetId($data); 
    }
    
    
    public function getSysUserById($sys_userid) {
        return DB::table('system_user')->where('user_id', $sys_userid)->value("name");
    }
    
    
    
}