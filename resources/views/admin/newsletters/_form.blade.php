@push('styles')
<link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
<style>
    trix-editor {
        min-height: 260px;
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        font-size: 15px;
        color: #262c39;
        line-height: 1.7;
        padding: 12px 14px;
    }
    trix-toolbar .trix-button-group { border-color: #e8e8e8; }
    .nl-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        padding: 2rem 1rem;
        overflow: auto;
    }
    .nl-modal-inner {
        max-width: 680px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
    }
    .nl-modal-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e8e8e8;
    }
    .nl-modal-bar strong { color: #262c39; }
    #nl-preview-frame {
        width: 100%;
        height: 70vh;
        border: none;
        background: #f4f4f4;
        display: block;
    }
</style>
@endpush

<div class="admin-card" style="max-width:760px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/newsletters" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">{{ $newsletter->newsletterID ? 'Edit' : 'New' }} newsletter</h2>
    </div>

    @if($errors->any())
    <div class="alert alert-error">
        <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ $action }}" id="newsletter-form">
        @csrf
        @if($method === 'PUT')
        @method('PUT')
        @endif

        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" id="nl-subject" class="form-control"
                   value="{{ old('subject', $newsletter->subject) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Body</label>
            <input id="nl-body" type="hidden" name="body" value="{{ old('body', $newsletter->body) }}">
            <trix-editor input="nl-body"></trix-editor>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Save Draft
            </button>
            <button type="button" class="btn btn-secondary" onclick="openPreview()">
                <i class="fa-solid fa-eye"></i> Preview &amp; Copy HTML
            </button>
        </div>
    </form>
</div>

{{-- Preview modal — renders the full SD email exactly as Sendy recipients see it --}}
<div class="nl-modal" id="nl-modal">
    <div class="nl-modal-inner">
        <div class="nl-modal-bar">
            <strong>Email preview</strong>
            <div style="display:flex; gap:8px;">
                <button type="button" class="btn btn-primary" id="copy-btn" onclick="copyHtml()">
                    <i class="fa-solid fa-copy"></i> Copy HTML
                </button>
                <button type="button" class="btn btn-secondary" onclick="closePreview()">Close</button>
            </div>
        </div>
        <iframe id="nl-preview-frame" title="Newsletter preview"></iframe>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/trix@2.1.1/dist/trix.umd.min.js"></script>
<script>
    // The SD email wrapper with __SUBJECT__ / __BODY__ placeholders (server-rendered).
    const EMAIL_TEMPLATE = @json($emailTemplate);

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function buildEmailHtml() {
        const body = document.getElementById('nl-body').value || '';
        const subject = document.getElementById('nl-subject').value || 'Soccer Dads Newsletter';
        return EMAIL_TEMPLATE
            .split('__BODY__').join(body)
            .split('__SUBJECT__').join(escapeHtml(subject));
    }

    function openPreview() {
        document.getElementById('nl-preview-frame').srcdoc = buildEmailHtml();
        document.getElementById('nl-modal').style.display = 'block';
    }

    function closePreview() {
        document.getElementById('nl-modal').style.display = 'none';
    }

    async function copyHtml() {
        const html = buildEmailHtml();
        const btn = document.getElementById('copy-btn');
        const done = () => {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            setTimeout(() => { btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy HTML'; }, 1500);
        };
        try {
            await navigator.clipboard.writeText(html);
            done();
        } catch (e) {
            const ta = document.createElement('textarea');
            ta.value = html;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            done();
        }
    }

    // Close the modal on background click or Escape.
    document.getElementById('nl-modal').addEventListener('click', function (e) {
        if (e.target === this) closePreview();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePreview();
    });
</script>
@endpush
