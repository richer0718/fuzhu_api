<?php

namespace App\Http\Controllers\Admin;

use App\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    //获取账号情况 api/getNumberData/{number}/{area}

    public function getNumberData($number,$area){
        $res = DB::table('number') -> select(
            'add_user',
            'is_jiaji',
            'number',
            'pass',
            'area',
            'xiaoqu',
            'use_time',
            'save_time',
            'map',
            'mode',
            'status',
            'device',

            'updated_time'
        ) -> where([
            'number' => $number,
            'area' => $area
        ]) -> first();

        if($res){
            $str = implode(',',(array)$res);
            echo $str;
        }else{
            echo 'error';
        }

    }

    //获取验证码
    public function getCode($number,$area){
        $res = DB::table('number') -> select(
            'yanzhengma'
        ) -> where([
            'number' => $number,
            'area' => $area
        ]) -> first();
        //获取的时候，把这个验证码变为0
        DB::table('number') -> where([
            'number' => $number,
            'area' => $area
        ]) -> update([
            'yanzhengma' => 0
        ]);

        if($res){
            echo $res -> yanzhengma;
        }else{
            echo 'error';
        }
    }



    //修改消耗次数，剩余次数   updateNumber/{number}/{area}/{save_time}/{map}/{mode}/{status}/{device}
    public function updateNumber($number,$area,$save_time,$map,$mode,$status,$device){
        $res = DB::table('number') -> where([
            'number' => $number,
            'area' => $area
        ]) -> update([
            'save_time' => $save_time,
            'map' => $map,
            'mode' => $mode,
            'status' => $status,
            'device' => $device,
            'updated_time' => time()
        ]);

        if($res){
            echo 'success';
        }else{
            echo 'error';
        }

    }

    //我输入4个参数，分别是，代理账号，游戏账号，扣除费用，代刷次数
    public function getLongNumber($daili,$number,$point,$save_time){
        $res = DB::table('daili') -> where([
            'username' => $daili
        ]) -> first();
        if(intval($res -> point) >= intval($point)){
            DB::table('daili')->where([
                'username' => $daili
            ]) -> decrement('point',$point);
            DB::table('number') -> where([
                'number' => $number
            ]) -> update([
                'save_time' => $save_time,
                'updated_time' => time()
            ]);
            //记录日志
            $log = new Log();
            //$username = '',$type = '' ,$point = 0,$zhanghao = '',$zhucema = '',$remark = ''
            $log -> write($daili,'代挂',$point,$number);

            echo 'success';
        }else{
            //把，“剩余次数”，改成0，“挂机状态,”改成 -21
            DB::table('number') -> where([
                'number' => $number
            ]) -> update([
                'save_time' => 0,
                'status' => -21
            ]);
            echo 'notenough';
        }
    }

    //通过系数名称获取
    public function getXishuByCode($code){
        $res = DB::table('xishu') -> where([
            'code' => $code
        ]) -> first();

        if($res){
            echo $res -> number;
        }

    }


    //回收点数  recoverPoint/{number}/{area}/{point}/{remark}
    public function recoverPoint($number,$area,$point,$remark){
        //看下这个账号存在不存在
        $number_info = DB::table('number') -> where([
            'number' => $number,
            'area' => $area
        ]) -> first();

        if($number_info){

            DB::table('number') -> where([
                'number' => $number,
                'area' => $area
            ]) -> update([
                //剩余次数变为0
                'save_time' => 0,
                'updated_time' => time()
            ]);


            //代理的剩余点数增加
            //挂机中数量-一 历史数量+1
            DB::table('daili') -> where([
                'username' => $number_info -> add_user
            ]) -> increment('point',intval($point));
            DB::table('daili') -> where([
                'username' => $number_info -> add_user
            ]) -> increment('number_lishi');
            DB::table('daili') -> where([
                'username' => $number_info -> add_user
            ]) -> decrement('number_guaji');


            //记录回收日志
            $log = new Log();
            $log -> write($number_info -> add_user,'回收',intval($point),$number,'',$remark);
            echo 'success';
        }else{
            echo 'error';
        }


    }

    public function autoGetData(){
        $url1 = 'http://222.185.25.254:8088/jsp1/show11?name=&ck=2&info=IOSWZRY-2&mark=&time=';
        $url2 = 'http://222.185.25.254:8088/jsp1/show11?name=&ck=2&info=AZWZRY-2&mark=&time=';


        $data1 = file_get_contents($url1);
        if(!strpos($data1,'数据总计为') || !$data1){
            $res1 = '99999';
        }else{
            $count1 = substr_count($data1,'null');
            $res1 = $count1;
        }


        $data2 = file_get_contents($url2);
        if(!strpos($data2,'数据总计为') || !$data2){
            $res2 = '99999';
        }else{
            $count2 = substr_count($data2,'null');
            $res2 = $count2;
        }

        DB::table('note') -> where([
            'id' => 1
        ]) -> update([
            'url1' => $res1,
            'url2' => $res2,
            'update_time' => time()
        ]);
    }






}
