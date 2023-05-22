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
    <link rel="stylesheet" href="{{ url('huisstijl/css/pagination.css') }}">
    <link rel="shortcut icon" href="/huisstijl/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/huisstijl/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/huisstijl/img/favicon-16x16.png">
    <script defer src="/js/form-help.min.js"></script>
    <script defer src="/js/navigation.min.js"></script>
    <script src="{{ url('js/app.js') }}"></script>
    <script defer src="/js/filters.min.js"></script>

    <style>.horizontal-view.form-fix { flex-direction:row; margin-top: 0}</style>
</head>
<body>
<header class="no-print">
    <a href="#main-content" class="button focus-only">{{__('Skip to content')}}</a>

    <section class="page-meta">
        <x-language />
    </section>

    <x-header />

    <section>
        <div>
            <nav data-open-label="Menu" data-close-label="{{__('Close menu')}}" data-media="(min-width: 63rem)" aria-label="{{__('Main navigation')}}">
                <ul>
                    <li><a href="/">@lang('Reporting Portal')</a></li>
                    @role([\App\Role::USER_ADMIN, \App\Role::SUPER_ADMIN])
                    <x-nav-item :route="'users.meldportaal.overview'">{{__('Users')}} </x-nav-item>
                    @endrole
                    @role([\App\Role::SUPER_ADMIN])
                    <x-nav-item :route="'users.api.overview'">{{__('API Users')}} </x-nav-item>
                    @endrole

                </ul>
            </nav>
            @auth
            <div>
                <a href="{{ route('logout') }}" class="button">{{__('Logout')}}</a>
            </div>
            @endif
        </div>
    </section>

</header>
<main id="main-content" tabindex="-1">

    @if(Session::has('message'))
        <p class="{{ Session::get('class', 'confirmation') }} no-print">
            {{ Session::get('message') }}
        </p>
    @endif


    @yield('content')
</main>
<footer class="no-print">
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
