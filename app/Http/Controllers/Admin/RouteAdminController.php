<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Functions;
use App\Models\Language;
use Storage;

class RouteAdminController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   public function index(Request $request)
   {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default APP_LANG_ADMIN trong file env
      $path  = "icon";
	  $strsearch = $request->search;
      if($request->isMethod('get')){
         $parent = Functions::where('parent_id', 0)->get(); 
         $list = Functions::where(function ($query) use($strsearch) {if($strsearch)$query->where('title', 'like', '%'.$strsearch.'%');})->orderBy('parent_id')->orderBy('id')->paginate(15);
         return view('Admin.Routeadmin.index', compact('list', 'parent', 'current_language', 'language'));
      }elseif($request->deleteMode==1){
         $record = Functions::find($request->Id);
		 $icon = $record->icon;
         if($icon!==''){
            try{
              Storage::delete($path.'/'.$icon);    
            }catch(\Exception $e){
               return redirect()->back()->with(['Flass_Message'=>'Có lỗi xảy ra khi xóa file ảnh']);
            }                
         }
         $record->delete();
         return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
      }else{
         $this->validateForm($request); // validate database
         $icon = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
		 $record = Functions::updateOrCreate(
            ['id'=>$request->Id],
            ['title_en'=>$request->title_en,
			 'title_vn'=>$request->title_vn, 	
             'route_name'=>$request->route_name,
             'controlleract'=>$request->controlleract,
             'method'=>$request->method,
             'url'=>$request->url,
			 'icon'=>$icon,
             'parent_id'=>$request->parent_id,
             'description'=>$request->description,
             'function_tab'=>$request->function_tab,
			 'can_grant'=>($request->can_grant==1)? 1 : 0,
			 'isshow'=>($request->isshow==1)? 1 : 0,
         ]);
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
      }
   }
   protected function validateForm(Request $request)
   {
      $this->validate($request, [
         'title_vn' => 'required'
      ],
      [
         'title_vn.required'=>'Vui lòng nhập vào tên router'
      ]);
   }
}
