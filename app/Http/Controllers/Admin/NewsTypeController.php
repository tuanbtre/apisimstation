<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NewsType;
use App\Models\News;
use App\Models\Language;
use Storage;
class NewsTypeController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }

   public function index(Request $request)
   {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default APP_LANG_ADMIN trong file env
      $path  = "news/category";
      $strsearch = $request->search;
      $re_name  = $request->re_name? RewriteUrlUnique($request->Id, $request->re_name, 'news_cat', 're_name', $current_language) : RewriteUrlUnique($request->Id, $request->title, 'news_cat', 're_name', $current_language);
      if($request->isMethod('get')){
         if($strsearch)
            $list =NewsType::where([['language_id', $current_language], ['title', 'like', '%'.$strsearch.'%']])->orderBy('priority','desc')->paginate(15);  
         else
            $list =NewsType::where('language_id', $current_language)->orderBy('priority','desc')->paginate(15);  
         return view('Admin.News.index', compact('list','current_language', 'language'));
      }elseif($request->deleteMode==1){//Xóa
         $record = NewsType::find($request->Id);
         $subcount = News::where('cat_id', $request->Id)->count();
         if($subcount)
            return redirect()->back()->with(['Flass_Message'=>'Dữ liệu đã sử dụng ở nơi khác']);
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
         $this->validateform($request); // validate database
         $fileimage = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
         $priority = $request->priority==0? NewsType::where('language_id', $request->l)->max('priority')+1 : $request->priority;
         $record = NewsType::updateOrCreate(
            ['id'=>$request->Id],
            ['title'=>$request->title,
             'image'=>$fileimage,
             're_name'=>$re_name,
             'priority'=>$priority,
             'language_id'=>$request->l,
             'isactive'=>($request->isactive==1)? 1 : 0
            ]);             
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
      }
   }    
   protected function validateform(Request $request)
   {
      $this->validate($request, [
         'title' => 'required',
         'image' => 'image|max:2048' 
      ],
      [
         'title.required'=>'Vui lòng nhập vào tiêu đề',
         'image.image'=>'Ảnh không hợp lệ',
         'image.max'=>'Kích thước ảnh vượt quá giới hạn 2MB'
      ]);
   }    
}
