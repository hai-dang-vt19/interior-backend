@extends('site.base')

@section('content')
    @php
        $activeTab = request()->query('tab', 'info');
        if ($errors->any()) {
            $activeTab = old('_tab', $activeTab);
        }
        if (!in_array($activeTab, ['info', 'password'], true)) {
            $activeTab = 'info';
        }
    @endphp

    <section class="site-account-page">
        <div class="site-account-tabs" role="tablist" aria-label="Tùy chọn tài khoản">
            <button
                type="button"
                class="site-account-tab-btn {{ $activeTab === 'info' ? 'is-active' : '' }}"
                data-tab-target="info"
                role="tab"
                aria-selected="{{ $activeTab === 'info' ? 'true' : 'false' }}"
            >
                Thông tin
            </button>
            <button
                type="button"
                class="site-account-tab-btn {{ $activeTab === 'password' ? 'is-active' : '' }}"
                data-tab-target="password"
                role="tab"
                aria-selected="{{ $activeTab === 'password' ? 'true' : 'false' }}"
            >
                Đổi mật khẩu
            </button>
        </div>

        <div
            class="site-account-panel {{ $activeTab === 'info' ? 'is-active' : '' }}"
            data-tab-pane="info"
            role="tabpanel"
            @if ($activeTab !== 'info') hidden @endif
        >
            <form action="{{ route('site.account.update') }}" method="POST" class="site-account-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_tab" value="info">

                <div class="site-account-field">
                    <label for="full_name">Họ tên</label>
                    <input
                        id="full_name"
                        name="full_name"
                        type="text"
                        value="{{ old('full_name', $customer->full_name ?? '') }}"
                        required
                    >
                </div>

                <div class="site-account-field">
                    <label for="phone">SDT</label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        value="{{ old('phone', $customer->phone ?? '') }}"
                    >
                </div>

                <div class="site-account-field">
                    <label for="address">Địa chỉ</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="4"
                    >{{ old('address', $customer->address ?? '') }}</textarea>
                </div>

                <button type="submit" class="site-account-submit">Cập nhật</button>
            </form>
        </div>

        <div
            class="site-account-panel {{ $activeTab === 'password' ? 'is-active' : '' }}"
            data-tab-pane="password"
            role="tabpanel"
            @if ($activeTab !== 'password') hidden @endif
        >
            <form action="{{ route('site.account.password.update') }}" method="POST" class="site-account-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_tab" value="password">

                <div class="site-account-field">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input id="current_password" name="current_password" type="password" required>
                </div>

                <div class="site-account-field">
                    <label for="new_password">Mật khẩu mới</label>
                    <input id="new_password" name="new_password" type="password" required>
                </div>

                <div class="site-account-field">
                    <label for="new_password_confirmation">Nhập lại mật khẩu mới</label>
                    <input id="new_password_confirmation" name="new_password_confirmation" type="password" required>
                </div>

                <button type="submit" class="site-account-submit">Cập nhật</button>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    @parent
    <script type="module">
        const tabButtons = document.querySelectorAll('.site-account-tab-btn');
        const tabPanes = document.querySelectorAll('.site-account-panel');

        if (tabButtons.length && tabPanes.length) {
            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.getAttribute('data-tab-target');
                    if (!target) {
                        return;
                    }

                    tabButtons.forEach((btn) => {
                        const active = btn === button;
                        btn.classList.toggle('is-active', active);
                        btn.setAttribute('aria-selected', active ? 'true' : 'false');
                    });

                    tabPanes.forEach((pane) => {
                        const active = pane.getAttribute('data-tab-pane') === target;
                        pane.classList.toggle('is-active', active);
                        if (active) {
                            pane.removeAttribute('hidden');
                        } else {
                            pane.setAttribute('hidden', '');
                        }
                    });
                });
            });
        }
    </script>
@endsection
