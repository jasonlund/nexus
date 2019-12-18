@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.front_end_url')])
            <img src="https://nexus-pylon-assets.s3.amazonaws.com/logo.png" width="320" height="50" />
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} <a href="https://thepylonshow.com">The Pylon Show</a>. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
