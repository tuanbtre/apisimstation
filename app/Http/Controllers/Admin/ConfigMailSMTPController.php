<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ConfigMailSMTP;
use App\Models\Language;

class ConfigMailSMTPController extends Controller
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
            $record = ConfigMailSMTP::firstOr(function(){return new ConfigMailSMTP;});
            return view('Admin.Configmailsmtp.index', compact('record', 'current_language', 'language'));
        }elseif($request->deleteMode==1){
            $record = ConfigMailSMTP::find(1);
            if($record)
                $record->delete();
            return redirect()->back()->with(['Flass_Message'=>'Xóa dữ liệu thành công']);
        }else{
            $this->validateform($request);
            DB::table('config_mailsmtp')->upsert(['id'=>1, 'mail_host'=>$request->mail_host, 'mail_port'=>$request->mail_port, 'username'=>$request->username, 'password'=>$request->password, 'encryption'=>$request->encryption, 'from_address'=>$request->from_address], ['id']);
            return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
        }
    }
    //Kiểm tra dữ liệu 
    protected function validateform(Request $request)
    {
        $this->validate($request, [
            'mail_host' => 'required',
            'mail_port' => 'required',
            'username' => 'required',
            'password' => 'required',
        ],
        [
            'mail_host.required'=>'Vui lòng nhập vào mail_host',
            'mail_port.required'=>'Vui lòng nhập vào mail_port',
            'username.required'=>'Vui lòng nhập vào username',
            'password.required'=>'Vui lòng nhập vào mật khẩu',
        ]);
    }
}
