<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    //
    public function index(){
        $res = DB::table('notice') -> orderBy('is_top','desc') -> orderBy('created_at','desc') -> get();
        return view('admin/notice/index') -> with([
            'res' => $res
        ]);
    }

    public function addNotice(){
        return view('admin/notice/addNotice');
    }

    public function addNoticeRes(Request $request){
        DB::table('notice') -> insert([
            'title' => $request -> input('title'),
            'content' => $request -> input('content'),
            'is_top' => $request -> input('is_top'),
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        return redirect('admin/notice') -> with('insertres','yes');
    }

    public function edit($id){
        $res = DB::table('notice') -> where([
            'id' => $id
        ]) -> first();
        return view('admin/notice/addNotice') -> with([
            'res'=> $res
        ]);
    }

    public function detail($id){
        $res = DB::table('notice') -> where([
            'id' => $id
        ]) -> first();
        return view('admin/notice/detail') -> with([
            'res'=> $res
        ]);
    }

    public function editNoticeRes(Request $request){
        DB::table('notice') -> where([
            'id' => $request -> input('id')
        ]) -> update([
            'updated_at' => time(),
            'title' => $request -> input('title'),
            'content' => $request -> input('content'),
            'is_top' => $request -> input('is_top'),
        ]);
        return redirect('admin/notice') -> with('editres','yes');
    }

    public function delete_notice($id){
        DB::table('notice') -> where([
            'id' => $id
        ]) -> delete();
        return redirect('admin/notice') -> with('deleteres','yes');

    }
}
