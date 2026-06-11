<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        .content p { margin-bottom: 16px; }
        .content p:last-child { margin-bottom: 0; }
    </style>
</head>
<body style="margin:0; padding:0; background:#f4f4f4; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">

    <!-- Header -->
    <div style="background:#262c39; padding:20px; text-align:center;">
        <img src="{{ url('/images/Soccer-Dads-Logo.png') }}" alt="Soccer Dads" style="max-height:50px; width:auto; display:inline-block; border:0;">
    </div>

    <!-- Title bar -->
    <div style="background:linear-gradient(to right, #3b82c4, #4aaa6e, #c8a83c); padding:20px 40px; text-align:center;">
        <span style="color:#ffffff; font-size:28px; font-weight:bold;">{{ $subject }}</span>
    </div>

    <!-- Content -->
    <div class="content" style="max-width:600px; margin:0 auto; background:#ffffff; padding:32px 40px; color:#262c39; font-size:15px; line-height:1.7;">
        {!! $body !!}
    </div>

    <!-- Footer -->
    <div style="background:#f4f4f4; padding:16px; text-align:center; font-size:12px; color:#999999;">
        &copy; 2026 Soccer Dads
    </div>

</body>
</html>
