<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CKEditorController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   public function upload(Request $request){
   	$validator = \Validator::make($request->all(), [
         'upload' => 'nullable|mimes:jpeg,bmp,png,webp|max:2048'
      ]);
      if ($validator->fails()) {
         $CKEditorFuncNum = $request->input('CKEditorFuncNum');
         $msg = 'File upload phải có địng dạng jpg,bmp,png,webp và nhỏ hơn 2Mb'; 
         $re = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '', '$msg')</script>";             
         // Render HTML output 
         @header('Content-type: text/html; charset=utf-8'); 
         return $re;
      }
      if($request->hasFile('upload')) {
         //get filename with extension
         $filenamewithextension = $request->file('upload')->getClientOriginalName();   
         //get filename without extension
         $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);      
         //get file extension
         $extension = $request->file('upload')->getClientOriginalExtension();      
         //filename to store
         $filenametostore = $filename.'_'.time().'.'.$extension;      
         //Upload File
         $request->file('upload')->storeAs('uploads', $filenametostore, 'public'); 
         $CKEditorFuncNum = $request->input('CKEditorFuncNum');
         $url = '/storage/uploads/'.$filenametostore; 
         $msg = 'File đã upload thành công'; 
         $re = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";             
         // Render HTML output 
         @header('Content-type: text/html; charset=utf-8'); 
         echo $re;
      }
   }
}
