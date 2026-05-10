@extends('admin.layout')
@section('title', 'Edit Product')

@section('content')

<div class="admin-card" style="max-width:600px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/store/products" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Edit — {{ $product->productName }}</h2>
    </div>

    <div style="background:#f8f8f8; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:13px; color:#888;">
        Slug: <strong style="color:#262c39;">{{ $product->productSlug }}</strong>
        &nbsp;·&nbsp;
        <a href="/store/{{ $product->productSlug }}" target="_blank" style="color:#458bc8; text-decoration:none;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> View in store
        </a>
    </div>

    <form method="POST" action="/admin/store/products/{{ $product->productID }}/edit">
        @csrf

        <div class="form-group">
            <label class="form-label">Product name</label>
            <input type="text" name="productName" class="form-control" value="{{ old('productName', $product->productName) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="productDescription" class="form-control" rows="4" style="resize:vertical;">{{ old('productDescription', $product->productDescription) }}</textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Price ($)</label>
                <input type="number" name="productPrice" class="form-control" value="{{ old('productPrice', $product->productPrice) }}" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Stock</label>
                <input type="number" name="productStock" class="form-control" value="{{ old('productStock', $product->productStock) }}" min="0" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Slug</label>
            <input type="text" name="productSlug" id="productSlug" class="form-control" value="{{ old('productSlug', $product->productSlug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" autocomplete="off">
            <div style="margin-top:6px; font-size:12px; color:#aaa;">
                soccerdads.com.au/store/<span id="slugPreview" style="color:#458bc8; font-weight:600;">{{ old('productSlug', $product->productSlug) }}</span>
            </div>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="productActive" id="productActive" value="1" style="width:18px; height:18px; cursor:pointer;" {{ old('productActive', $product->productActive) ? 'checked' : '' }}>
            <label for="productActive" style="font-size:14px; color:#262c39; cursor:pointer; margin:0;">Active (visible in store)</label>
        </div>

        <div style="border-top:1px solid #e8e8e8; margin:1.5rem 0;"></div>
        <h3 style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:1rem;">Availability</h3>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Available from</label>
                <input type="datetime-local" name="productAvailableFrom" class="form-control"
                    value="{{ old('productAvailableFrom', $product->productAvailableFrom ? \Carbon\Carbon::parse($product->productAvailableFrom)->format('Y-m-d\TH:i') : '') }}">
                <div style="font-size:11px; color:#aaa; margin-top:4px;">Leave blank to show immediately</div>
            </div>
            <div class="form-group">
                <label class="form-label">Available to</label>
                <input type="datetime-local" name="productAvailableTo" class="form-control"
                    value="{{ old('productAvailableTo', $product->productAvailableTo ? \Carbon\Carbon::parse($product->productAvailableTo)->format('Y-m-d\TH:i') : '') }}">
                <div style="font-size:11px; color:#aaa; margin-top:4px;">Leave blank for no end date</div>
            </div>
        </div>

        <div class="form-group" style="max-width:200px;">
            <label class="form-label">Max quantity per order</label>
            <input type="number" name="productMaxQuantity" class="form-control"
                value="{{ old('productMaxQuantity', $product->productMaxQuantity ?? 1) }}" min="1" required>
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
                <i class="fa-solid fa-floppy-disk"></i> Save changes
            </button>
            <a href="/admin/store/products" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

{{-- IMAGE MANAGEMENT CARD (outside the main form, changes apply immediately via AJAX) --}}
<div class="admin-card" style="max-width:600px; margin-top:0;">
    <h2>Images</h2>

    <div id="image-grid" style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:1.25rem; min-height:8px;">
        @foreach($images as $image)
        <div class="img-card" data-id="{{ $image->imageID }}" style="width:130px; flex-shrink:0; border:1px solid #e8e8e8; border-radius:10px; overflow:hidden; background:#fff; position:relative;">
            <img src="{{ $image->imageUrl }}" alt="" style="width:130px; height:130px; object-fit:cover; display:block;">
            <div style="padding:6px 8px; display:flex; flex-direction:column; gap:4px;">
                @if($image->isPrimary)
                <span class="badge-primary" style="background:#f4f4f4; color:#888; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:2px 8px; border-radius:10px; text-align:center;">Primary</span>
                @else
                <button type="button" class="btn-set-primary" onclick="setPrimary({{ $image->imageID }}, this)" style="background:none; border:1px solid #e8e8e8; border-radius:6px; font-size:11px; color:#555; padding:2px 6px; cursor:pointer; width:100%; text-align:center;">Set primary</button>
                @endif
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; gap:2px;">
                        <button type="button" onclick="moveImage(this.closest('.img-card'), -1)" style="background:none; border:1px solid #e8e8e8; border-radius:4px; width:22px; height:22px; cursor:pointer; font-size:11px; color:#555; display:flex; align-items:center; justify-content:center;" title="Move left">‹</button>
                        <button type="button" onclick="moveImage(this.closest('.img-card'), 1)" style="background:none; border:1px solid #e8e8e8; border-radius:4px; width:22px; height:22px; cursor:pointer; font-size:11px; color:#555; display:flex; align-items:center; justify-content:center;" title="Move right">›</button>
                    </div>
                    <button type="button" onclick="deleteImage({{ $image->imageID }}, this.closest('.img-card'))" style="background:none; border:none; color:#ccc; cursor:pointer; font-size:15px; padding:0 2px;" title="Delete">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Drop zone --}}
    <div id="drop-zone" style="border:2px dashed #e8e8e8; border-radius:10px; padding:2rem; text-align:center; cursor:pointer; transition:border-color 0.2s, background 0.2s;">
        <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px; color:#ccc; display:block; margin-bottom:8px;"></i>
        <p style="font-size:14px; color:#888; margin-bottom:4px;">Drop images here or <span style="color:#458bc8; font-weight:600;">click to upload</span></p>
        <p style="font-size:11px; color:#bbb;">JPG, PNG, WebP — max 5 MB each</p>
        <input type="file" id="file-input" accept="image/jpeg,image/png,image/webp" multiple style="display:none;">
    </div>

    <div id="upload-errors" style="margin-top:8px;"></div>
</div>

@push('scripts')
<script>
    const PRODUCT_ID  = {{ $product->productID }};
    const CSRF        = document.querySelector('meta[name="csrf-token"]').content;
    const uploadUrl   = `/admin/store/products/${PRODUCT_ID}/images/upload`;
    const reorderUrl  = `/admin/store/products/${PRODUCT_ID}/images/reorder`;
    const grid        = document.getElementById('image-grid');
    const dropZone    = document.getElementById('drop-zone');
    const fileInput   = document.getElementById('file-input');
    const errorsEl    = document.getElementById('upload-errors');

    // ── Drop zone ────────────────────────────────────────────────
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = '#458bc8'; dropZone.style.background = '#f0f8ff'; });
    dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#e8e8e8'; dropZone.style.background = ''; });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#e8e8e8';
        dropZone.style.background = '';
        uploadFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', () => { uploadFiles(fileInput.files); fileInput.value = ''; });

    // ── Upload ───────────────────────────────────────────────────
    async function uploadFiles(files) {
        errorsEl.innerHTML = '';
        for (const file of files) {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                showError(`"${file.name}" is not a supported format (JPG, PNG, WebP only).`);
                continue;
            }
            if (file.size > 5 * 1024 * 1024) {
                showError(`"${file.name}" exceeds the 5 MB limit.`);
                continue;
            }
            const placeholder = appendPlaceholder();
            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', CSRF);
            try {
                const res  = await fetch(uploadUrl, { method: 'POST', body: formData });
                const data = await res.json();
                placeholder.remove();
                appendImageCard(data.imageID, data.imageUrl, data.isPrimary);
            } catch (e) {
                placeholder.remove();
                showError(`Failed to upload "${file.name}".`);
            }
        }
    }

    function appendPlaceholder() {
        const div = document.createElement('div');
        div.style.cssText = 'width:130px; height:190px; border:1px solid #e8e8e8; border-radius:10px; background:#f8f8f8; display:flex; align-items:center; justify-content:center; flex-shrink:0;';
        div.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="color:#ccc; font-size:20px;"></i>';
        grid.appendChild(div);
        return div;
    }

    function appendImageCard(imageID, imageUrl, isPrimary) {
        const div = document.createElement('div');
        div.className = 'img-card';
        div.dataset.id = imageID;
        div.style.cssText = 'width:130px; flex-shrink:0; border:1px solid #e8e8e8; border-radius:10px; overflow:hidden; background:#fff; position:relative;';
        div.innerHTML = `
            <img src="${imageUrl}" alt="" style="width:130px; height:130px; object-fit:cover; display:block;">
            <div style="padding:6px 8px; display:flex; flex-direction:column; gap:4px;">
                ${isPrimary
                    ? `<span class="badge-primary" style="background:#f4f4f4; color:#888; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:2px 8px; border-radius:10px; text-align:center;">Primary</span>`
                    : `<button type="button" class="btn-set-primary" onclick="setPrimary(${imageID}, this)" style="background:none; border:1px solid #e8e8e8; border-radius:6px; font-size:11px; color:#555; padding:2px 6px; cursor:pointer; width:100%; text-align:center;">Set primary</button>`
                }
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; gap:2px;">
                        <button type="button" onclick="moveImage(this.closest('.img-card'), -1)" style="background:none; border:1px solid #e8e8e8; border-radius:4px; width:22px; height:22px; cursor:pointer; font-size:11px; color:#555; display:flex; align-items:center; justify-content:center;" title="Move left">‹</button>
                        <button type="button" onclick="moveImage(this.closest('.img-card'), 1)"  style="background:none; border:1px solid #e8e8e8; border-radius:4px; width:22px; height:22px; cursor:pointer; font-size:11px; color:#555; display:flex; align-items:center; justify-content:center;" title="Move right">›</button>
                    </div>
                    <button type="button" onclick="deleteImage(${imageID}, this.closest('.img-card'))" style="background:none; border:none; color:#ccc; cursor:pointer; font-size:15px; padding:0 2px;" title="Delete">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>`;
        grid.appendChild(div);
    }

    // ── Delete ───────────────────────────────────────────────────
    async function deleteImage(imageID, card) {
        if (!confirm('Delete this image?')) return;
        const res  = await fetch(`/admin/store/products/${PRODUCT_ID}/images/${imageID}/delete`, {
            method: 'POST',
            body: new URLSearchParams({ _token: CSRF }),
        });
        const data = await res.json();
        if (data.success) {
            card.remove();
            if (data.newPrimaryID) {
                document.querySelectorAll('.img-card').forEach(c => {
                    const id = parseInt(c.dataset.id);
                    const badge = c.querySelector('.badge-primary');
                    const btn   = c.querySelector('.btn-set-primary');
                    if (id === data.newPrimaryID) {
                        if (btn)   btn.outerHTML = `<span class="badge-primary" style="background:#f4f4f4; color:#888; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:2px 8px; border-radius:10px; text-align:center;">Primary</span>`;
                    }
                });
            }
        }
    }

    // ── Set primary ──────────────────────────────────────────────
    async function setPrimary(imageID, btn) {
        const res  = await fetch(`/admin/store/products/${PRODUCT_ID}/images/${imageID}/primary`, {
            method: 'POST',
            body: new URLSearchParams({ _token: CSRF }),
        });
        const data = await res.json();
        if (data.success) {
            document.querySelectorAll('.img-card').forEach(c => {
                const badge = c.querySelector('.badge-primary');
                const spBtn = c.querySelector('.btn-set-primary');
                const id    = parseInt(c.dataset.id);
                if (id === imageID) {
                    if (spBtn) spBtn.outerHTML = `<span class="badge-primary" style="background:#f4f4f4; color:#888; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:2px 8px; border-radius:10px; text-align:center;">Primary</span>`;
                } else {
                    if (badge) badge.outerHTML = `<button type="button" class="btn-set-primary" onclick="setPrimary(${id}, this)" style="background:none; border:1px solid #e8e8e8; border-radius:6px; font-size:11px; color:#555; padding:2px 6px; cursor:pointer; width:100%; text-align:center;">Set primary</button>`;
                }
            });
        }
    }

    // ── Reorder ──────────────────────────────────────────────────
    function moveImage(card, dir) {
        const cards = [...grid.querySelectorAll('.img-card')];
        const idx   = cards.indexOf(card);
        const target = dir === -1 ? cards[idx - 1] : cards[idx + 1];
        if (!target) return;
        if (dir === -1) grid.insertBefore(card, target);
        else grid.insertBefore(target, card);
        saveOrder();
    }

    async function saveOrder() {
        const order = [...grid.querySelectorAll('.img-card')].map(c => c.dataset.id);
        const body  = new URLSearchParams({ _token: CSRF });
        order.forEach((id, i) => body.append(`order[${i}]`, id));
        await fetch(reorderUrl, { method: 'POST', body });
    }

    // ── Helpers ──────────────────────────────────────────────────
    function showError(msg) {
        const div = document.createElement('div');
        div.style.cssText = 'background:#fff3f3; border:1px solid #e24b4a; color:#262c39; padding:8px 12px; border-radius:8px; font-size:13px; margin-top:4px;';
        div.textContent = msg;
        errorsEl.appendChild(div);
    }

    // ── Slug preview ─────────────────────────────────────────────
    const slugInput = document.getElementById('productSlug');
    const slugPreview = document.getElementById('slugPreview');

    slugInput.addEventListener('input', function () {
        // Sanitise: lowercase, replace anything not a-z 0-9 with hyphen, collapse/trim hyphens
        const sanitised = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        this.value = sanitised;
        slugPreview.textContent = sanitised || '…';
    });
</script>
@endpush

@endsection
