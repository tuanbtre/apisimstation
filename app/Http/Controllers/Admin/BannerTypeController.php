<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BannerType;
use App\Models\Banner;
use App\Models\RouteLanguage;
use App\Models\StaticPage;
use App\Models\Language;

class BannerTypeController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   public function index(Request $request)
    {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu không có lấy default =2
      $path  = "banner";
      $listrouter = RouteLanguage::where([['language_id', $current_language], ['parent_id', 0]])->get();
      if($request->isMethod('get')){
         $list = BannerType::where('language_id', $current_language)->orderBy('priority', 'desc')->paginate(15);
         $listpage = StaticPage::where([['language_id', $current_language], ['isactive', 1]])->orderBy('priority', 'desc')->get();
         return view('Admin.Banner.index', compact('list', 'current_language', 'language', 'listrouter', 'listpage'));
      }elseif($request->deleteMode==1){
         $record = BannerType::find($request->Id);
         $subcount = Banner::where('cat_id', $request->Id)->count();
         if($subcount)
            return redirect()->back()->with(['Flass_Message'=>'Dữ liệu đã sử dụng ở nơi khác']);
         $record->delete();
         return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
      }else{
         $this->validateForm($request); // validate database
         $priority = $request->priority==0? BannerType::where('language_id', $request->l)->max('priority')+1 : $request->priority;
         $record = BannerType::updateOrCreate(
            ['id'=>$request->Id],
            ['title'=>$request->title,
             'group'=>$request->group,
             'type'=>$request->type,
             'priority'=>$priority,
             'language_id'=>$request->l,
             'isactive'=>($request->isactive==1)? 1 : 0
         ]);             
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
      }        
   }
   //Kiểm tra dữ liệu 
   protected function validateForm(Request $request)
   {
      $this->validate($request, [
         'title' => 'required',
         'group' => 'required|unique:banner_type,group,'.$request->Id.',id,language_id,'.$request->l
      ],
      [
         'title.required'=>'Vui lòng nhập vào tiêu đề',
         'group.required' => 'Nhóm banner không được rỗng',
         'group.unique'=>'Nhóm banner '.$request->group.' đã tồn tại'
      ]);
   }
}
