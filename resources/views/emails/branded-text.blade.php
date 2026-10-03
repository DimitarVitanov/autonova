AUTONOVA

{{ $title }}

@isset($greeting){{ $greeting }}

@endisset
@foreach ($lines as $line)
{{ $line }}

@endforeach
@isset($details)
@foreach ($details as $label => $value)
{{ $label }}: {{ $value }}
@endforeach

@endisset
@isset($actionUrl)
{{ $actionText }}: {{ $actionUrl }}

@endisset
@foreach ($outro ?? [] as $line)
{{ $line }}

@endforeach
© {{ date('Y') }} AutoNova · {{ url('/') }}
