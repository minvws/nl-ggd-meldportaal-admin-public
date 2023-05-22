@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'error']) }}>
        <span>@lang("Error"):</span>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
