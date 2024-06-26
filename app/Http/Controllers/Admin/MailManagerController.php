<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MailManager;

class MailManagerController extends Controller
{
   public function __construct()
   {
      $this->middleware('auth:admin');
   }
   public function index(request $request)
   {
      if($request->isMethod('get')){
         $list = MailManager::paginate(15);
        return view('Admin.Mailmanager.index', compact('list'));        
      }elseif($request->deleteMode==1){
         $record = MailManager::find($request->Id);
         $record->delete();
         return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
      }else{
         $this->validatemail($request);
         $record = MailManager::updateOrCreate(
            ['id'=>$request->Id],
            ['email'=>$request->email,
             'type'=>$request->type                 
            ]);             
         return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
      }
   }    
   //Kiểm tra dữ liệu 
   protected function validatemail(Request $request)
   {
      $this->validate($request, [
         'email' => 'required|email',
      ],
      [
         'email.required'=>'Vui lòng nhập vào email',
         'email.email'=>'Email không đúng định dạng',
      ]);
   }
}
