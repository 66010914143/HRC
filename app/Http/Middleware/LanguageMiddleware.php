<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ถ้ามี Session ภาษาที่เลือก ให้ตั้งค่า Locale ของระบบเป็นภาษานั้น
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            App::setLocale('th'); // ภาษาเริ่มต้นถ้ายังไม่ได้เลือก
        }

        return $next($request);
    }
}