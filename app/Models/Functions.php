<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Functions extends Model
{
   use HasFactory;
   protected $table = 'tbl_function';
   protected $fillable = [
      'id', 'url', 'route_name', 'controlleract', 'method', 'icon', 'title_vn', 'title_en', 'description', 'function_tab', 'can_grant', 'isshow', 'parent_id' 
   ];
    
   public function users()
   {
      return $this->belongsToMany('App\Models\User', 'tbl_permission', 'function_id', 'user_id');
   }

   public function sub(){
      return $this->hasMany('App\Models\Functions', 'parent_id', 'id');
   }
   public function parent()
   {
      return $this->belongsTo('App\Models\Functions', 'parent_id', 'id');
   }
   public function hasAccess(string $permissions) : bool
   {
      return ($this->route_name==$permissions)? true : false;
   }    
   public $timestamps = false;
}
