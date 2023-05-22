@extends('layouts.app')

@section('content')
    <article>
      <div>
        <h1>{{__('New user')}}</h1>
        <dl>
          <div>
              <dt>{{__('Name')}}</dt>
              <dd>{{ $user->name }}</dd>
          </div>
          <div>
              <dt>{{__('Email')}}</dt>
              <dd>{{ $user->email }}</dd>
          </div>
          <div>
              <dt>{{__('Initial password')}}</dt>
              <dd>{{ $password }}</dd>
          </div>
          <div>
              <dt>{{__('QR Code')}}</dt>
              <dd>
                {!! $user->twoFactorQrCodeSvg() !!}
              </dd>
          </div>
        </dl>

@if(Route::has("pdf.${route}.download"))
            <div class="actions">
                <button id="executePrint" class="no-print">&#128438; {{__('Print Document')}}</button>
                <script>
                    const printButton = document.querySelector("#executePrint");
                    printButton.addEventListener("click", function () {
                        window.print();
                    });
                </script>

                <div class="no-print">
                    <a class="button" href="{{ route("pdf.${route}.download", ['uuid' => $user->uuid])}}">
                        <span class="ro-icon ro-icon-download" style="margin: 0"></span> {{__('Download PDF')}}
                    </a>
                </div>
            </div>
            @endif
        </div>
    </article>

@endsection
