<div class="language">
    @auth
        <span>
            {{ __("Logged in as") }} :
        </span>
        <a href="{{ route('profile.show') }}">{{ Auth::user()->name }}</a>
        <span>|</span>
    @endauth

    <a href="{{route('changelang', ['locale' => 'nl'])}}">Nederlands</a>
    <a href="{{route('changelang', ['locale' => 'en'])}}">English</a>
</div>