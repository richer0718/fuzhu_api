<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class NumberController extends Controller
{
    //
    public function index(Request $request){
        //配置
        $areas = config('setting.areas');
        $maps = config('setting.maps');
        $modes = config('setting.modes');
        $statuss = config('setting.statuss');

        //代理统计
        $res_daili = DB::table('daili') -> get();

        //$res_all = DB::table('daili') -> get();
        $count_guaji = 0;
        $count_lishi = 0;
        $count_all = 0;
        $count_point = 0;
        $count_point_all = 0;

        foreach($res_daili as $vo){
            $count_guaji += $vo -> number_guaji;
            $count_lishi += $vo -> number_lishi;
            $count_all += $vo -> number_all;
            $count_point += $vo -> point;
            $count_point_all += $vo -> point_all;
        }

        $res = DB::table('number') -> where(function($query) use($request){
            if($request -> input('number')){
                $query -> where('number','like','%'.trim($request -> input('number')).'%' );
                //dd($request -> input('number'));
            }
            if($request -> input('daili')){
                $query -> where('add_user','like','%'.trim($request -> input('daili')).'%' );
                //dd($request -> input('number'));
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
        }) ->  orderBy('save_time','desc') -> paginate(3000);

        foreach($res as $k => $vo){
            $res[$k] -> area = $areas[$vo -> area];
            $res[$k] -> map = $maps[$vo -> map];
            $res[$k] -> mode = $modes[$vo -> mode];
            $res[$k] -> status = $statuss[$vo -> status];
        }

        //挂机中的数量
        $count_guaji = DB::table('number') -> where('status','>',0) -> count();
        $count_paidui = DB::table('number') -> where('status','=',0) -> count();
        return view('admin/number/index') -> with([
            'res' => $res,
            'count_guaji'=>$count_guaji,
            'count_paidui'=>$count_paidui,
            'areas'=>$areas,
            'maps' => $maps,
            'statuss' => $statuss,
            'count_guaji_daili' => $count_guaji,
            'count_lishi' => $count_lishi,
            'count_all' => $count_all,
            'count_point' => $count_point,
            'count_point_all' => $count_point_all,
        ]);
    }
}
