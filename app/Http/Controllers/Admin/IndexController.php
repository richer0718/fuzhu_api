<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{

    public function getDataTable1(){
        //dump($_GET['info']);
        if($_GET['info']) {
            $res = DB::table('newtable')->whereIn('info', $_GET['info'])->get();
        }else{
            $res = DB::table('newtable')->get();
        }
        return view('admin/table1') -> with([
            'res' => $res
        ]);
    }


    public function getDataTable2(){
        if($_GET['info']) {
            $res = DB::table('newtable2')->whereIn('info', $_GET['info']) -> orderBy('info2','asc') ->get();
        }else{
            $res = DB::table('newtable2')-> orderBy('info2','asc')->get();
        }
        return view('admin/table2') -> with([
            'res' => $res
        ]);
    }

    public function getDataTable3(){
        if($_GET['info']){
            $res = DB::table('newtable3') -> whereIn('info',$_GET['info'])  -> get();
        }else{
            $res = DB::table('newtable3')  -> get();
        }

        return view('admin/table3') -> with([
            'res' => $res
        ]);
    }

    public function getDataTable4(){
        $res = DB::table('newtable4') -> get();
        return view('admin/table4') -> with([
            'res' => $res
        ]);
    }


}
