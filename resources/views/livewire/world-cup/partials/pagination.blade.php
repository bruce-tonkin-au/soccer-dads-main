{{-- Simple prev / next pager for a Livewire paginator. Expects $paginator
     (a LengthAwarePaginator). Uses the WithPagination previousPage/nextPage
     methods so navigation stays within the live component. --}}
@if ($paginator->hasPages())
    <div class="wc-pagination">
        <button type="button" class="wc-page-btn" wire:click="previousPage" @disabled($paginator->onFirstPage())>‹ Prev</button>
        <span class="wc-page-info">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        <button type="button" class="wc-page-btn" wire:click="nextPage" @disabled(! $paginator->hasMorePages())>Next ›</button>
    </div>
@endif
