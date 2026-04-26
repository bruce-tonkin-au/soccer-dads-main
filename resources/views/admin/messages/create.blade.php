@extends('admin.layout')
@section('title', 'New Message')

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
</style>
@endpush

@section('content')

<div class="admin-card" style="max-width:700px;">
    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
        <a href="/admin/messages" class="btn btn-secondary" style="padding:6px 12px;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 style="margin-bottom:0;">New message</h2>
    </div>
    <form method="POST" action="/admin/messages/create">
        @csrf
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control" placeholder="e.g. Friday night — are you in?" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">Message body</label>
            <div class="editor-wrap">
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
            <textarea name="body" id="body-textarea" style="display:none;"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-paper-plane"></i> Create message & get links
        </button>
    </form>
</div>

@push('scripts')
<script>
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
    document.querySelector('form').onsubmit = function() {
        syncContent();
        return true;
    };
</script>
@endpush

@endsection
