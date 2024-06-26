<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\MetaHeader;
use App\Models\Language;

class MetaHeaderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    public function index(request $request)
    {
        $language = Language::all();
        $current_language = $request->l?? config('admin.lang', 2); //kiểm tra ngôn ngữ nếu ko có lấy default APP_LANG_ADMIN trong file env
        if($request->isMethod('get')){
            $record = MetaHeader::where('language_id', $current_language)->first();
            return view('Admin.MetaHeader.index', compact('record', 'current_language', 'language'));
        }else{
            $this->validateMetaheader($request);
            if($request->hasfile('image')){
                $file_name = 'logo_'.Language::find($current_language)->url_name.'.'.$request->file('image')->extension();
                $request->file('image')->storeAs('', $file_name);
            }else{
                $record = MetaHeader::where('language_id', $current_language)->first();
                $file_name = $record? $record->image : '';
            }
            MetaHeader::updateOrCreate(['language_id'=>$current_language],['title'=>$request->title, 'keyword'=>$request->keyword, 'meta_description'=>$request->meta_description, 'image'=>$file_name]);
            return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
        }
    }
    //Kiểm tra dữ liệu 
    protected function validateMetaheader(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'keyword' => 'required',
            'meta_description' => 'required'
        ],
        [
            'title.required'=>'Vui lòng nhập vào tiêu đề',
            'keyword.required'=>'Vui lòng nhập vào keyword',
            'meta_description.required'=>'Vui lòng nhập vào meta-description'
        ]);
    }
}
