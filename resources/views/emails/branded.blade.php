{{-- Branded transactional email: dark AutoNova header, white card, accent button.
     Table layout + inline styles only, so it renders the same in Gmail, Outlook and Apple Mail. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f2f1ef;font-family:Arial,Helvetica,sans-serif;color:#16181d;-webkit-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader ?? $title }}</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f1ef;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
                    {{-- Header --}}
                    <tr>
                        <td style="background:#14161b;border-radius:18px 18px 0 0;padding:26px 36px;border-bottom:3px solid #ec3013;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:#ec3013;border-radius:6px;width:28px;height:28px;text-align:center;vertical-align:middle;color:#ffffff;font-size:16px;line-height:28px;">&#10022;</td>
                                    <td style="padding-left:10px;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:800;letter-spacing:.5px;color:#ffffff;">AUTO<span style="color:#ec3013;">NOVA</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff;padding:38px 36px 34px;border-radius:0 0 18px 18px;box-shadow:0 12px 30px rgba(20,22,27,.08);">
                            @isset($kicker)
                                <div style="font-family:'Courier New',monospace;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#ec3013;margin-bottom:10px;">{{ $kicker }}</div>
                            @endisset
                            <h1 style="margin:0 0 18px;font-size:26px;line-height:1.2;font-weight:800;color:#16181d;">{{ $title }}</h1>
                            @isset($greeting)
                                <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#16181d;font-weight:700;">{{ $greeting }}</p>
                            @endisset
                            @foreach ($lines as $line)
                                <p style="margin:0 0 14px;font-size:15px;line-height:1.65;color:#4a4f5a;">{{ $line }}</p>
                            @endforeach

                            @isset($details)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 14px;background:#f7f6f4;border-radius:12px;">
                                    @foreach ($details as $label => $value)
                                        <tr>
                                            <td style="padding:10px 16px;font-family:'Courier New',monospace;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#8a8f99;width:38%;">{{ $label }}</td>
                                            <td style="padding:10px 16px;font-size:14px;color:#16181d;font-weight:700;">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endisset

                            @isset($actionUrl)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0 24px;">
                                    <tr>
                                        <td style="background:#ec3013;border-radius:999px;box-shadow:0 8px 18px rgba(236,48,19,.28);">
                                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:15px 34px;font-size:15px;font-weight:800;color:#ffffff;text-decoration:none;">{{ $actionText }}</a>
                                        </td>
                                    </tr>
                                </table>
                            @endisset

                            @foreach ($outro ?? [] as $line)
                                <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#8a8f99;">{{ $line }}</p>
                            @endforeach

                            @isset($actionUrl)
                                <div style="border-top:1px solid #ecebe8;margin-top:22px;padding-top:16px;font-size:12px;line-height:1.6;color:#8a8f99;">
                                    {{ __('If the button does not work, copy this link into your browser:') }}<br>
                                    <a href="{{ $actionUrl }}" style="color:#ec3013;word-break:break-all;">{{ $actionUrl }}</a>
                                </div>
                            @endisset
                        </td>
                    </tr>
                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:22px 20px 0;font-size:12px;line-height:1.6;color:#8a8f99;">
                            {{ __('Macedonia’s marketplace for vehicles.') }}<br>
                            &copy; {{ date('Y') }} AutoNova &middot; <a href="{{ url('/') }}" style="color:#8a8f99;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
