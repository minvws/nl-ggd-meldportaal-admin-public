@props(['label'])

<p
    class="explanation"
    data-open-label="{{ __('Explanation for') }}: {{ $label }}"
    data-close-label="{{ __('Close explanation for') }}: {{ $label }}"
    role="group"
    aria-label="{{__('explanation') }}"
><span>{{__('explanation') }}:</span> {{ $slot }}</p>
