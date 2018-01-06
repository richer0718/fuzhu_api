<?php

namespace App\Http\Controllers\Kefu;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Log;

class NumberController extends Controller
{
    //
    public function addNumber(){
        //判断权限
        //dd(session('kefupower'));
        if(session('kefupower')['kefuusername'] != session('kefuusername') || !session('kefupower')['power1']){
            return redirect('kefu/login');
        }

        $xishus = DB::table('xishu') -> get();
        $info = null;
        if(session('info')){
            $info = session('info');
        }

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



        return view('kefu/addNumber') -> with([
            'info' => $info,
            'xishus' => $xishus,
            'note_res' => $note_res
        ]);
    }


    public function addNumberRes(Request $request){
        if(session('kefupower')['kefuusername'] != session('kefuusername') || !session('kefupower')['power1']){
            return redirect('kefu/login');
        }
        //判断必填
        if(!$request -> input('number') || !$request -> input('pass') || !$request -> input('area') || !$request -> input('map') || !$request -> input('save_time')){
            return false;
        }

        //先判断下此账号是否存在与此系统中
        $isset = DB::table('number') -> where([
            'number' => $request -> input('number')
        ]) -> first();
        if($isset && $isset -> 	save_time > 0){
            return redirect('kefu/number') -> with('isset','该账号有剩余代刷次数没有回收，请回收后，再上传！如有疑问，请联系代理');
        }

        //该代理账号的“总点数”大于或等于“刷图次数*系数”
        //根据大区，获取系数
        $xishus = DB::table('xishu') -> where([
            'code' => $request -> input('area')
        ]) -> first();
        //用户选择的系数
        $xishu = $xishus -> number;



        //查他代理的点数
        //先查他属于哪个代理
        $kefuinfo = DB::table('kefu') -> where([
            'username' => session('kefuusername')
        ]) -> first();

        if(!$kefuinfo){
            echo 'error';exit;
        }

        $userinfo = DB::table('daili') -> where([
            'username' => $kefuinfo -> fname
        ]) -> first();

        if(!$userinfo){
            echo 'error';exit;
        }


        //代理的点数
        $point_user = intval($userinfo -> point);

        //要扣除的点数
        $point_cut = intval($xishu) * intval($request -> input('save_time'));
        if($request -> input('jiaji')){
            $point_cut = $point_cut * 1.5;
        }
        if($point_user >= $point_cut){
            //全部正确 开始上传

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
            $jiange = intval(time() + intval($request -> input('shanghao_time'))*3600  )* 1000;

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
                    return redirect('kefu/number') -> with('isset','上传失败，请联系代理');
                }
            }
            //添加成功后
            //扣除他的点数
            DB::table('daili') -> where([
                'username' => $userinfo -> username
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
            $log -> write(session('username'),'挂机',$point_cut,$request -> input('number'),'',$temp_area.','.$request -> input('xiaoqu').','.$temp_map.','.$request -> input('save_time').'次,'.$request -> input('order_id'),session('kefuusername'));

            if($isset){
                //如果存在 则删除老数据
                DB::table('number') -> where([
                    'id' => $isset -> id
                ]) -> delete();
            }
            if($request -> input('mark')){
                $mark = 1;
            }else{
                $mark = 0;
            }
            //不存在 直接新增
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
                'add_user' => $userinfo -> username,
                'kefu_name' => session('kefuusername')
            ]);

            //挂机中数量加一 总账号数量+1
            DB::table('daili') -> where([
                'username' => $userinfo -> username
            ]) -> increment('number_guaji');

            DB::table('daili') -> where([
                'username' => $userinfo -> username
            ]) -> increment('number_all');
        }else{
            //不够支付，返回
            return redirect('kefu/number')-> with('pointerror','yes') -> withInput($request->flash());
        }
    }



    //完成订单
    public function index($url_status = null,Request $request){
        if(session('kefupower')['kefuusername'] != session('kefuusername') || !session('kefupower')['power4'] ){
            return redirect('kefu/login');
        }
        //配置
        $areas = config('setting.areas');
        $maps = config('setting.maps');
        $modes = config('setting.modes');
        $statuss = config('setting.statuss');
        $status_name = '';

        if($url_status == '1'){
            $status_name = '完成订单';

        }
        if($url_status == '3'){
            $status_name = '问题订单';
        }

        //查找她的代理是谁
        $daili_info = DB::table('kefu') -> where([
            'username' => session('kefuusername')
        ]) -> first();
        if(!$daili_info){
            echo '请重新登录';exit;
        }

        $res = DB::table('number') -> where(function($query) use($url_status,$daili_info,$request){
            $query -> where('add_user',$daili_info -> fname);
            if($url_status == '1'){
                //历史账号 -> 完成订单 0
                $query -> whereIn('status',[0,-1]);
            }elseif($url_status == '3'){
                //问题订单  -1
                $query -> where('status','=',-1);
            }
            if($request -> input('number')){
                $query -> where('number','like',trim($request -> input('number')) );
            }
            if($request -> input('order_id')){
                $query -> where('order_id','=',trim($request -> input('order_id')) );
            }



        })  -> orderBy('created_time','desc')  -> paginate(1000);
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


        return view('kefu/index') -> with([
            'res' => $res,
            'url_status' => $url_status,
            'areas'=>$areas,
            'maps' => $maps,
            'statuss' => $statuss,
            'price_str' => $price_str,
            'userinfo' => $userinfo,
            'status_name' => $status_name
        ]);
    }



    //问题订单
    public function index2($url_status = null,Request $request){
        if(session('kefupower')['kefuusername'] != session('kefuusername') || !session('kefupower')['power3'] ){
            return redirect('kefu/login');
        }

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
        }

        //查找她的代理是谁
        $daili_info = DB::table('kefu') -> where([
            'username' => session('kefuusername')
        ]) -> first();
        if(!$daili_info){
            echo '请重新登录';exit;
        }

        $res = DB::table('number') -> where(function($query) use($url_status,$daili_info,$request){
            $query -> where('add_user',$daili_info -> fname);
            if($url_status == '1'){
                //历史账号 -> 完成订单 0
                $query -> where('status','=',0);

            }elseif($url_status == '3'){
                //问题订单  -1
                $query -> where('status','<',-1);
            }

            if($request -> input('number')){
                $query -> where('number','like',trim($request -> input('number')) );
            }
            if($request -> input('order_id')){
                $query -> where('order_id','=',trim($request -> input('order_id')) );
            }





        }) -> orderBy('created_time','desc')  -> paginate(1000);
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



        return view('kefu/index') -> with([
            'res' => $res,
            'url_status' => $url_status,
            'areas'=>$areas,
            'maps' => $maps,
            'statuss' => $statuss,
            'price_str' => $price_str,
            'status_name' => $status_name
        ]);
    }


    //查询
    public function searchOrder(Request $request){
        if(session('kefupower')['kefuusername'] != session('kefuusername') || !session('kefupower')['power2'] ){
            return redirect('kefu/login');
        }
        //配置
        $areas = config('setting.areas');
        $maps = config('setting.maps');
        $modes = config('setting.modes');
        $statuss = config('setting.statuss');

        //查找她的代理是谁
        $daili_info = DB::table('kefu') -> where([
            'username' => session('kefuusername')
        ]) -> first();
        if(!$daili_info){
            echo '请重新登录';exit;
        }

        $res = DB::table('number') -> where(function($query) use($daili_info,$request){
            $query -> where('add_user',$daili_info -> fname);
            if($request -> input('number')){
                $query -> where('number','like',trim($request -> input('number')) );
            }
            if($request -> input('order_id')){
                $query -> where('order_id','=',trim($request -> input('order_id')) );
            }

            if(!$request -> input('number') && !$request -> input('order_id') ){
                $query -> where('id',0);
            }


        }) -> limit(1) -> get();
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


        $status_name = '查询';
        return view('kefu/index') -> with([
            'res' => $res,
            'status_name' => $status_name,
            'areas'=>$areas,
            'maps' => $maps,
            'statuss' => $statuss,
            'price_str' => $price_str,
            'nolink' => 1,
            'url_status' => ''
        ]);
    }

    public function stopNumber(Request $request){
        if(session('kefupower')['kefuusername'] != session('kefuusername')){
            return redirect('kefu/login');
        }


        //通过kefuusername 找到代理
        $kefuinfo = DB::table('kefu') -> where([
            'username' => session('kefuusername')
        ]) -> first();
        if(!$kefuinfo){
            return false;
        }



        //代理点击“确认”后， 挂机状态 改成“手动停挂”参数是-1，挂机设备，改为0，检测时间改为当前时间
        $number_info = DB::table('number') -> where([
            'id' => $request -> input('id'),
            'add_user' => $kefuinfo -> fname
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
            'username' => $kefuinfo -> fname
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
        $log_res -> write(session('username'),'回收',intval($price_all),$number_info -> number,'','',session('kefuusername'));




        //var_dump($poing_result);exit;

        DB::table('number') -> where([
            'id' => $request -> input('id'),
            'add_user' => $kefuinfo -> fname
        ]) -> update([
            'status' => '-1',
            'device' => 0,
            'save_time'=>0,
            'updated_time' => time()
        ]);

        //更新扣的点数
        DB::table('daili') -> where([
            'username' => $kefuinfo -> fname
        ]) -> update([
            'point' => $poing_result
        ]);

        //挂机中数量减一
        DB::table('daili') -> where([
            'username' => $kefuinfo -> fname
        ]) -> decrement('number_guaji');
        //历史数量加一
        DB::table('daili') -> where([
            'username' => $kefuinfo -> fname
        ]) -> increment('number_lishi');


        //扣点数

        //扣除违约点数
        $log = new Log();
        $log -> write(session('username'),'违约',$point_user - $poing_result + $price_all,'','','',session('kefuusername'));

        return redirect('kefu/searchOrder')->with('stop_number', 'success');

    }

    public function xiugaiRes(Request $request){
        if(session('kefupower')['kefuusername'] != session('kefuusername')){
            return redirect('kefu/login');
        }
        //查下此账号是不是他添加的
        //得到代理的账号 $daili_info -> fname
        $daili_info = DB::table('kefu') -> where([
            'username' => session('kefuusername')
        ]) -> first();

        $number_info = DB::table('number') -> where([
            'add_user' => $daili_info -> fname,
            'number' => $request -> input('show_number')
        ]) -> first();
        if(!$number_info){
            return false;
        }

        //开始修改
        $res = DB::table('number') -> where([
            'number' => $request -> input('show_number')
        ]) -> update([
            'order_id' => $request -> input('order_id'),
            'wangwang_type' => $request -> input('wangwang_type'),
            'wangwang' => $request -> input('wangwang'),
        ]);

        //通过权限判断他跳转到哪里
        if(session('kefupower')['power1'] == 1){
            return redirect('kefu/number')->with('update_status', 'success');
        }
        if(session('kefupower')['power2'] == 1){
            return redirect('kefu/searchOrder')->with('update_status', 'success');
        }

        if(session('kefupower')['power3'] == 1){
            return redirect('kefu/number/3')->with('update_status', 'success');
        }

        if(session('kefupower')['power4'] == 1){
            return redirect('kefu/number/1')->with('update_status', 'success');
        }else{
            return redirect('kefu/login')->with('status', 'error');
        }


    }



}
