@extends('admin.layout')
@section('title', 'Add Product')

@section('content')

<div class="admin-card" style="max-width:600px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/store/products" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Add product</h2>
    </div>

    <form method="POST" action="/admin/store/products/create">
        @csrf

        <div class="form-group">
            <label class="form-label">Product name</label>
            <input type="text" name="productName" class="form-control" value="{{ old('productName') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="productDescription" class="form-control" rows="4" style="resize:vertical;">{{ old('productDescription') }}</textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Price ($)</label>
                <input type="number" name="productPrice" class="form-control" value="{{ old('productPrice', '0.00') }}" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Stock</label>
                <input type="number" name="productStock" class="form-control" value="{{ old('productStock', '0') }}" min="0" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Image URL</label>
            <input type="url" name="productImage" class="form-control" value="{{ old('productImage') }}" placeholder="https://…">
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="productActive" id="productActive" value="1" style="width:18px; height:18px; cursor:pointer;" {{ old('productActive', '1') ? 'checked' : '' }}>
            <label for="productActive" style="font-size:14px; color:#262c39; cursor:pointer; margin:0;">Active (visible in store)</label>
        </div>

        <div style="border-top:1px solid #e8e8e8; margin:1.5rem 0;"></div>
        <h3 style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:1rem;">Availability</h3>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Available from</label>
                <input type="datetime-local" name="productAvailableFrom" class="form-control" value="{{ old('productAvailableFrom') }}">
                <div style="font-size:11px; color:#aaa; margin-top:4px;">Leave blank to show immediately</div>
            </div>
            <div class="form-group">
                <label class="form-label">Available to</label>
                <input type="datetime-local" name="productAvailableTo" class="form-control" value="{{ old('productAvailableTo') }}">
                <div style="font-size:11px; color:#aaa; margin-top:4px;">Leave blank for no end date</div>
            </div>
        </div>

        <div class="form-group" style="max-width:200px;">
            <label class="form-label">Max quantity per order</label>
            <input type="number" name="productMaxQuantity" class="form-control" value="{{ old('productMaxQuantity', '1') }}" min="1" required>
        </div>

        @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <div style="display:flex; gap:8px; margin-top:1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Save product
            </button>
            <a href="/admin/store/products" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
