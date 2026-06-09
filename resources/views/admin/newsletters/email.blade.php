<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f4f4; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
    <div style="max-width:600px; margin:0 auto; padding:24px 12px;">
        <div style="background:#ffffff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden;">

            <!-- Header -->
            <div style="background:#262c39; padding:24px; text-align:center;">
                <span style="color:#ffffff; font-size:22px; font-weight:700; letter-spacing:0.01em;">⚽ Soccer Dads</span>
            </div>

            <!-- Body -->
            <div style="padding:28px; color:#262c39; font-size:15px; line-height:1.7;">
                {!! $body !!}
            </div>

            <!-- Footer -->
            <div style="padding:18px 28px 26px; border-top:1px solid #f0f0f0; text-align:center;">
                <p style="color:#999999; font-size:12px; margin:0;">Soccer Dads &middot; soccerdads.com.au</p>
            </div>

        </div>
    </div>
</body>
</html>
