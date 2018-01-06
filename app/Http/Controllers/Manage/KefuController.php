<?php

namespace App\Http\Controllers\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class KefuController extends Controller
{
    //
    public function index(){
        $res = DB::table('kefu') -> paginate(100);
        foreach($res as $k => $vo){
            $temp = '';
            if($vo -> power_1 == 1){
                $temp .= '上传,';
            }
            if($vo -> power_2 == 1){
                $temp .= '查询,';
            }
            if($vo -> power_3 == 1){
                $temp .= '问题订单,';
            }
            if($vo -> power_4 == 1){
                $temp .= '完成订单,';
            }

            $res[$k] -> quanxian_list = substr($temp,0,strlen($temp) -1);
        }

        return view('manage/kefu/index') -> with([
            'res' => $res
        ]);
    }

    public function addKefu(){
        return view('manage/kefu/addKefu');
    }

    public function addKefuRes(Request $request){
        if($request -> input('username') && $request -> input('password') && $request -> input('name') && $request -> input('tel')){
            //判断 用户名是否存在
            $isset = DB::table('kefu') -> where([
                'username' => trim($request -> input('username'))
            ]) -> first();
            if($isset){
                //用户名存在
                return redirect('manage/addKefu') -> with('isset','yes') ->withInput($request->flash());
            }
            //权限数组
            $quanxian_arr = $request -> input('quanxian');
            //dd($quanxian_arr);
            //上传
            if(in_array(1,$quanxian_arr)){
                $power_1 = 1;
            }else{
                $power_1 = 0;
            }

            //查询
            if(in_array(2,$quanxian_arr)){
                $power_2 = 1;
            }else{
                $power_2 = 0;
            }

            //问题订单
            if(in_array(3,$quanxian_arr)){
                $power_3 = 1;
            }else{
                $power_3 = 0;
            }

            //完成订单
            if(in_array(4,$quanxian_arr)){
                $power_4 = 1;
            }else{
                $power_4 = 0;
            }




            //录入
            $res = DB::table('kefu') -> insert([
                'username' => trim($request -> input('username')),
                'password' => trim($request -> input('password')),
                'zhiwei' => trim($request -> input('zhiwei')),
                'name' => trim($request -> input('name')),
                'tel' => trim($request -> input('tel')),
                'remark' => trim($request -> input('remark')),
                'power_1' => $power_1,
                'power_2' => $power_2,
                'power_3' => $power_3,
                'power_4' => $power_4,
                'created_at' => time(),
                'updated_at' => time(),
                'fname' => session('username')
            ]);
            if($res){
                return redirect('manage/kefu') -> with('addres','success');
            }else{
                return redirect('manage/kefu') -> with('addres','error');
            }
        }
    }

    public function editKefu($id){
        $res = DB::table('kefu') -> where([
            'id' => $id
        ]) -> first();
        return view('manage/kefu/editKefu') -> with([
            'res' => $res
        ]);
    }

    public function editKefuRes(Request $request){
        //权限数组
        $quanxian_arr = $request -> input('quanxian');
        //上传
        if(in_array(1,$quanxian_arr)){
            $power_1 = 1;
        }else{
            $power_1 = 0;
        }

        //查询
        if(in_array(2,$quanxian_arr)){
            $power_2 = 1;
        }else{
            $power_2 = 0;
        }

        //问题订单
        if(in_array(3,$quanxian_arr)){
            $power_3 = 1;
        }else{
            $power_3 = 0;
        }

        //完成订单
        if(in_array(4,$quanxian_arr)){
            $power_4 = 1;
        }else{
            $power_4 = 0;
        }

        $res = DB::table('kefu') -> where([
            'id' => $request -> input('id')
        ]) -> update([
            'password' => trim($request -> input('password')),
            'zhiwei' => trim($request -> input('zhiwei')),
            'name' => trim($request -> input('name')),
            'tel' => trim($request -> input('tel')),
            'remark' => trim($request -> input('remark')),
            'power_1' => $power_1,
            'power_2' => $power_2,
            'power_3' => $power_3,
            'power_4' => $power_4,
            'updated_at' => time(),
        ]);

        if($res){
            return redirect('manage/kefu') -> with('updateres','success');
        }
    }

    //删除
    public function deleteKefu($id){
        $res = DB::table('kefu') -> where([
            'id' => $id,
            'fname' => session('username')
        ]) -> delete();
        if($res){
            return redirect('manage/kefu') -> with('deleteres','success');
        }else{
            return redirect('manage/kefu') -> with('deleteres','error');
        }
    }
}
