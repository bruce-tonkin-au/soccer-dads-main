@php
    $mergeVariables = \App\Support\MessageMerge::variables();
    $samplePlayer = \Illuminate\Support\Facades\DB::table('members')
        ->where('memberActive', 1)
        ->whereNull('memberParent')
        ->orderBy('memberID')
        ->first();
    $sampleValues = $samplePlayer ? \App\Support\MessageMerge::valuesFor($samplePlayer) : [];
@endphp

<div x-data="messageMergeHelper(@js($sampleValues))">
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm dark:border-white/10 dark:bg-white/5">
        <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">
            Insert variable — click a chip to drop it into the body at the cursor. Variables are
            replaced with each player's real data when the link is opened.
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @foreach ($mergeVariables as $key => $desc)
                <button
                    type="button"
                    title="{{ $desc }}"
                    x-on:click="insertTag(@js($key))"
                    class="rounded-full border border-gray-300 bg-white px-2 py-1 font-mono text-xs text-gray-700 hover:bg-primary-600 hover:text-white dark:border-white/20 dark:bg-white/10 dark:text-gray-200"
                >&#123;&#123;{{ $key }}&#125;&#125;</button>
            @endforeach

            <button
                type="button"
                x-on:click="togglePreview()"
                x-text="showPreview ? 'Edit' : 'Preview'"
                class="ml-auto rounded-md border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 dark:border-white/20 dark:text-gray-200"
            ></button>
        </div>

        <div x-show="showPreview" x-cloak class="mt-3 rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-3 py-2 text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">
                Preview as
                <strong class="text-gray-700 dark:text-gray-200">{{ $samplePlayer?->memberNameFirst ?? 'Sample' }} {{ $samplePlayer?->memberNameLast ?? 'Player' }}</strong>
                @if ($samplePlayer)
                    <code class="rounded bg-gray-100 px-1 dark:bg-white/10">{{ $samplePlayer->memberCode }}</code>
                @endif
            </div>
            <div x-ref="preview" class="fi-prose p-3"></div>
        </div>
    </div>
</div>

<script>
    window.messageMergeHelper = function (sampleValues) {
        return {
            showPreview: false,
            sampleValues: sampleValues,

            // Reach the sibling RichEditor's Alpine component (which exposes the
            // TipTap editor instance as `editor`) within the same form.
            findEditor() {
                const scope = this.$el.closest('form') || document;
                const el = scope.querySelector('.fi-fo-rich-editor');
                if (! el || ! window.Alpine) return null;
                try { return window.Alpine.$data(el); } catch (e) { return null; }
            },

            insertTag(key) {
                const ob = String.fromCharCode(123, 123);
                const cb = String.fromCharCode(125, 125);
                const placeholder = ob + key + cb;

                const cmp = this.findEditor();
                if (cmp && cmp.editor) {
                    cmp.editor.chain().focus().insertContent(placeholder).run();
                    return;
                }

                // Fallback if the editor instance can't be reached: insert into
                // the contenteditable surface directly.
                const scope = this.$el.closest('form') || document;
                const surface = scope.querySelector('.fi-fo-rich-editor-content');
                if (surface) {
                    surface.focus();
                    if (! document.execCommand('insertText', false, placeholder)) {
                        surface.innerHTML += placeholder;
                    }
                }
            },

            togglePreview() {
                this.showPreview = ! this.showPreview;
                if (this.showPreview) this.renderPreview();
            },

            renderPreview() {
                const cmp = this.findEditor();
                let html = '';
                if (cmp && cmp.editor) {
                    html = cmp.editor.getHTML();
                } else {
                    const scope = this.$el.closest('form') || document;
                    const surface = scope.querySelector('.fi-fo-rich-editor-content');
                    html = surface ? surface.innerHTML : '';
                }
                this.$refs.preview.innerHTML = this.renderMerge(html);
            },

            // Mirrors the legacy renderMerge(): substitute each merge tag with
            // the sample player's values, escaping the value.
            renderMerge(text) {
                let out = text || '';
                for (const [key, val] of Object.entries(this.sampleValues)) {
                    const safe = String(val)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    const pattern = new RegExp('\\{\\{\\s*' + key + '\\s*\\}\\}', 'g');
                    out = out.replace(pattern, safe);
                }
                return out;
            },
        };
    };
</script>
