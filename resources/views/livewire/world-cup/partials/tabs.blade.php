{{-- Page navigation — links between the four World Cup pages. The active tab is
     driven by $activeTab (the route name captured at mount), NOT request()->routeIs():
     during a Livewire poll/update the current request points at /livewire/update,
     so routeIs() would be false and the highlight would drop after the first poll.
     Falls back to the live route name for any non-Livewire include. --}}
@php $active = $activeTab ?? request()->route()?->getName(); @endphp
<div class="wc-tabs">
    <a href="{{ route('worldcup.ladder') }}" class="wc-tab {{ $active === 'worldcup.ladder' ? 'active' : '' }}">Ladder</a>
    <a href="{{ route('worldcup.results') }}" class="wc-tab {{ $active === 'worldcup.results' ? 'active' : '' }}">Results</a>
    <a href="{{ route('worldcup.upcoming') }}" class="wc-tab {{ $active === 'worldcup.upcoming' ? 'active' : '' }}">Upcoming</a>
    <a href="{{ route('worldcup.cards') }}" class="wc-tab {{ $active === 'worldcup.cards' ? 'active' : '' }}">Cards</a>
</div>
