<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class News extends Model
{
   use HasFactory;
   protected $table = 'news';

   protected $fillable = [
      'id', 'cat_id', 'title', 'brief', 'content', 'tag', 'keyword', 'meta_description', 'image', 're_name', 'priority', 'isactive', 'ishot', 'isdefault', 'activedate' 
   ];
    
   public function newscat()
   {
      return $this->belongsTo('App\Models\NewsType', 'cat_id', 'id');
   }
   public function tags()
   {
      return $this->morphToMany('App\Models\Tags', 'taggable');
   }
   public function getActivedateAttribute($value)
   {
      try {
         return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('d/m/Y');  
      } catch (\Exception $e) {
         return null;
      }      
   }
   public function setActivedateAttribute($value)
   {
      try {
         $this->attributes['activedate'] = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
      } catch (\Exception $e) {
         $this->attributes['activedate'] = null;
      }   
   }
   public $timestamps = false;
}
