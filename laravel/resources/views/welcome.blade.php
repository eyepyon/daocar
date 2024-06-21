<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>OnAR</title>
        <meta name="description" content="OnAR">
        <meta name="keyword" content="OnAR,健康">

        <!--googlfont-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho&family=Tangerine:wght@400;700&display=swap" rel="stylesheet">

        <link rel="alternate" hreflang="ja" lang="ja" href="https://daocar.llc/"><!--日本語-->
        <link rel="alternate" hreflang="en" lang="en" href="https://daocar.llc/en/"><!--英語-->
        <link rel="alternate" hreflang="th" lang="th" href="https://daocar.llc/th/"><!--タイ-->
        <link rel="alternate" hreflang="id" lang="id" href="https://daocar.llc/id/"><!--インドネシア-->
        <link rel="alternate" hreflang="zh-cmn-Hant" lang="zh-cmn-Hant" href="https://daocar.llc/zh-tw/"><!--中国語（繁体）-->

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    </head>

    <body id="home">

    <div class="line_bnr">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/home') }}">Wallet</a>
            @else
                <a href="{{ route('login.line.redirect') }}" target="_blank"><span>でログイン</span></a>
            @endauth
        @endif
    </div>


    <header class="is-pc pc_header">
        <div class="upper_header">
            <ul>
                <li>
                    <h1 class="logo"><a href="/"><img src="img/common/logo.png" alt="OnAR"></a></h1>
                </li>

                <li>

                </li>
            </ul>
        </div>

    </header>

    <p class="copy_right">Copyright © FrogCompany inc. All Rights Reserved.</p>

    </body>
</html>
