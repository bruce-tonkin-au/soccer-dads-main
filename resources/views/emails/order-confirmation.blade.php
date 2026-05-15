<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8f8f8; padding: 2rem; margin: 0; }
        .card { background: #fff; border-radius: 12px; padding: 2rem; max-width: 520px; margin: 0 auto; }
        .logo { font-size: 22px; font-weight: 700; color: #262c39; margin-bottom: 1.5rem; }
        .heading { font-size: 20px; font-weight: 700; color: #262c39; margin: 0 0 0.5rem; }
        .sub { color: #555; font-size: 15px; line-height: 1.6; margin: 0 0 1.5rem; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 1.5rem; }
        thead th { background: #f4f4f4; padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #888; border-bottom: 1px solid #e8e8e8; }
        thead th.right { text-align: right; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; color: #262c39; }
        tbody td.right { text-align: right; }
        .total-row td { border-top: 2px solid #e8e8e8; border-bottom: none; padding-top: 12px; font-weight: 700; font-size: 15px; }
        .order-meta { background: #f8f8f8; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: 14px; color: #555; }
        .order-meta strong { color: #262c39; }
        .note { font-size: 13px; color: #888; line-height: 1.6; margin-top: 1.5rem; border-top: 1px solid #f0f0f0; padding-top: 1.25rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">⚽ Soccer Dads</div>

        <h2 class="heading">
            @if($toName)
            Thanks, {{ $toName }}!
            @else
            Order confirmed!
            @endif
        </h2>
        <p class="sub">Your order has been received and payment confirmed. We'll be in touch soon to arrange delivery.</p>

        <div class="order-meta">
            <strong>Order #{{ $order->orderID }}</strong> &nbsp;·&nbsp;
            {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="right">Qty</th>
                    <th class="right">Price</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->productName }}</td>
                    <td class="right">{{ $item->itemQuantity }}</td>
                    <td class="right">${{ number_format($item->itemPrice, 2) }}</td>
                    <td class="right">${{ number_format($item->itemPrice * $item->itemQuantity, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align:right; color:#555; font-size:13px; font-weight:600;">Total paid</td>
                    <td class="right">${{ number_format($order->orderTotal, 2) }} AUD</td>
                </tr>
            </tbody>
        </table>

        <p class="note">
            Questions about your order? Reply to this email or contact us at
            <a href="mailto:admin@soccerdads.com.au" style="color:#458bc8;">admin@soccerdads.com.au</a>
        </p>
        <p class="note" style="border-top:none; padding-top:0; margin-top:0.5rem;">
            — The Soccer Dads team
        </p>
    </div>
</body>
</html>
