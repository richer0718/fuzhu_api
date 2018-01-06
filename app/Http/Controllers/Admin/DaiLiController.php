<?php

namespace App\Http\Controllers\Admin;

use App\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DaiLiController extends Controller
{
    //
    public function index(Request $request) {
        $res = DB::table('daili') -> where(function($query) use($request){
            if( $request-> input('username')){
                $query -> where('username','like','%'.trim($request -> input('username')).'%' );
                //dd($request -> input('number'));
            }
        }) -> get();

        //$res_all = DB::table('daili') -> get();
        $count_guaji = 0;
        $count_lishi = 0;
        $count_all = 0;
        $count_point = 0;
        $count_point_all = 0;

        foreach($res as $vo){
            $count_guaji += $vo -> number_guaji;
            $count_lishi += $vo -> number_lishi;
            $count_all += $vo -> number_all;
            $count_point += $vo -> point;
            $count_point_all += $vo -> point_all;
        }

        //是否允许注册
        $is_reg = DB::table('reg') -> where([
            'id' => 1
        ]) -> first();
        return view('admin/daili/index') -> with([
            'res' => $res,
            'count_guaji' => $count_guaji,
            'count_lishi' => $count_lishi,
            'count_all' => $count_all,
            'count_point' => $count_point,
            'count_point_all' => $count_point_all,
            'is_reg' => $is_reg -> is_reg
        ]);
    }

    public function remark(Request $request){
        $res = DB::table('daili') -> where([
            'id' => $request -> input('remark_id')
        ]) -> update([
            'remark' => $request -> input('remark')
        ]);

        if($res){
            return redirect('admin/daili') -> with('remark','yes');
        }
    }


    public function chongzhi($id){
        DB::table('daili') -> where([
            'id' => $id
        ]) -> update([
            'password' => 'Abcdef001'
        ]);
        return redirect('admin/daili') -> with('chongzhi','yes');
    }


    //新增代理
    public function addDailiRes(Request $request){
        //检验是否重复
        $is_set = DB::table('daili') -> where([
            'username' => $request -> input('username')
        ]) -> first();
        if($is_set){
            return view('admin/daili') -> with('isset','yes');
        }

        DB::table('daili') -> insert([
            'username' => $request -> input('username'),
            'password' => $request -> input('password'),
            'upload' => $request -> input('upload'),
            'is_kefu' => $request -> input('is_kefu'),
            'tel' => $request -> input('tel'),
            'qq' => $request -> input('qq'),
            'remark' => $request -> input('remark'),
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        return redirect('admin/daili') -> with('insertres','yes');

    }

    //修改代理
    public function editDaili($id){
        $res = DB::table('daili') -> where([
            'id' => $id
        ]) -> first();
        return redirect('admin/daili') -> with('editdaili',$res);
    }

    //修改代理结果
    public function editDailiRes(Request $request){
        $res = DB::table('daili')->where([
            'id' => $request -> input('id')
        ]) -> update([
            'password' => $request -> input('password'),
            'tel' => $request -> input('tel'),
            'qq' => $request -> input('qq'),
            'remark' => $request -> input('remark'),
            'upload' => $request -> input('upload'),
            'is_kefu' => $request -> input('is_kefu'),
        ]);
        return redirect('admin/daili') -> with('editRes',$res);

    }

    //代理充值记录
    public function  recharge_log(Request $request){
        $res = DB::table('recharge_log') -> where(function($query) use($request){
            if($request -> input('log_type')){
                $query -> where('log_type',$request -> input('log_type'));
            }
            if($request -> input('zhanghao')){
                $query -> where('username',$request -> input('zhanghao'));
            }
            if($request -> input('log_type')){
                $query -> where('log_type',$request -> input('log_type'));
            }
            if(!empty($request -> createtime_left)){
                $query -> where('created_at','>',strtotime($request -> createtime_left));
            }

            if(!empty($request -> createtime_right)){
                $query -> where('created_at','<',strtotime($request -> createtime_right));
            }
        }) -> orderBy('created_at','desc') -> paginate(1000);

        $count_point = 0;
        foreach($res as $k => $vo){
            //扣点数的，用粗体，红色显示
            if($vo -> log_type == '违约' || $vo -> log_type == '挂机' ){
                $vo -> class_name = 'super';
            }else{
                $vo -> class_name = '';
            }

            //计算总数
            switch ($vo -> log_type){
                case '充值':$count_point = $count_point + intval($vo -> point);break;
                case '挂机':$count_point = $count_point - intval($vo -> point);$vo -> class_name='super';break;
                case '违约':$count_point = $count_point - intval($vo -> point);$vo -> class_name='super';break;
                case '回收':$count_point = $count_point + intval($vo -> point);break;
            }

        }
        return view('admin/daili/log') -> with([
            'res' => $res,
            'count_point' => $count_point
        ]);
    }



    //充值
    public function recharge(Request $request){
        //dd($request);
        DB::table('daili') -> where([
            'id' => $request -> input('recharge_id')
        ]) -> increment('point',intval($request -> input('recharge_point')));

        DB::table('daili') -> where([
            'id' => $request -> input('recharge_id')
        ])-> increment('point_all',intval($request -> input('recharge_point')));


        //添加充值记录
        $log = new Log();
        $log -> write($request -> input('recharge_username'),'充值',$request -> input('recharge_point'),'','',$request->input('recharge_remark'));
        return redirect('admin/daili') -> with('rechargeres','success');
    }

    //冻结账号
    public function dongjie($id){
        DB::table('daili') -> where([
            'id' => $id
        ]) -> update([
            'status' => 1
        ]);
        return redirect('admin/daili') -> with('dongjie','success');
    }

    //恢复账号
    public function huifu($id){
        DB::table('daili') -> where([
            'id' => $id
        ]) -> update([
            'status' => 0
        ]);
        return redirect('admin/daili') -> with('huifu','success');
    }

    //改变是否可以注册状态
    public function changeReg($data){
        DB::table('reg') -> where([
            'id' => 1
        ]) -> update([
            'is_reg' => $data
        ]);
        return redirect('admin/daili') -> with('is_reg','success');

    }


}
