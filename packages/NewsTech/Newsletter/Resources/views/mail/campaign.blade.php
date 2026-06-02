<!DOCTYPE html>
<html lang="en">
    <body style="margin:0;background:#f5f5f4;color:#292524;font-family:Arial,sans-serif;">
        <div style="max-width:680px;margin:0 auto;padding:32px 20px;">
            <div style="background:#ffffff;border:1px solid #e7e5e4;border-radius:24px;padding:32px;">
                <p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:0.28em;text-transform:uppercase;color:#d97706;">{{ $siteName }}</p>

                @if ($campaign->preheader)
                    <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#78716c;">{{ $campaign->preheader }}</p>
                @endif

                <div style="font-size:15px;line-height:1.8;color:#44403c;">
                    {!! $campaign->content !!}
                </div>

                <hr style="border:none;border-top:1px solid #e7e5e4;margin:28px 0;">

                <p style="margin:0 0 12px;font-size:13px;line-height:1.7;color:#78716c;">
                    {{ $footerUnsubscribeText }}
                </p>

                <p style="margin:0;">
                    <a href="{{ $unsubscribeUrl }}" style="color:#d97706;font-weight:700;text-decoration:none;">Unsubscribe</a>
                </p>
            </div>
        </div>
    </body>
</html>
