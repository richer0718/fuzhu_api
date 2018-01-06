<?php

namespace App\Http\Controllers\Kefu;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Mews\Captcha\Facades\Captcha;

class IndexController extends Controller
{
    //
    public function login(){
        return view('kefu/login');
    }
    public function loginout(Request $request){
        $request->session()->flush();
        return redirect('kefu/login');
    }

    public function loginRes(Request $request){
        if(!Captcha::check($request -> input('code'))){
            return redirect('kefu/login')->with('code', 'error')->withInput( $request->flash() );
        }

        $username = $request -> input('username');
        $password = $request -> input('password');
        $res = DB::table('kefu') -> where([
            'username'=>$username,
            'password'=>$password,
        ]) -> first();

        $res = (array)$res;
        if($res){

            session([
                'kefupower' => [
                    'kefuusername' => $res['username'],
                    'power1' => $res['power_1'],
                    'power2' => $res['power_2'],
                    'power3' => $res['power_3'],
                    'power4' => $res['power_4'],
                ]
            ]);
            session([
                'kefuusername' => $res['username'],
                'type' => 'kefu',
            ]); //储存登陆标志

            //通过权限判断他跳转到哪里
            if($res['power_1'] == 1){
                return redirect('kefu/number')->with('login_status', 'success');
            }
            if($res['power_2'] == 1){
                return redirect('kefu/searchOrder')->with('login_status', 'success');
            }

            if($res['power_3'] == 1){
                return redirect('kefu/number/1')->with('login_status', 'success');
            }

            if($res['power_4'] == 1){
                return redirect('kefu/number/1')->with('login_status', 'success');
            }else{
                return redirect('kefu/login')->with('status', 'error');
            }



        }else{
            return redirect('kefu/login')->with('status', 'error');
        }
        //var_dump($res);
    }

}
