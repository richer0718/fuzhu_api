<?php

namespace App\Http\Controllers\Manage;

use App\Log;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class NumberController extends Controller
{
    //
    public function index($url_status = null,Request $request){
        //var_dump($url_status);exit;

        //配置
        $areas = config('setting.areas');
        $maps = config('setting.maps');
        $modes = config('setting.modes');
        $statuss = config('setting.statuss');

        $status_name = '';
        if($url_status == '1'){
            $status_name = '完成订单';
        }elseif($url_status == '3'){
            $status_name = '问题订单';
        }elseif($url_status == '2'){
            $status_name = '长期账号';
        }
        $add_user = session('username');
        $res = DB::table('number') -> where(function($query) use($url_status,$add_user,$request){
            $query -> where('add_user','=',$add_user);
            if($request -> input('number')){
                $query -> where('number','like','%'.trim($request -> input('number')).'%' );
                //dd($request -> input('number'));
            }else{
                if($url_status == '1'){
                    //历史账号 -> 完成订单 0
                    $query -> whereIn('status',[0,-1]);

                }elseif($url_status == '3'){
                    //问题订单  -1
                    $query -> where('status','<',-1);
                }elseif($url_status == '2'){
                    //长期账号
                    $query -> where('mode','>',0);
                    //$query -> where('status','>=',0);
                }else{
                    //挂机 检测时间 asc
                    $order1 = '';
                    //$query -> where('mode','=',0);
                    $query -> where('status','>',0);
                }


                if($request -> input('area')){
                    $query -> where('area',trim($request -> input('area')));
                }
                if($request -> input('map')){
                    $query -> where('map',trim($request -> input('map')));
                }
                if($request -> input('status')){
                    $query -> where('status',trim($request -> input('status')));
                }
            }





        })  -> orderBy('created_time','desc') -> paginate(3000);
        //dd($res);
        foreach($res as $k => $vo){
            $res[$k] -> area_name = $areas[$vo -> area];
            $res[$k] -> map = $maps[$vo -> map];
            $res[$k] -> mode = $modes[$vo -> mode];
            $res[$k] -> status = $statuss[$vo -> status];
        }

        //返回价格列表
        $price = DB::table('xishu') -> get();
        $price_str = '';
        foreach($price as $vo){
            $price_str .= $vo -> remark.':'.$vo -> number.'  ';
        }

        //查找此代理的信息
        $userinfo = DB::table('daili') -> where([
            'username' => session('username')
        ]) -> first();


        //看服务器返回值
        $note = DB::table('note') -> where([
            'id' => 1
        ]) -> first();
        $note_res = '目前排队账号:';
        $note_res .= '</br>';
        if($note -> url1 == '99999'){
            $note_res .= '苹果：服务器异常';
            $note_res .= '<br>';
        }else{
            $note_res .= '苹果：'.$note -> url1.'个';
            $note_res .= '<br>';
        }

        if($note -> url2 == '99999'){
            $note_res .= '安卓：服务器异常';
        }else{
            $note_res .= '安卓：'.$note -> url2.'个';
        }
        return view('manage/number/index') -> with([
            'res' => $res,
            'url_status' => $url_status,
            'areas'=>$areas,
            'maps' => $maps,
            'statuss' => $statuss,
            'price_str' => $price_str,
            'userinfo' => $userinfo,
            'status_name' => $status_name,
            'note_res' => $note_res
        ]);
    }



    public function addNumber(){
        $xishus = DB::table('xishu') -> get();
        $info = null;
        if(session('info')){
            $info = session('info');
        }
        return view('manage/number/addNumber') -> with([
            'info' => $info,
            'xishus' => $xishus
        ]);
    }

    public function addNumberRes(Request $request){
        //判断必填
        if(!$request -> input('number') || !$request -> input('pass') || !$request -> input('area') || !$request -> input('map') || !$request -> input('save_time')){
            return false;
        }
       //先判断下此账号是否存在与此系统中
        $isset = DB::table('number') -> where([
            'number' => $request -> input('number')
        ]) -> first();
        if($isset && $isset -> 	save_time > 0){
            return redirect('manage/number') -> with('isset','该账号有剩余代刷次数没有回收，请回收后，再上传！如有疑问，请联系QQ：972102275');
        }


        //该代理账号的“总点数”大于或等于“刷图次数*系数”
        //根据大区，获取系数
        $xishus = DB::table('xishu') -> where([
            'code' => $request -> input('area')
        ]) -> first();
        //用户选择的系数
        $xishu = $xishus -> number;
        $userinfo = DB::table('daili') -> where([
            'username' => session('username')
        ]) -> first();
        //代理的点数
        $point_user = intval($userinfo -> point);
        //要扣除的点数
        $point_cut = intval($xishu) * intval($request -> input('save_time'));
        if($request -> input('jiaji')){
            $point_cut = $point_cut * 1.5;
        }
        if($point_user >= $point_cut){
            //他的余额够支付
            //调他的接口
            $daqu = $request -> input('area');
            $number = $request -> input('number');
            $pass = $request -> input('pass');

            //IOSWZRY-2  IOSWZRY-2
            $string = substr($request -> input('area'),0,2);

            if($request -> input('jiaji')){
                $jiaji = 1;
                $endstr = 3;
            }else{
                $endstr = 2;
                $jiaji = 0;
            }
            if($string == 'AZ'){
                $youxi = $userinfo -> upload.'AZWZRY-'.$endstr;
            }else{
                $youxi = $userinfo -> upload.'IOSWZRY-'.$endstr;
            }

            //（当前时间+上号时间*60）*1000'
            $jiange = intval(time() + intval($request -> input('shanghao_time'))*3600  ) * 1000;
            $pass = 'aaaa';
            $url = 'http://222.185.25.254:8088/jsp1/input3.jsp?name='.$daqu.'-'.$number.'&passwd='.$pass.'&info='.$youxi.'&jiange='.$jiange;
            $url2 = 'http://222.185.25.254:8088/jsp1/delete3.jsp?name='.$daqu.'-'.$number;
            //var_dump($url);
            //var_dump($url2);exit;
            $result = file_get_contents($url);
            if(!strstr($result,'添加成功')){
                $result2 = file_get_contents($url2);

                //删除之后 再调用
                $result = file_get_contents($url);
                if(!strstr($result,'添加成功')){
                    //添加失败
                    return redirect('manage/number') -> with('isset','上传失败，联系QQ：972102275');
                }
            }

            //添加成功后
            //扣除他的点数
            DB::table('daili') -> where([
                'username' => session('username')
            ]) -> decrement('point',$point_cut);

            //记录扣除日志
            $log = new Log();
            //将字符串转换成中文
            switch ($request -> input('area')){
                case 'AZQQ':$temp_area = '安卓QQ';break;
                case 'AZVX':$temp_area = '安卓微信';break;
                case 'IOSQQ':$temp_area = '苹果QQ';break;
                case 'IOSVX':$temp_area = '苹果微信';break;
            }

            switch ($request -> input('map')){
                case 'DS':$temp_map = '大师';break;
                case 'JY':$temp_map = '精英';break;
                case 'PT':$temp_map = '普通';break;
            }


            $log -> write(session('username'),'挂机',$point_cut,$request -> input('number'),'',$temp_area.','.$request -> input('xiaoqu').','.$temp_map.','.$request -> input('save_time').'次,'.$request -> input('order_id'));

            if($isset){
                //如果存在 则删除老数据
                DB::table('number') -> where([
                    'id' => $isset -> id
                ]) -> delete();
            }
            //不存在 直接新增
            if($request -> input('mark')){
                $mark = 1;
            }else{
                $mark = 0;
            }
            $res = DB::table('number') -> insert([
                'is_jiaji' => $jiaji,
                'is_mark' => $mark,
                'order_id' => $request -> input('order_id'),
                'wangwang' => $request -> input('wangwang'),
                'xiaoqu' => $request -> input('xiaoqu'),
                'number' => $request -> input('number'),
                'pass' => $request -> input('pass'),
                'area' => $request -> input('area'),
                'map' => $request -> input('map'),
                'save_time' => $request -> input('save_time'),
                'use_time' => $request -> input('save_time'),
                'mode' => $request -> input('mode'),
                'shanghao_time' => intval($request -> input('shanghao_time')) * 3600,
                'end_date' => $request -> input('end_date'),
                'wangwang_type' => $request -> input('wangwang_type'),
                'remark' => $request -> input('remark'),
                'created_time' => time(),
                'updated_time' => time(),
                'add_user' => session('username'),
            ]);

            //挂机中数量加一 总账号数量+1
            DB::table('daili') -> where([
                'username' => session('username')
            ]) -> increment('number_guaji');

            DB::table('daili') -> where([
                'username' => session('username')
            ]) -> increment('number_all');

            return redirect('manage/number') -> with('insertres','yes');

        }else{
            //不够支付，返回
            return redirect('manage/addNumber')->withInput($request->flash());
        }



        //先判断她的点数


    }

    public function rechargeRes(Request $request){
        //调接口充值
        $req_res = file_get_contents('http://feifeifuzhu.com/feifei/index.php/Admin/getCode/zhucema/'.$request -> input('code').'/youxi/WZRY');
        $req_res = trim($req_res);
        //dd($req_res);
        if($req_res){
            if($req_res == 'error'){
                //充值失败
                return redirect('manage/number') -> with('rechargeres','error');
            }else{
                $code = explode(',',$req_res);
                //判断code
                if(count($code) == 9 && $code[0] == 0 && $code[1] == 0 && $code[2] == 0 && $code[3] == 0 && $code[4] == 'WZRY' && intval($code[5]) > 0 && $code[8] == 0){
                    //code  符合要求 返回页面让他确认
                    return redirect('manage/number') -> with('recharge_true',array('point'=>$code[5],'code'=>$request -> input('code')));
                }else{
                    //充值失败
                    return redirect('manage/number') -> with('rechargeres','error');
                }
            }
        }else{
            //充值失败
            return redirect('manage/number') -> with('rechargeres','error');
        }




    }

    //确认充值
    public function rechargeConfirm(Request $request){
        //调接口验证第二次传来的数据
        $req_res = file_get_contents('http://feifeifuzhu.com/feifei/index.php/Admin/getCode/zhucema/'.$request -> input('code').'/youxi/WZRY');
        $req_res = trim($req_res);
        var_dump($req_res);
        if($req_res){
            if($req_res == 'error'){
                //充值失败
                return redirect('manage/number') -> with('rechargeres','error');
            }else{
                $code = explode(',',$req_res);
                //判断code
                if(count($code) == 9 && $code[0] == 0 && $code[1] == 0 && $code[2] == 0 && $code[3] == 0 && $code[4] == 'WZRY' && intval($code[5]) > 0 && $code[8] == 0){

                    //正式充值成功
                    //将这个代码作废

                    $delete_url = 'http://feifeifuzhu.com/feifei/index.php/Admin/uploadNumber/bh1/'.session('username').'/bh2/'.$code[5].'/bh3/1/bh4/1/youxi/WZRY/shuliang/0/zhucema/'.$request -> input('code').'/leixing/dk/daoqishijian/-'.time();
                    $delete_code = file_get_contents($delete_url);
                    //var_dump($delete_code);

                    if(strstr($delete_code,'success')){

                        //var_dump($delete_code);
                        //代理点数加
                        //DB::connection()->enableQueryLog();
                        DB::table('daili') -> where([
                            'username' => session('username')
                        ]) -> increment('point', intval($code[5]));

                        DB::table('daili') -> where([
                            'username' => session('username')
                        ]) -> increment('point_all',intval($code[5]));


                        //$log = DB::getQueryLog();
                        //dd($log);   //打印sql语句
                        //记录日志
                        $log = new Log();
                        $log -> write(session('username'),'充值',intval($code[5]),'',$request -> input('code'));
                        //echo 11;exit;
                        //充值成功
                        return redirect('manage/number') -> with('rechargeres','success');
                    }else{
                        //echo 22;exit;
                        return redirect('manage/number') -> with('rechargeres','error');
                    }




                }else{
                    //充值失败
                    return redirect('manage/number') -> with('rechargeres','error');
                }
            }
        }else{
            //充值失败
            return redirect('manage/number') -> with('rechargeres','error');
        }
    }

    //充值日志
    public function log(Request $request){
        $logs = DB::table('recharge_log') -> where(function($query) use($request){
            $query -> where('username',session('username'));
            if($request -> input('zhanghao')){
                $query -> where('zhanghao',$request -> input('zhanghao'));
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
        }) -> orderBy('created_at','desc') -> get();

        $count_point = 0;
        foreach($logs as $vo){
            $vo -> class_name = '';
            //计算总数
            switch ($vo -> log_type){
                case '充值':$count_point = $count_point + intval($vo -> point);break;
                case '挂机':$count_point = $count_point - intval($vo -> point);$vo -> class_name='super';break;
                case '违约':$count_point = $count_point - intval($vo -> point);$vo -> class_name='super';break;
                case '回收':$count_point = $count_point + intval($vo -> point);break;
            }
        }

        return view('manage/number/log') -> with([
            'logs' => $logs,
            'count_point' => $count_point
        ]);
    }

    //停挂之前，检查该账号的信息
    public function stopNumber(Request $request){
        //代理点击“确认”后， 挂机状态 改成“手动停挂”参数是-1，挂机设备，改为0，检测时间改为当前时间
        $number_info = DB::table('number') -> where([
            'id' => $request -> input('id'),
            'add_user' => session('username')
        ]) -> first();

        //找出他的系数
        $xishus = DB::table('xishu') -> where([
            'code' => $number_info -> area
        ]) -> first();
        //单价
        $danjia = $xishus -> number;
        //总共返还的点数
        $price_all = intval($danjia) * intval($number_info -> save_time);

        //查下此人点数

        $userinfo = DB::table('daili') -> where([
            'username' => session('username')
        ]) -> first();
        $point_user = $userinfo -> point;
        //var_dump($point_user);
        //var_dump($price_all);
        if(intval($point_user) + intval($price_all) <100){
            //需要给他更新的点数为
            $poing_result = 0;



        }else{
            $poing_result = $point_user + $price_all - 100 ;
            ////把违约 剩下的钱还给他

        }

        //返还多少点 就是回收多少点
        $log_res = new Log();
        $log_res -> write(session('username'),'回收',intval($price_all),$number_info -> number);




        //var_dump($poing_result);exit;

        DB::table('number') -> where([
            'id' => $request -> input('id'),
            'add_user' => session('username')
        ]) -> update([
            'status' => '-1',
            'device' => 0,
            'save_time'=>0,
            'updated_time' => time()
        ]);

        //更新扣的点数
        DB::table('daili') -> where([
            'username' => session('username')
        ]) -> update([
            'point' => $poing_result
        ]);

        //挂机中数量减一
        DB::table('daili') -> where([
            'username' => session('username')
        ]) -> decrement('number_guaji');
        //历史数量加一
        DB::table('daili') -> where([
            'username' => session('username')
        ]) -> increment('number_lishi');


        //扣点数

        //扣除违约点数
        $log = new Log();
        $log -> write(session('username'),'违约',$point_user - $poing_result + $price_all);


        return redirect('manage/number') -> with('stopres','yes');
    }

    //重新上传账号
    public function uploadNumber($id){
        $res = DB::table('number') -> where([
            'id' => $id
        ]) -> first();
        //把此账号的信息带过去
        return redirect('manage/addNumber') -> with('info',$res);
    }

    public function delete_number($id){
        $res = DB::table('number') -> where([
            'id' => $id,
            'add_user' => session('username'),
            'save_time' => 0
        ]) -> delete();
        if($res){

            //代理的总数量减一
            DB::table('daili') -> where([
                'username' => session('username')
            ]) -> decrement('number_all');

            return redirect('manage/number/3') -> with('delete_number','success');
        }else{
            return redirect('manage/number/3') -> with('delete_number','error');
        }
    }

    public function yanzhengma(Request $request){
        $res = DB::table('number') -> where([
            'id' => $request -> input('yanzheng_id')
        ]) -> update([
            'yanzhengma' => $request -> input('yanzhengma')
        ]);
        if($res){
            return redirect('manage/number') -> with('yanzhengma','success');
        }else{
            return redirect('manage/number') -> with('yanzhengma','error');
        }

    }

    //客服列表
    public function kefu(){
        $res = DB::table('kefu') -> paginate(100);
        return view('manage/kefu') -> with([
            'res' => $res
        ]);
    }


    public function xiugaiRes(Request $request){
        if($request -> input('is_mark') == 'on'){
            $is_mark = 1;
        }else{
            $is_mark = 0;
        }
        //开始修改
        $res = DB::table('number') -> where([
            'number' => $request -> input('show_number')
        ]) -> update([
            'order_id' => $request -> input('order_id'),
            'wangwang_type' => $request -> input('wangwang_type'),
            'wangwang' => $request -> input('wangwang'),
            'is_mark' => $is_mark
        ]);

        return redirect('manage/number/1');


    }

    //删除所有
    public function deleteAllData(Request $request){
        $datas = $request -> input('data');
        $arr = explode(',',$datas);
        foreach($arr as $vo){
            //删除每个id
            $res = DB::table('number') -> where([
                'id' => $vo,
                'add_user' => session('username'),
                'save_time' => 0
            ]) -> delete();
        }
        echo 'success';
    }

    //导出
    public function exportFile(Request $request){
        set_time_limit(0);
        $data = $request -> input('data');
        $data = explode(',',$data);
        //大区
        $areas = config('setting.areas');
        //地图
        $maps = config('setting.maps');

        $res_arr[] = ['订单编号','旺旺/QQ','游戏账号','游戏密码','大区','小区','代挂次数','代挂地图','检测时间','操作时间'];
        foreach($data as $k => $vo){
            //查这个id的信息
            $temp = DB::table('number') -> where([
                'id' => $vo
            ]) -> first();
            $res_arr[] = [$temp -> order_id,$temp -> wangwang,$temp->number,$temp->pass,$areas[$temp->area],$temp->xiaoqu,$temp->use_time,$maps[$temp->map],date('Y-m-d H:i',$temp->updated_time),date('Y-m-d H:i',$temp->created_time)];

        }

        Excel::create('订单'.date('Y-m-d'),function($excel) use ($res_arr){
            $excel->sheet('order', function($sheet) use ($res_arr){
                $sheet->rows($res_arr);
            });
        })->export('xls');

    }

}



