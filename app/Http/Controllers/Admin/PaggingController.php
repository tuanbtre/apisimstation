<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pagging;
use App\Models\Language;

class PaggingController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
   public function index(request $request)
   {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default APP_LANG_ADMIN trong file env
      if($request->isMethod('get')){
         $list = Pagging::where('language_id', $current_language)->paginate(15);
         return view('Admin.Pagging.index', compact('list', 'current_language', 'language'));
      }elseif($request->deleteMode==1){ //Xóa
         $record = Pagging::find($request->Id);
         $record->delete();
         return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
      }else{
         $this->validatePagging($request); // validate database
         $priority = $request->priority==0? Pagging::where('language_id', $request->l)->max('priority')+1 : $request->priority;
         $record = Pagging::updateOrCreate(
            ['id'=>$request->Id],
            ['title'=>$request->title,
             'route_name'=>$request->route_name,
             'numofpage'=>$request->numofpage,
             'priority'=>$priority,
             'language_id'=>$request->l,
            ]);             
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
      }    
   }    
   //Kiểm tra dữ liệu 
   protected function validatePagging(Request $request)
   {
      $this->validate($request, [
         'route_name' => 'required|unique:tbl_pagging,route_name,'.$request->Id.',id,language_id,'.$request->l,
      ],
      [
         'route_name.required'=>'Vui lòng nhập vào mã trang',
         'route_name.unique'=>'Mã trang đã tồn tại'
      ]);
   }
}
