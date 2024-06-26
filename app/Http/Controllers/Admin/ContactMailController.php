<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactMail;
use App\Models\User;
use App\Models\Language;

class ContactMailController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   public function index(Request $request)
   {
    	$language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default =2
      $strsearch = $request->searchstr;
      if($request->isMethod('get')){
         $list = ContactMail::where(function($query) use($strsearch){if($strsearch)$query->where('email', 'like', '%'.$strsearch.'%');})->orderBy('created_at', 'desc')->paginate(8);
         return view('Admin.Contactmail.index', compact('list', 'current_language', 'language'));
      }elseif($request->deleteMode==1){//Xóa
         $record = ContactMail::find($request->Id);
         $record->delete();
         return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
      }     
   }
}
