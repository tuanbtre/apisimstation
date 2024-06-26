<?php

namespace App\Http\Controllers\Admin;

use App;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use App\Models\About_us;
use App\Models\Language;
use Storage;
class AboutusController extends Controller
{
   /**
   * Create a new controller instance.
   *
   * @return void
   */
   public function __construct()
   {
      $this->middleware('auth:admin');
   }

   /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
   public function index(Request $request)
   {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default APP_LANG_ADMIN trong file env
      $path  = "about_us";
      $strsearch = $request->search;
      $re_name  = $request->re_name? RewriteUrlUnique($request->Id, $request->re_name, 'about_us', 're_name') : RewriteUrlUnique($request->Id, $request->title, 'about_us', 're_name');
      if($request->isMethod('get')){
         $list = About_us::where('language_id', $current_language)->where(function ($query) use($strsearch) {if($strsearch)$query->where('title', 'like', '%'.$strsearch.'%');})->orderBy('priority', 'desc')->paginate(15);
         return view('Admin.Aboutus.index', compact('list', 'current_language', 'language'));
      }elseif($request->deleteMode==1){
         $record = About_us::find($request->Id);
         $image = $record->image;
         if($image!==''){
            try{
              Storage::delete($path.'/'.$image);    
            }catch(\Exception $e){
               return redirect()->back()->with(['Flass_Message'=>'Có lỗi xảy ra khi xóa file ảnh']);
            }                
         }
         $record->delete();
         return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
      }else{
         $this->validateForm($request); // validate database
         $fileimage = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
         $priority = $request->priority==0? About_us::where('language_id', $request->l)->max('priority')+1 : $request->priority;
         $record = About_us::updateOrCreate(
            ['id'=>$request->Id],
            ['title'=>$request->title,
             'brief'=>$request->brief,
             'content'=>$request->content,
             'image'=>$fileimage,
             're_name'=>$re_name,
             'keyword'=>$request->keyword,
             'meta_description'=>$request->meta_description,
             'priority'=>$priority,
             'language_id'=>$request->l,
             'isactive'=>($request->isactive==1)? 1 : 0
         ]);
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
      }
   }
   protected function validateForm(Request $request)
   {
      $this->validate($request, [
         'title' => 'required',
         'image' => 'image|max:2048'
      ],
      [
         'title.required'=>'Vui lòng nhập vào tiêu đề',
         'image.image'=>'Ảnh không hợp lệ',
         'image.max'=>'Kích thước ảnh vượt quá giới hạn 2M'
      ]);
   }
}