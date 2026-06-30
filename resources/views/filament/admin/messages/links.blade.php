@php
    $code = $message->messageCode;
    $smsLink = url('/msg/' . $code) . '/{memberCode}';
    $newsletterLink = url('/msg/' . $code . '/newsletter');
@endphp

<div
    x-data="{
        copy(text, ev) {
            if (navigator.clipboard) navigator.clipboard.writeText(text);
            const b = ev.target.closest('button');
            if (b) { const o = b.innerText; b.innerText = 'Copied!'; setTimeout(() => b.innerText = o, 1500); }
        }
    }"
    class="space-y-4 text-sm"
>
    <div>
        <div class="font-semibold text-gray-700 dark:text-gray-200">SMS link</div>
        <div class="mb-1 text-xs text-gray-500 dark:text-gray-400">
            Per-player link — replace <code>{memberCode}</code> with the player's code.
        </div>
        <div class="flex items-center gap-2">
            <code class="flex-1 overflow-x-auto rounded bg-gray-100 px-2 py-1 text-xs dark:bg-white/10">{{ $smsLink }}</code>
            <button type="button" x-on:click="copy(@js($smsLink), $event)" class="shrink-0 rounded-md border border-gray-300 px-2 py-1 text-xs dark:border-white/20">Copy</button>
        </div>
    </div>

    <div>
        <div class="font-semibold text-gray-700 dark:text-gray-200">Newsletter link</div>
        <div class="mb-1 text-xs text-gray-500 dark:text-gray-400">
            Email-friendly HTML page to paste into Sendy.
        </div>
        <div class="flex items-center gap-2">
            <code class="flex-1 overflow-x-auto rounded bg-gray-100 px-2 py-1 text-xs dark:bg-white/10">{{ $newsletterLink }}</code>
            <button type="button" x-on:click="copy(@js($newsletterLink), $event)" class="shrink-0 rounded-md border border-gray-300 px-2 py-1 text-xs dark:border-white/20">Copy</button>
            <a href="{{ $newsletterLink }}" target="_blank" rel="noopener" class="shrink-0 rounded-md border border-gray-300 px-2 py-1 text-xs dark:border-white/20">Preview</a>
        </div>
    </div>
</div>
