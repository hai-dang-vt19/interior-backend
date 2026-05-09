@php($loyaltySections = \App\Support\CustomerLoyalty::adminPolicySections())
<div class="card border-secondary-subtle mb-4" id="admin-customer-loyalty-policy">
    <div class="card-header bg-light">
        <h6 class="mb-0">Tiêu chí hạng, điểm &amp; ưu đãi (áp dụng hệ thống hiện tại)</h6>
    </div>
    <div class="card-body">
        @foreach ($loyaltySections as $block)
            <section class="@if(!$loop->last) mb-4 @endif">
                <h6 class="text-primary">{{ $block['title'] }}</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($block['items'] as $line)
                        <li class="mb-1">{{ $line }}</li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</div>
