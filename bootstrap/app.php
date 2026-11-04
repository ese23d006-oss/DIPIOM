<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(ForceHttps::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->alias(['role' => EnsureUserHasRole::class]);

        // Идэвхгүй болгосон хэрэглэгчийг нэвтэрсэн байсан ч шууд гаргана.
        $middleware->web(append: EnsureUserIsActive::class);

        $middleware->redirectUsersTo(
            fn (Request $request) => $request->user()->dashboardUrl()
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API-д алдаа үргэлж JSON хэлбэрээр: HTML хуудас буцаахгүй.
        // expectsJson-ийг хадгална: вэбийн AJAX (файл оруулах, явц, Япон шалгалт) үүнээс хамаардаг.
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());
    })->create();
