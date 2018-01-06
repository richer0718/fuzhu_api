<?php

namespace App\Http\Controllers\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    //
    public function index(){
        $res = DB::table('notice') -> orderBy('is_top','desc') -> orderBy('created_at','desc') -> get();
        return view('manage/notice/index') -> with([
            'res' => $res
        ]);
    }

    public function detail($id){
        $res = DB::table('notice') -> where([
            'id' => $id
        ]) -> first();
        return view('manage/notice/detail') -> with([
            'res'=> $res
        ]);
    }


}
