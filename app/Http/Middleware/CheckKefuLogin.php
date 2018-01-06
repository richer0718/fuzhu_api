<?php

namespace App\Http\Middleware;

use Closure;

class CheckKefuLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!session('kefuusername') || !session('kefupower')){
            return redirect('kefu/login');
        }
        //检验此用户 跟权限是不是一套的
        $array = session('kefupower');
        if($array['kefuusername'] != session('kefuusername')){
            return redirect('kefu/login');
        }
        //dd($array);
        return $next($request);
    }
}
