<div class="site-empty">
    <div class="site-empty-graphic" aria-hidden="true">
        <svg viewBox="0 0 160 120" xmlns="http://www.w3.org/2000/svg">
            <rect x="18" y="28" width="124" height="72" rx="12" fill="#eef3f8" />
            <rect x="36" y="44" width="88" height="8" rx="4" fill="#c8d6e5" />
            <rect x="36" y="60" width="64" height="8" rx="4" fill="#d7e3ef" />
            <circle cx="80" cy="24" r="12" fill="#2c3e50" opacity="0.9" />
            <path d="M74 24h12M80 18v12" stroke="#fff" stroke-width="2.5" stroke-linecap="round" />
        </svg>
    </div>
    <h6 class="mb-1">{{ $title ?? 'Chưa có dữ liệu' }}</h6>
    <p class="site-muted mb-3">{{ $description ?? 'Không có dữ liệu phù hợp.' }}</p>
    @if (!empty($actionUrl) && !empty($actionText))
        <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-dark">{{ $actionText }}</a>
    @endif
</div>
