@props([
    'title' => null,
    'description' => null,
    'actions' => null,
    'eyebrow' => null,
    'searchAction' => null,
    'searchName' => 'q',
    'searchPlaceholder' => 'Cari data...',
    'hideSearch' => false,
])

<div class="admin-page-top admin-page-top--compact">
    @if(!$hideSearch)
        <form method="GET" action="{{ $searchAction ?? url()->current() }}" class="header-search">
            @foreach(request()->query() as $key => $value)
                @if($key !== $searchName)
                    <input type="hidden" name="{{ $key }}" value="{{ is_array($value) ? json_encode($value) : $value }}">
                @endif
            @endforeach
            <div class="header-search__field">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="{{ $searchName }}" value="{{ request($searchName) }}" placeholder="{{ $searchPlaceholder }}" aria-label="Cari data">
                <button type="submit" class="search-submit-icon" title="Cari" aria-label="Cari"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
            </div>
        </form>
    @endif

    @if($actions)
        <div class="header-actions">
            {!! $actions !!}
        </div>
    @endif
</div>
