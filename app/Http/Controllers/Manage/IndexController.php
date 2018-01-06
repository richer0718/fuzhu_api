<?php

namespace App\Http\Controllers\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Mews\Captcha\Facades\Captcha;

class IndexController extends Controller
{
    public function login(){
        return view('manage/login');

    }

    public function loginout(Request $request){
        $request->session()->flush();
        return redirect('manage/login');
    }

    public function loginRes(Request $request){
        if(!Captcha::check($request -> input('code'))){
            return redirect('manage/login')->with('code', 'error')->withInput( $request->flash() );
        }
        $username = $request -> input('username');
        $password = $request -> input('password');
        $res = DB::table('daili') -> where([
            'username'=>$username,
            'password'=>$password,
            'status' => 0
        ]) -> first();
        //dd($res);
        $res = (array)$res;
        if($res){
            //看下他是否有添加客服的权限
            if($res['is_kefu']){
                session([
                    'is_kefu' => $res['username']
                ]);
            }
            session([
                'username' => $res['username'],
                'type' => 'daili',
            ]); //储存登陆标志

            return redirect('manage/number')->with('login_status', 'success');
        }else{
            return redirect('manage/login')->with('status', 'error');
        }
        //var_dump($res);
    }

    public function regRes(Request $request){
        if(!Captcha::check($request -> input('code'))){
            return redirect('manage/login')->with('code', 'error')->withInput( $request->flash() );
        }
        //检测是否可以注册
        $is_reg = DB::table('reg') -> where([
            'id' => 1
        ]) -> first();
        if($is_reg -> is_reg == 1){
            return redirect('manage/login') ->with('stopreg','yes');
        }
        //dd($request);
        //检验是否重复
        $is_set = DB::table('daili') -> where([
            'username' => $request -> input('username')
        ]) -> first();
        if($is_set){
            return view('manage/login') -> with('nameisset','yes') -> withInput( $request->flash() );
        }
        DB::table('daili') -> insert([
            'username' => $request -> input('username'),
            'password' => $request -> input('newpassword'),
            'qq' => $request -> input('qq'),
            'tel' => $request -> input('tel'),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        return redirect('manage/login') -> with('regres','yes');


    }

    public function findPass(Request $request){
        $is_set = DB::table('daili') -> where([
            'username' => $request -> input('username2'),
            'tel' => $request -> input('tel2'),
        ]) -> first();
        if($is_set){
            //修改
            DB::table('daili') -> where([
                'username' => $request -> input('username2')
            ]) -> update([
                'password' => $request -> input('newpassword2')
            ]);
            return redirect('manage/login') -> with('findpass','success');
        }else{
            return redirect('manage/login') -> with('findpass','error');
        }
    }

    public function topdetail(){
        $res = DB::table('notice') -> orderBy('created_at','desc') -> first();
        return view('manage/notice/topdetail') -> with([
            'res' => $res
        ]);
    }


}
