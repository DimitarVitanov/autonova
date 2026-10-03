<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ec3013">

        <title inertia>{{ config('app.name', 'AutoNova') }}</title>

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/favicon.svg">

        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        {{-- Splash loader — inline so it paints instantly, before the JS bundle --}}
        <style>
            #app-loader{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;
                background:radial-gradient(60% 55% at 50% 42%, rgba(236,48,19,.20), transparent 70%), #0b0c0f;
                transition:opacity .55s ease, visibility .55s ease;}
            #app-loader.hide{opacity:0;visibility:hidden;}
            .ldr-inner{display:flex;flex-direction:column;align-items:center;gap:18px}
            .ldr-mark{filter:drop-shadow(0 10px 34px rgba(236,48,19,.55));animation:ldrPulse 1.5s ease-in-out infinite}
            .ldr-mark svg{display:block;animation:ldrSpin 3.4s linear infinite}
            .ldr-word{font-family:Archivo,system-ui,sans-serif;font-weight:900;letter-spacing:-.03em;font-size:27px;color:#fff;opacity:0;animation:ldrFade .6s ease .15s forwards}
            .ldr-word b{color:#ec3013;font-weight:900}
            .ldr-bar{width:124px;height:3px;border-radius:3px;background:rgba(255,255,255,.12);overflow:hidden}
            .ldr-bar i{display:block;height:100%;width:42%;border-radius:3px;background:#ec3013;animation:ldrBar 1.15s ease-in-out infinite}
            @keyframes ldrPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.09)}}
            @keyframes ldrSpin{to{transform:rotate(360deg)}}
            @keyframes ldrFade{to{opacity:1}}
            @keyframes ldrBar{0%{transform:translateX(-130%)}100%{transform:translateX(330%)}}
            @media (prefers-reduced-motion: reduce){.ldr-mark,.ldr-mark svg,.ldr-bar i{animation:none}}
        </style>
    </head>
    <body>
        <div id="app-loader" aria-hidden="true">
            <div class="ldr-inner">
                <div class="ldr-mark">
                    <svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                        <rect width="100" height="100" rx="22" fill="#ec3013"/>
                        <path d="M50 12 C55 38 62 45 88 50 C62 55 55 62 50 88 C45 62 38 55 12 50 C38 45 45 38 50 12 Z" fill="#0b0c0f"/>
                    </svg>
                </div>
                <div class="ldr-word">AUTO<b>NOVA</b></div>
                <div class="ldr-bar"><i></i></div>
            </div>
        </div>
        @inertia
    </body>
</html>
