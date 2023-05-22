<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', '') }}</title>
    <link rel="preload" media="screen and (min-width: 768px)" href="/huisstijl/img/ggdghor-logo.svg" as="image">
    <link rel="preload" href="/huisstijl/fonts/RO-SansWebText-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/huisstijl/fonts/RO-SansWebText-Bold.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ url('/huisstijl/css/manon-ruby-red.css') }}">
    <link rel="stylesheet" href="{{ url('/css/app.css') }}">
    <link rel="shortcut icon" href="/huisstijl/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/huisstijl/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/huisstijl/img/favicon-16x16.png">
    <script defer src="/js/form-help.min.js"></script>
    <script src="{{ url('js/app.js') }}"></script>

    <style>.horizontal-view.form-fix { flex-direction:row; margin-top: 0}</style>
</head>
<body>
<header>
    <a href="#main-content" class="button focus-only">{{__('Skip to content')}}</a>

    <section class="page-meta">
        <x-language />
    </section>

    <a href="/" class="ggdghor-logo" aria-label="{{__('GGD-GHOR logo, go to the homepage')}}">
        <img src="/huisstijl/img/ggdghor-logo.svg" alt="Logo GGD-GHOR">
        GGD-GHOR
    </a>

    <nav data-open-label="Menu" data-close-label="{{__('Close menu') }}" data-media="(min-width: 60rem)" aria-label="{{__('Main navigation')}}">
        <div>
            <ul>
                <li><a href="/" aria-current="page">@lang('HOME')</a></li>
            </ul>
        </div>
    </nav>
</header>
<main id="main-content" tabindex="-1">
    @yield('content')
</main>
<footer>
    <div>
        <span>De Rijksoverheid. Voor Nederland</span>

        <div class="meta">
            <p>
                {{ __('Version')}}
                {{ App\Http\Kernel::applicationVersion() }}
            </p>
        </div>
    </div>
</footer>
</body>
</html>
