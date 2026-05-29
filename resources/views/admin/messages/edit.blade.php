@extends('admin.layout')
@section('title', 'Edit Message')

@push('styles')
<style>
    .editor-wrap { border: 1px solid #e8e8e8; border-radius: 8px; overflow: hidden; }
    .editor-toolbar {
        display: flex;
        gap: 4px;
        padding: 8px;
        background: #f8f8f8;
        border-bottom: 1px solid #e8e8e8;
        flex-wrap: wrap;
    }
    .editor-toolbar button {
        padding: 4px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
        font-size: 13px;
        color: #262c39;
        line-height: 1.4;
    }
    .editor-toolbar button:hover { background: #262c39; color: #fff; border-color: #262c39; }
    .editor-content {
        min-height: 220px;
        padding: 12px;
        font-size: 15px;
        color: #262c39;
        outline: none;
        line-height: 1.8;
    }
    .editor-content p { margin-bottom: 0.75rem; }
    .editor-content ul, .editor-content ol { padding-left: 1.5rem; margin-bottom: 0.75rem; }
    .merge-helper {
        margin-top: 8px;
        padding: 10px 12px;
        background: #f8f8f8;
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        font-size: 12px;
    }
    .merge-helper-label { color: #888; margin-bottom: 6px; }
    .merge-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .merge-chip {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        padding: 4px 8px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 999px;
        cursor: pointer;
        color: #262c39;
        font-size: 12px;
    }
    .merge-chip:hover { background: #262c39; color: #fff; border-color: #262c39; }
    .preview-pane {
        min-height: 220px;
        padding: 12px;
        font-size: 15px;
        color: #262c39;
        line-height: 1.8;
        background: #fff;
    }
    .preview-pane p { margin-bottom: 0.75rem; }
    .preview-pane ul, .preview-pane ol { padding-left: 1.5rem; margin-bottom: 0.75rem; }
</style>
@endpush

@section('content')

<div class="admin-card" style="max-width:700px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/messages" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">Edit message</h2>
    </div>
    <form method="POST" action="/admin/messages/{{ $message->messageCode }}/edit">
        @csrf
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" id="subject-input" class="form-control" value="{{ $message->messageSubject }}" required>
        </div>
        <div class="form-group">
            <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span>Message body</span>
                <button type="button" id="preview-toggle" onclick="togglePreview()" style="background:none; border:1px solid #e8e8e8; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:600; color:#262c39; cursor:pointer;">
                    <i class="fa-solid fa-eye"></i> Preview
                </button>
            </label>
            <div class="editor-wrap" id="editor-wrap">
                <div class="editor-toolbar">
                    <button type="button" onmousedown="event.preventDefault(); document.execCommand('bold')"><b>B</b></button>
                    <button type="button" onmousedown="event.preventDefault(); document.execCommand('italic')"><i>I</i></button>
                    <button type="button" onmousedown="event.preventDefault(); document.execCommand('underline')"><u>U</u></button>
                    <button type="button" onmousedown="event.preventDefault(); document.execCommand('insertUnorderedList')">• List</button>
                    <button type="button" onmousedown="event.preventDefault(); document.execCommand('insertOrderedList')">1. List</button>
                    <button type="button" onmousedown="event.preventDefault(); insertLink()">Link</button>
                </div>
                <div class="editor-content"
                     id="editor-content"
                     contenteditable="true"
                     oninput="syncContent()"></div>
            </div>
            <div id="preview-wrap" style="display:none; border:1px solid #e8e8e8; border-radius:8px; overflow:hidden;">
                <div style="padding:8px 12px; background:#f8f8f8; border-bottom:1px solid #e8e8e8; font-size:12px; color:#888;">
                    Preview as <strong style="color:#262c39;">{{ $samplePlayer?->memberNameFirst ?? 'Sample' }} {{ $samplePlayer?->memberNameLast ?? 'Player' }}</strong>
                    @if($samplePlayer) <code style="background:#fff; border:1px solid #e8e8e8; padding:1px 6px; border-radius:4px;">{{ $samplePlayer->memberCode }}</code> @endif
                </div>
                <div class="preview-pane" id="preview-content"></div>
            </div>
            <div class="merge-helper">
                <div class="merge-helper-label">Insert variable — click a chip to drop it in the editor. Variables are replaced with each player's real data when the link is opened.</div>
                <div class="merge-chips">
                    @foreach($mergeVariables as $key => $desc)
                    <button type="button" class="merge-chip" title="{{ $desc }}" onmousedown="event.preventDefault(); insertMergeVar('{{ $key }}')">&#123;&#123;{{ $key }}&#125;&#125;</button>
                    @endforeach
                </div>
            </div>
            <textarea name="body" id="body-textarea" style="display:none;"></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="active" class="form-control">
                <option value="1" {{ $message->messageActive ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$message->messageActive ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Save changes
            </button>
            <a href="/admin/messages/{{ $message->messageCode }}/links" class="btn btn-secondary">
                <i class="fa-solid fa-link"></i> View links
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const sampleValues = @json($sampleValues);

    document.getElementById('editor-content').innerHTML = @json($message->messageBody);
    document.getElementById('body-textarea').value = @json($message->messageBody);

    function syncContent() {
        document.getElementById('body-textarea').value =
            document.getElementById('editor-content').innerHTML;
    }
    function insertLink() {
        const url = prompt('Enter URL (include https://):');
        if (url) {
            document.getElementById('editor-content').focus();
            document.execCommand('createLink', false, url);
        }
        syncContent();
    }
    function insertMergeVar(name) {
        const ob = String.fromCharCode(123, 123);
        const cb = String.fromCharCode(125, 125);
        const placeholder = ob + name + cb;
        const editor = document.getElementById('editor-content');
        editor.focus();
        const sel = window.getSelection();
        let inserted = false;
        if (sel && sel.rangeCount && editor.contains(sel.anchorNode)) {
            inserted = document.execCommand('insertText', false, placeholder);
        }
        if (!inserted) {
            editor.innerHTML += placeholder;
        }
        syncContent();
    }
    function renderMerge(text) {
        let out = text || '';
        for (const [key, val] of Object.entries(sampleValues)) {
            const safe = String(val).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            const pattern = new RegExp('\\{\\{\\s*' + key + '\\s*\\}\\}', 'g');
            out = out.replace(pattern, safe);
        }
        return out;
    }
    function togglePreview() {
        const editorWrap  = document.getElementById('editor-wrap');
        const previewWrap = document.getElementById('preview-wrap');
        const btn         = document.getElementById('preview-toggle');
        if (previewWrap.style.display === 'none') {
            syncContent();
            document.getElementById('preview-content').innerHTML =
                renderMerge(document.getElementById('editor-content').innerHTML);
            editorWrap.style.display = 'none';
            previewWrap.style.display = '';
            btn.innerHTML = '<i class="fa-solid fa-pen"></i> Edit';
        } else {
            editorWrap.style.display = '';
            previewWrap.style.display = 'none';
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview';
        }
    }
    document.querySelector('form').onsubmit = function() {
        syncContent();
        return true;
    };
</script>
@endpush

@endsection
