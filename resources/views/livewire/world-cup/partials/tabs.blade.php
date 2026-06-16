{{-- Page navigation — links between the four World Cup pages. Active tab is
     highlighted from the current route. --}}
<div class="wc-tabs">
    <a href="{{ route('worldcup.ladder') }}" class="wc-tab {{ request()->routeIs('worldcup.ladder') ? 'active' : '' }}">Ladder</a>
    <a href="{{ route('worldcup.results') }}" class="wc-tab {{ request()->routeIs('worldcup.results') ? 'active' : '' }}">Results</a>
    <a href="{{ route('worldcup.upcoming') }}" class="wc-tab {{ request()->routeIs('worldcup.upcoming') ? 'active' : '' }}">Upcoming</a>
    <a href="{{ route('worldcup.cards') }}" class="wc-tab {{ request()->routeIs('worldcup.cards') ? 'active' : '' }}">Cards</a>
</div>
