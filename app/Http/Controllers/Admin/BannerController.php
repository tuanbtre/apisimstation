<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BannerType;
use App\Models\Banner;
use App\Models\RouteLanguage;
use App\Models\Language;
use Storage;

class BannerController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }

   public function index(int $id, Request $request)
   {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu không có lấy default =2
      $path  = "banner";
      $language_name = Language::where('id', $current_language)->value('url_name');
      $cat = BannerType::find($id);
      if($request->isMethod('get')){
         if($cat->type==1)
            $tmpl = 'Admin.Banner.video';
         else
            $tmpl = 'Admin.Banner.image';     
         $list = Banner::where('cat_id', $id)->orderBy('priority', 'desc')->paginate(15);
         return view($tmpl, compact('list', 'cat', 'current_language', 'language'));
      }elseif($request->deleteMode==1){
         $record = Banner::find($request->Id);
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
         if($cat->type==1)
            $this->validateVideoBanner($request); // validate database
         else
            $this->validateImageBanner($request);
         $fileimage = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
         $priority = $request->priority==0? Banner::where('cat_id', $request->cat_id)->max('priority')+1 : $request->priority;
         $record = Banner::updateOrCreate(
            ['id'=>$request->Id, 'cat_id'=>$request->cat_id],
            ['title'=>$request->title,
             'youtube'=>$request->youtube,
             'brief'=>$request->brief,
             'image'=>$fileimage,
             'url'=>$request->url,
             'priority'=>$priority,
             'popup'=>($request->popup==1)? 1 : 0,
             'isactive'=>($request->isactive==1)? 1 : 0
         ]);             
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
        }        
    }
    //Kiểm tra dữ liệu 
    protected function validateVideoBanner(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'image' => 'nullable|mimes:mp4|max:1048576'
        ],
        [
            'title.required'=>'Vui lòng nhập vào tiêu đề',
            'image.mimes'=>'Video không hợp lệ',
            'image.max'=>'Kích thước video vượt quá giới hạn 100MB'
        ]);
    }
    protected function validateImageBanner(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'image' => 'nullable|image|max:2048'
        ],
        [
            'title.required'=>'Vui lòng nhập vào tiêu đề',
            'image.image'=>'Ảnh không hợp lệ',
            'image.max'=>'Kích thước ảnh vượt quá giới hạn 2MB'
        ]);
    }    
}