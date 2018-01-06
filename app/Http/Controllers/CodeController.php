<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;

class CodeController extends Controller
{
    //
    public function index(){
        return Captcha::create('default');
    }
    public function clearSession (Request $request){
        $request->session()->flush();
    }
}
