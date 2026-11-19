{{-- Plain-text part: raw output on purpose — there is no HTML here to escape for. --}}
AUTONOVA

{!! $title !!}

@isset($greeting){!! $greeting !!}

@endisset
@foreach ($lines as $line)
{!! $line !!}

@endforeach
@isset($quote)
"{!! $quote !!}"

@endisset
@isset($vehicle)
{!! $vehicle['title'] !!} — {!! $vehicle['price'] !!}
{!! $vehicle['url'] !!}

@endisset
@isset($details)
@foreach ($details as $label => $value)
{!! $label !!}: {!! $value !!}
@endforeach

@endisset
@isset($actionUrl)
{!! $actionText !!}: {!! $actionUrl !!}

@endisset
@foreach ($outro ?? [] as $line)
{!! $line !!}

@endforeach
@if (config('mail.from.address'))
{!! __('Questions? Write to us at') !!} {!! config('mail.from.address') !!}
@endif
© {{ date('Y') }} AutoNova · {!! url('/') !!}
