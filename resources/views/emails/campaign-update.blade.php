<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Property update') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f4f4f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,0.08); overflow:hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3a5f 0%, #0d2137 100%); padding: 28px 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:700;">{{ $websiteTitle ?? config('app.name') }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px 32px 24px;">
                            <p style="margin:0 0 20px; font-size:16px; line-height:1.6; color:#2d3748;">
                                <strong style="color:#1e3a5f;">{{ __('Dear') }} {{ $customerName }},</strong>
                            </p>
                            <div style="font-size:15px; line-height:1.7; color:#2d3748;">
                                {!! nl2br(e($body ?? '')) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 32px; border-top:1px solid #e2e8f0;">
                            <p style="margin:0 0 4px; font-size:15px; font-weight:700; color:#1e3a5f;">{{ $senderName }}</p>
                            <p style="margin:0; font-size:13px; color:#718096;">{{ $senderRole ?? __('Real Estate') }}</p>
                        </td>
                    </tr>
                    @if(!empty($unsubscribeUrl))
                    <tr>
                        <td style="background-color:#f8fafc; padding: 16px 32px; text-align:center; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; font-size:12px; color:#718096;">
                                <a href="{{ $unsubscribeUrl }}" style="color:#4a5568;">{{ __('Unsubscribe from these updates') }}</a>
                            </p>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
