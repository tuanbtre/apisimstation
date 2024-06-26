<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanyInfo;
use App\Models\Language;
use Storage;
class CompanyInfoController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   public function index(Request $request)
   {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default APP_LANG_ADMIN trong file env
      $path  = "companyinfo";
      if($request->isMethod('get')){
         $list = CompanyInfo::where('language_id', $current_language)->orderBy('priority')->paginate(15);        
         return view('Admin.CompanyInfo.index', compact('list', 'current_language', 'language'));
      }elseif($request->deleteMode==1){//Xóa
         $record = CompanyInfo::find($request->Id);
         $image = $record->image;
         if($image!==''){
            try{
               Storage::delete($path.'/'.$image);    
            }
            catch(\Exception $e){
               return redirect()->back()->with(['Flass_Message'=>'Có lỗi xảy ra khi xóa file ảnh']);
            }                
         }
         $record->delete();
         return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
      }else{
         $this->validateInfo($request);
         $fileimage = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
         $priority = $request->priority==0? CompanyInfo::where('language_id', $request->l)->max('priority')+1 : $request->priority;
         $record = CompanyInfo::updateOrCreate(
            ['id'=>$request->Id],
            ['title'=>$request->title,
             'code'=>$request->code,
             'link'=>$request->link,
             'content'=>$request->content,
             'image'=>$fileimage,
             'font_icon'=>$request->font_icon,
             'priority'=>$priority,
             'language_id'=>$request->l,
             'isactive'=>($request->isactive==1)? 1 : 0
            ]);             
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
      }        
   }
   protected function validateInfo(Request $request)
   {
      $this->validate($request, [
         'code' => 'required|unique:tbl_companyinfo,code,'.$request->Id.',id,language_id,'.$request->l,
         'image' => 'image|max:2048'
      ],
      [
         'code.required'=>'Vui lòng nhập vào mã code',
         'code.unique'=>'Mã code đã tồn tại',
         'image.image'=>'Ảnh không hợp lệ',
         'image.max'=>'Kích thước ảnh vượt quá giới hạn 2M'
      ]);
   }
}
