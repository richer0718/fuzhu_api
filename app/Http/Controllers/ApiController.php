<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    //表1
    public function uploadNumber(){
        if($_GET['name'] && $_GET['passwd'] && $_GET['info']){
            $repeat = DB::table('newtable') -> where([
                'name' => trim($_GET['name'])
            ]) -> first();
            if($repeat){
                echo 'repeat';
            }else{
                //插入
                $res = DB::table('newtable') -> insert([
                    'name' => trim($_GET['name']),
                    'passwd' => trim($_GET['passwd']),
                    'info' => trim($_GET['info']),
                ]);
                if($res){
                    echo 'success';
                }else{
                    echo 'error';
                }
            }
        }else{
            echo 'error';
        }
    }

    public function deleteNumber(){
        if($_GET['name']){
            $res = DB::table('newtable') -> where([
                'name' => trim($_GET['name'])
            ]) -> delete();
            if($res){
                echo 'success';
            }else{
                echo 'nosuccess';
            }
        }else{
            echo 'error';
        }
    }



    //表2
    public function  updateDeviceData(){
        if($_GET['info'] && $_GET['info2']){
            $number = DB::table('newtable') -> where([
                'info' => trim($_GET['info']),
                'mark' => NULL
            ]) -> first();
            if($number){
                $isset = DB::table('newtable2') -> where([
                    'info2' => trim($_GET['info2'])
                ]) -> first();
                if($isset){
                    //更新
                    $res = DB::table('newtable2') -> where([
                        'info2' => trim($_GET['info2'])
                    ]) -> update([
                        'info' => trim($_GET['info']),
                        'name' => $number -> name,
                        'passwd' => $number -> passwd,
                        'timee' => time()
                    ]);
                }else{
                    //找一个
                    //插入
                    $res = DB::table('newtable2') -> insert([
                        'info' => trim($_GET['info']),
                        'name' => $number -> name,
                        'passwd' => $number -> passwd,
                        'info2' => trim($_GET['info2']),
                        'timee' => time()
                    ]);
                }

                if($res){
                    DB::table('newtable') -> where([
                        'id' => $number -> id
                    ]) -> update([
                        'mark' => 1
                    ]);
                    echo $number->name.','.$number -> passwd;
                }else{
                    echo 'error';
                }
            }else{
                echo 'nouse';
            }

        }else{
            echo 'error';
        }
    }


    //表2获取
    public function getDeviceData(){
        if($_GET['info2']){
            $isset = DB::table('newtable2') -> where([
                'info2' => trim($_GET['info2'])
            ]) -> first();
            if($isset){
                echo $isset -> name.','.$isset -> passwd;
            }else{
                echo 'nodata';
            }
        }else{
            echo 'error';
        }
    }



    //表4
    public function addNumberTable4(){
        if($_GET['name'] && $_GET['pwe'] ){
            $isset = DB::table('newtable4') -> where([
                'name' => trim($_GET['name'])
            ])->first();
            if($isset){
                echo 'isset';
            }else{
                $res = DB::table('newtable4') -> insert([
                    'name' => trim($_GET['name']),
                    'pwe' => trim($_GET['pwe']),
                    'wheree' => trim($_GET['wheree']),
                    'beizhu1' => trim($_GET['beizhu1']),
                    'beizhu2' => trim($_GET['beizhu2']),
                    'beizhu3' => trim($_GET['beizhu3']),
                    'beizhu4' => trim($_GET['beizhu4']),
                    'created_at' => time()
                ]);
                if($res){
                    echo 'success';
                }else{
                    echo 'error';
                }
            }
        }else{
            echo 'error';
        }
    }

    public function deleteNumberTable4(){
        if($_GET['name']){
            $res = DB::table('newtable4') -> where([
                'name' => $_GET['name']
            ]) -> delete();
            if($res){
                echo 'success';
            }else{
                echo 'error';
            }
        }else{
            echo 'error';
        }
    }


    public function updateNumberTable4(){
        //替换：就是，不管存在不存在，直接覆盖。没有就上传，有就替换
        if($_GET['name']){
            $isset = DB::table('newtable4') -> where([
                'name' => trim($_GET['name'])
            ])->first();
            if($isset){
                //更新
                $res = DB::table('newtable4') -> where([
                    'name' => trim($_GET['name']),
                ])-> update([
                    'pwe' => trim($_GET['pwe']),
                    'wheree' => trim($_GET['wheree']),
                    'beizhu1' => trim($_GET['beizhu1']),
                    'beizhu2' => trim($_GET['beizhu2']),
                    'beizhu3' => trim($_GET['beizhu3']),
                    'beizhu4' => trim($_GET['beizhu4']),
                    'created_at' => time()
                ]);
                if($res){
                    echo 'success';
                }else{
                    echo 'error';
                }
            }else{
                //新增
                $res = DB::table('newtable4') -> insert([
                    'name' => trim($_GET['name']),
                    'pwe' => trim($_GET['pwe']),
                    'wheree' => trim($_GET['wheree']),
                    'beizhu1' => trim($_GET['beizhu1']),
                    'beizhu2' => trim($_GET['beizhu2']),
                    'beizhu3' => trim($_GET['beizhu3']),
                    'beizhu4' => trim($_GET['beizhu4']),
                    'created_at' => time()
                ]);
                if($res){
                    echo 'success';
                }else{
                    echo 'error';
                }
            }


        }else{
            echo 'error';
        }
    }

    public function getNumberTable4(){
        if($_GET['name']){
            $res = DB::table('newtable4') -> where([
                'name' => trim($_GET['name']),
            ])->first();
            if($res){
                echo $res->name.','.$res->pwe.','.$res->wheree.','.$res->beizhu1.','.$res->beizhu2.','.$res->beizhu3.','.$res->beizhu4;

                //return response() -> json($res);
            }
        }else{
            echo 'error';
        }
    }


    public function addNumberTable3(){
        if($_GET['name'] && $_GET['passwd'] && $_GET['info']&& $_GET['jiange2']){
            $repeat = DB::table('newtable3') -> where([
                'name' => trim($_GET['name'])
            ]) -> first();
            if($repeat){
                echo 'repeat';
            }else{
                //插入
                $res = DB::table('newtable3') -> insert([
                    'name' => trim($_GET['name']),
                    'passwd' => trim($_GET['passwd']),
                    'info' => trim($_GET['info']),
                    'jiange2' => trim($_GET['jiange2']),
                ]);
                if($res){
                    echo 'success';
                }else{
                    echo 'error';
                }
            }
        }else{
            echo 'error';
        }
    }

    //自动程序 从表3跑到表1中
    public function autoRunTable3(){
        $time = time();
        $numbers = DB::table('newtable3')
            -> where('jiange2', '<=', $time)
            -> get();
        //把这些号全放到1表
        if($numbers){
            foreach($numbers as $vo){

                $repeat = DB::table('newtable') -> where([
                    'name' => $vo -> name
                ]) -> first();
                if($repeat){
                    //更新
                    $res = DB::table('newtable') -> where([
                        'name' => $vo -> name
                    ])->update([
                        'passwd' => $vo -> passwd,
                        'info' => $vo -> info,
                        'mark' => NULL
                    ]);
                }else{
                    //插入
                    $res = DB::table('newtable') -> insert([
                        'name' => $vo -> name,
                        'passwd' => $vo -> passwd,
                        'info' => $vo -> info,
                    ]);
                }

                //删除
                DB::table('newtable3')
                    -> where('name', $vo -> name)
                    -> delete();

            }
        }else{
            echo 'nonumber';
        }
    }


    public function getNumberTable5(){
        if($_GET['info']){
            $res = DB::table('newtable5') -> where([
                'info' => trim($_GET['info'])
            ]) -> first();
            if($res){
                echo $res -> tezhen;
            }else{
                echo 'nodata';
            }
        }else{
            echo 'error';
        }
    }
/*
* * * * * /usr/bin/curl http://feifeifuzhu.com/fuzhu_test/public/api/autoRunTable3
* * * * * sleep 10; /usr/bin/curl http://feifeifuzhu.com/fuzhu_test/public/api/autoRunTable3
* * * * * sleep 20; /usr/bin/curl http://feifeifuzhu.com/fuzhu_test/public/api/autoRunTable3
* * * * * sleep 30; /usr/bin/curl http://feifeifuzhu.com/fuzhu_test/public/api/autoRunTable3
* * * * * sleep 40; /usr/bin/curl http://feifeifuzhu.com/fuzhu_test/public/api/autoRunTable3
* * * * * sleep 50; /usr/bin/curl http://feifeifuzhu.com/fuzhu_test/public/api/autoRunTable3
*/











}
