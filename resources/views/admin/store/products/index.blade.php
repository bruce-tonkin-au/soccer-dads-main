@extends('admin.layout')
@section('title', 'Store — Products')

@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Products ({{ $products->count() }})</h2>
        <div style="display:flex; gap:8px;">
            <a href="/store" target="_blank" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View store
            </a>
            <a href="/admin/store/products/create" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add product
            </a>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:12px;">
                        @if($product->displayImage)
                        <img src="{{ $product->displayImage }}" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #e8e8e8;">
                        @else
                        <div style="width:40px; height:40px; background:#f4f4f4; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#ccc;">
                            <i class="fa-solid fa-image"></i>
                        </div>
                        @endif
                        <div>
                            <div style="font-weight:600; color:#262c39;">{{ $product->productName }}</div>
                            <div style="font-size:12px; color:#aaa;">/store/{{ $product->productSlug }}</div>
                        </div>
                    </div>
                </td>
                <td style="font-weight:600;">${{ number_format($product->productPrice, 2) }}</td>
                <td>
                    @if($product->productStock <= 0)
                    <span style="color:#e24b4a; font-weight:600;">Out of stock</span>
                    @else
                    {{ $product->productStock }}
                    @endif
                </td>
                <td>
                    @if($product->productActive)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Active</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Inactive</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex; gap:6px; justify-content:flex-end;">
                        <a href="/admin/store/products/{{ $product->productID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <form method="POST" action="/admin/store/products/{{ $product->productID }}/toggle">
                            @csrf
                            <button type="submit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                                @if($product->productActive)
                                <i class="fa-solid fa-eye-slash"></i> Deactivate
                                @else
                                <i class="fa-solid fa-eye"></i> Activate
                                @endif
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
