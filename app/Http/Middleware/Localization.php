<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ตรวจสอบว่าใน Session มีการเก็บค่าภาษา (locale) ไว้ไหม
        if (Session::has('locale')) {
            // บังคับให้ Laravel เปลี่ยนไปใช้ภาษาที่ดึงมาจาก Session (th, en, my)
            App::setLocale(Session::get('locale'));
        }

        return $next($request);
    }
}