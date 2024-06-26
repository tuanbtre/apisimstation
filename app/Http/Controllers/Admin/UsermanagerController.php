<?php

namespace App\Http\Controllers\Admin;

use App;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use App\Models\User;
use Storage;
use Hash;
class UsermanagerController extends Controller
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
    public function index(request $request)
    {
        $path  = "user";
		if($request->isMethod('get')){
			$list = User::where('isadmin', 0)->paginate(15);
			if($str=$request->search)
			$list = User::where('name', 'like', '%'.$str.'%')->paginate(15);        
			return view('Admin.Usermanager.index', compact('list'));	
		}elseif($request->deleteMode==1){
			$record = User::find($request->Id);
            $image = $record->image;
            $img = User::where([['id','<>',$request->Id],['image',$image]])->get()->toArray();
            if($image!=='' && !$img){
                try{
                    Storage::delete($path.'/'.$image);
                }
                catch(\Exception $e){
                    return redirect()->back()->with(['Flass_Message'=>'Có lỗi xảy ra khi xóa file ảnh']);
                }                
            }
            $record->delete();
            return redirect()->back()->with(['Flass_Message'=>'Xóa người dùng thành công']);	
		}else{
			if($request->Id==0){//thêm mới
				$this->validateUser($request); // validate database
				$fileimage = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
				$tbl_user = new User;
				$tbl_user->name = $request->name;
				$tbl_user->username = $request->username;
				$tbl_user->image = $fileimage;
				$tbl_user->email = $request->email;
				$tbl_user->phone = $request->phone;
				$tbl_user->password = Hash::make($request->password);
				$tbl_user->isactive = ($request->isactive==1)? 1 : 0;
				$tbl_user->save();
				
				return redirect()->back()->with(['Flass_Message'=>'Lưu dữ liệu thành công']);
			}else{//cập nhật
				$recorduser = User::find($request->Id);
				$fileimage = FunctionUpload($request->isDelete, $path, 'image', $request->oldimage);
				if($request->changePass==1)
				{
					$this->validateChangePass($request);
					$recorduser->password = Hash::make($request->password);
				}else{
					$this->validateEditeUser($request, $request->Id);
					$recorduser->name = $request->name;
					$recorduser->username = $request->username;
					$recorduser->image = $fileimage;
					$recorduser->email = $request->email;
					$recorduser->phone = $request->phone;
					$recorduser->isactive = ($request->isactive==1)? 1 : 0;	
				}            
				$recorduser->save();
				return redirect()->back()->with(['Flass_Message'=>'Cập nhật dữ liệu thành công']);
			}	
		}		
    }
    //Kiểm tra dữ liệu 
    protected function validateUser(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'username' => 'required|unique:users',
            'email'		=>'required|unique:users',
            'password'	=>'required',
            'password_confirmation'		=> 'required_with:password|same:password'
        ],
        [
            'name.required'=>'Vui lòng nhập tên người dùng',
            'username.required'=>'Tên tài khoản không được để trống',
            'username.unique'=>'Tên tài khoản đã tồn tại',
            'email.required'	=> 'Email không được để trống',
            'email.unique' => 'Email đã tồn tại',
            'password.required'	=> 'Mật khẩu không được để trống',
            'password_confirmation.required_with'	=>'Chưa xác nhận mật khẩu',
            'password_confirmation.same'	=> 'Xác nhận mật khẩu không trùng khớp' 
        ]);
    }
    protected function validateChangePass(Request $request)
    {
    	$this->validate($request, [
            'password'	=>'required',
            'password_confirmation'		=> 'required_with:password|same:password'
        ],
        [
            'password.required'	=> 'Mật khẩu không được để trống',
            'password_confirmation.required_with'	=>'Chưa xác nhận mật khẩu',
            'password_confirmation.same'	=> 'Xác nhận mật khẩu không trùng khớp' 
        ]);
    }
    protected function validateEditeUser(Request $request, $id)
    {
    	$this->validate($request, [
            'name' => 'required',
            'username' => 'required|unique:users,username,'.$id,
            'email'		=>'required|unique:users,email,'.$id,
        ],
        [
            'name.required'=>'Vui lòng nhập tên người dùng',
            'username.required'=>'Tên tài khoản không được để trống',
            'username.unique'=>'Tên tài khoản đã tồn tại',
            'email.required'	=> 'Email không được để trống',
            'email.unique' => 'Email đã tồn tại',
        ]);
    }
}