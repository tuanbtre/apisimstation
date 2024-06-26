<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\Language;
use Storage;
class StaticPageController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   public function index(Request $request)
   {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default APP_LANG_ADMIN trong file env
      $strsearch = $request->search;
      $path  = "staticpage";
      if($request->isMethod('get')){
         if($strsearch)
            $list = StaticPage::where([['language_id', $current_language], ['title', 'like', '%'.$strsearch.'%']])->paginate(15);
         else
            $list = StaticPage::where('language_id', $current_language)->paginate(15);
         return view('Admin.Staticpage.index', compact('list', 'current_language', 'language'));    
      }elseif($request->deleteMode==1){//Xóa
         $record = StaticPage::find($request->Id);
         $image = $record->image;
         if($image!==''){
            try{
               Storage::delete($path.'/'.$image);
            }catch(\Exception $e)
            {
               return redirect()->back()->with(['Flass_Message'=>'Xảy ra lỗi trong lúc xóa file ảnh']);   
            }
         }
         $record->delete();
         return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
      }else{
         $this->validateData($request);
         $fileimage = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
         $priority = $request->priority==0? StaticPage::where('language_id', $request->l)->max('priority')+1 : $request->priority;
         $record = StaticPage::updateOrCreate(
           ['id'=>$request->Id],
           ['title'=>$request->title,
            'pagecode'=>$request->pagecode,
            'brief'=>$request->brief,
            'content'=>$request->content,
            'image'=>$fileimage,
            'keyword'=>$request->keyword,
            'meta_description'=>$request->meta_description,
            'priority'=>$priority,
            'language_id'=>$request->l,
            'isdefault'=>($request->isdefault==1)? 1 : 0,
            'isactive'=>($request->isactive==1)? 1 : 0
           ]);             
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
      }      
   }   
  
   protected function validateData(Request $request)
   {
      $this->validate($request, [
         'title' => 'required',
         'pagecode' => 'required|unique:tbl_staticpage,pagecode,'.$request->Id.',id,language_id,'.$request->l,
         'image' => 'image|max:2048'
      ],
      [
         'title.required'=>'Vui lòng nhập vào tiêu đề',
         'pagecode.required' => 'Mã trang không được rỗng',
         'pagecode.unique' => 'Mã trang đã tồn tại',
         'image.image'=>'Ảnh không hợp lệ',
         'image.max'=>'Kích thước ảnh vượt quá giới hạn 2M'            
      ]);
   }
}
