@extends('site.base')

@section('content')
    @php
        $activeTab = request()->query('tab', 'info');
        if ($errors->any()) {
            $activeTab = old('_tab', $activeTab);
        }
        if (!in_array($activeTab, ['info', 'password', 'addresses'], true)) {
            $activeTab = 'info';
        }
        $editingAddressId = (int) request()->query('edit', 0);
        $addressList = $addresses ?? collect();
    @endphp

    <section class="site-account-page">
        <header class="site-account-hero">
            <div class="site-account-hero-copy">
                <p class="site-account-kicker mb-1">Tài khoản khách hàng</p>
                <h1 class="site-account-heading mb-2">Quản lý thông tin cá nhân</h1>
                <p class="site-account-subtle mb-0">Cập nhật hồ sơ, đổi mật khẩu và quản lý địa chỉ giao hàng trên một giao diện gọn gàng, dễ dàng.</p>
            </div>
            <div class="site-account-hero-meta">
                <span class="site-account-hero-label">Xin chào</span>
                <strong>{{ $customer->full_name ?? 'Khách hàng' }}</strong>
                @if (!empty($customer->email))
                    <small>{{ $customer->email }}</small>
                @endif
            </div>
        </header>

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
            <button
                type="button"
                class="site-account-tab-btn {{ $activeTab === 'addresses' ? 'is-active' : '' }}"
                data-tab-target="addresses"
                role="tab"
                aria-selected="{{ $activeTab === 'addresses' ? 'true' : 'false' }}"
            >
                Địa chỉ
            </button>
        </div>

        <div class="site-account-shell">
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

        <div
            class="site-account-panel {{ $activeTab === 'addresses' ? 'is-active' : '' }}"
            data-tab-pane="addresses"
            role="tabpanel"
            @if ($activeTab !== 'addresses') hidden @endif
        >
            <p class="site-account-address-intro">Thêm hoặc chỉnh sửa địa chỉ giao hàng. Một địa chỉ có thể được đặt làm mặc định.</p>

            @unless ($editingAddressId)
                <h2 class="site-account-subheading">Thêm địa chỉ mới</h2>
                <form action="{{ route('site.account.addresses.store') }}" method="POST" class="site-account-form site-account-form--address">
                    @csrf
                    <input type="hidden" name="_tab" value="addresses">

                    <div class="site-account-field">
                        <label for="new_address_line">Địa chỉ cụ thể</label>
                        <input
                            id="new_address_line"
                            name="address_line"
                            type="text"
                            value="{{ old('address_line') }}"
                            required
                        >
                    </div>
                    <div class="site-account-field">
                        <label for="new_ward">Phường / xã</label>
                        <input id="new_ward" name="ward" type="text" value="{{ old('ward') }}">
                    </div>
                    <div class="site-account-field">
                        <label for="new_district">Quận / huyện</label>
                        <input id="new_district" name="district" type="text" value="{{ old('district') }}">
                    </div>
                    <div class="site-account-field">
                        <label for="new_city">Tỉnh / thành phố</label>
                        <input id="new_city" name="city" type="text" value="{{ old('city') }}">
                    </div>
                    <div class="site-account-field site-account-field--checkbox">
                        <input type="hidden" name="is_default" value="0">
                        <label>
                            <input type="checkbox" name="is_default" value="1" @checked(old('is_default', '0') === '1')>
                            Đặt làm địa chỉ mặc định
                        </label>
                    </div>
                    <button type="submit" class="site-account-submit">Thêm địa chỉ</button>
                </form>
            @endunless

            <h2 class="site-account-subheading">Địa chỉ đã lưu</h2>
            @forelse ($addressList as $address)
                @if ($editingAddressId === (int) $address->id)
                    <form
                        action="{{ route('site.account.addresses.update', $address->id) }}"
                        method="POST"
                        class="site-account-form site-account-form--address site-account-address-card"
                    >
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_tab" value="addresses">

                        <div class="site-account-field">
                            <label for="edit_address_line_{{ $address->id }}">Địa chỉ cụ thể</label>
                            <input
                                id="edit_address_line_{{ $address->id }}"
                                name="address_line"
                                type="text"
                                value="{{ old('address_line', $address->address_line) }}"
                                required
                            >
                        </div>
                        <div class="site-account-field">
                            <label for="edit_ward_{{ $address->id }}">Phường / xã</label>
                            <input id="edit_ward_{{ $address->id }}" name="ward" type="text" value="{{ old('ward', $address->ward) }}">
                        </div>
                        <div class="site-account-field">
                            <label for="edit_district_{{ $address->id }}">Quận / huyện</label>
                            <input id="edit_district_{{ $address->id }}" name="district" type="text" value="{{ old('district', $address->district) }}">
                        </div>
                        <div class="site-account-field">
                            <label for="edit_city_{{ $address->id }}">Tỉnh / thành phố</label>
                            <input id="edit_city_{{ $address->id }}" name="city" type="text" value="{{ old('city', $address->city) }}">
                        </div>
                        <div class="site-account-field site-account-field--checkbox">
                            <input type="hidden" name="is_default" value="0">
                            <label>
                                <input
                                    type="checkbox"
                                    name="is_default"
                                    value="1"
                                    @checked(old('is_default', $address->is_default ? '1' : '0') === '1')
                                >
                                Địa chỉ mặc định
                            </label>
                        </div>
                        <div class="site-account-address-actions">
                            <button type="submit" class="site-account-submit">Lưu thay đổi</button>
                            <a href="{{ route('site.account', ['tab' => 'addresses']) }}" class="site-account-link-cancel">Hủy</a>
                        </div>
                    </form>
                @else
                    <div class="site-account-address-card">
                        <div class="site-account-address-summary">
                            @if ($address->is_default)
                                <span class="site-account-address-badge">Mặc định</span>
                            @endif
                            <p class="site-account-address-lines">
                                {{ $address->address_line }}
                                @php
                                    $parts = array_filter([
                                        $address->ward,
                                        $address->district,
                                        $address->city,
                                    ], fn ($v) => filled($v));
                                @endphp
                                @if (count($parts))
                                    <br><span class="site-account-address-meta">{{ implode(', ', $parts) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="site-account-address-row-actions">
                            <a href="{{ route('site.account', ['tab' => 'addresses', 'edit' => $address->id]) }}" class="site-account-link">Sửa</a>
                            @if (!$address->is_default)
                                <form action="{{ route('site.account.addresses.default', $address->id) }}" method="POST" class="site-account-inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="site-account-link site-account-link--button">Đặt mặc định</button>
                                </form>
                            @endif
                            <form
                                action="{{ route('site.account.addresses.destroy', $address->id) }}"
                                method="POST"
                                class="site-account-inline-form"
                                onsubmit="return confirm('Xóa địa chỉ này?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="site-account-link site-account-link--danger site-account-link--button">Xóa</button>
                            </form>
                        </div>
                    </div>
                @endif
            @empty
                <p class="site-account-empty">Chưa có địa chỉ nào. Hãy thêm địa chỉ phía trên.</p>
            @endforelse
        </div>
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
