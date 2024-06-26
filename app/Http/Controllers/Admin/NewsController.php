<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\NewsType;
use App\Models\News;
use App\Models\Tags;
use App\Models\Language;
use Storage;
class NewsController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   public function index(Request $request)
   {
      $language = Language::all();
      $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default APP_LANG_ADMIN trong file env
      $path  = "news";
      $strsearch = $request->search;
      $re_name  = $request->re_name? RewriteUrlUnique($request->Id, $request->re_name, 'news', 're_name') : RewriteUrlUnique($request->Id, $request->title, 'news', 're_name');
      if($request->isMethod('get')){
         $listcat = NewsType::where('language_id', $current_language)->orderBy('priority', 'desc')->get();
         if($strsearch)
            $list = News::whereHas('newscat',function($query) use ($current_language){$query->where('language_id', $current_language);})->where('title', 'like', '%'.$strsearch.'%')->select(DB::raw('*, DATE_FORMAT(updated_at,\'%d/%m/%Y %H:%i\') as updated, DATE_FORMAT(activedate,\'%d/%m/%Y\') as actdate'))->orderBy('priority', 'desc')->paginate(15);
         else
            $list = News::whereHas('newscat',function($query) use ($current_language){$query->where('language_id', $current_language);})->select(DB::raw('*, DATE_FORMAT(updated_at,\'%d/%m/%Y %H:%i\') as updated, DATE_FORMAT(activedate,\'%d/%m/%Y\') as actdate'))->orderBy('priority', 'desc')->paginate(15);
         return view('Admin.News.detail', compact('list', 'listcat', 'current_language', 'language'));
      }elseif($request->deleteMode==1){
         $record = News::find($request->Id);
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
         $this->validateform($request); // validate database
         $fileimage = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
         $priority = $request->priority==0? News::where('cat_id', $request->cat_id)->max('priority')+1 : $request->priority;
         $record = News::updateOrCreate(
            ['id'=>$request->Id],
            ['cat_id'=>$request->cat_id,
             'title'=>$request->title,
             'brief'=>$request->brief,
             'content'=>$request->content,
             'tag' => $request->tag,
             'image'=>$fileimage,
             're_name'=>$re_name,
             'keyword'=>$request->keyword,
             'meta_description'=>$request->meta_description,
             'priority'=>$priority,
			 'activedate' => $request->activedate,
             'isactive'=>($request->isactive==1)? 1 : 0,
             'ishot' => ($request->ishot==1)? 1 : 0,
             'isdefault' => ($request->isdefault==1)? 1 : 0            
         ]);
         //$record->tags()->detach();
         $arraytags = $request->tag? explode(',', $request->tag) : [];
         $arraytag_id = [];
         foreach($arraytags as $item)
         {
            $trimitem = trim($item);
            $tag = Tags::firstOrCreate(['tag_name'=>$trimitem], ['re_name'=>Str::slug($trimitem, '-')]);
            if($tag)
               $arraytag_id[] = $tag->id;     
         }
         $record->tags()->sync($arraytag_id);
         if($request->isedit && $request->updated_at)
         {
            $record->updated_at = Carbon::createFromFormat('d/m/Y H:i', $request->updated_at)->format('Y-m-d H:i');
            $record->save();
         }
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
         'image.max'=>'Kích thước ảnh vượt quá giới hạn 2M'
      ]);
   }    
}
