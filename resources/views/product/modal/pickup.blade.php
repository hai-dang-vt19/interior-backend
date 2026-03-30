<div class="modal fade" id="modalPickupProductSlider" tabindex="-1" aria-labelledby="modalPickupProductSliderLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPickupProductSliderLabel">Chọn sản phẩm hiển thị banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form action="{{ route('admin.product.banner-products.update') }}" method="POST" id="bannerPickupForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Mỗi cột chọn tối đa 3 sản phẩm để hiển thị lên slider trang chủ.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header fw-bold">Cột trái</div>
                                <div class="card-body">
                                    @for ($i = 1; $i <= 3; $i++)
                                        @php($selectedLeft = old("left.$i", optional($bannerBySide['left']->firstWhere('position', $i))->product_id))
                                        <div class="mb-3">
                                            <label class="form-label" for="banner_left_{{ $i }}">Item {{ $i }}</label>
                                            <select
                                                class="form-select js-banner-select"
                                                id="banner_left_{{ $i }}"
                                                data-column="left"
                                                name="left[{{ $i }}]"
                                            >
                                                <option value="">-- Chọn sản phẩm --</option>
                                                @foreach ($bannerCandidateProducts as $product)
                                                    <option value="{{ $product->id }}" {{ (int) $selectedLeft === (int) $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} (#{{ $product->id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header fw-bold">Cột phải</div>
                                <div class="card-body">
                                    @for ($i = 1; $i <= 3; $i++)
                                        @php($selectedRight = old("right.$i", optional($bannerBySide['right']->firstWhere('position', $i))->product_id))
                                        <div class="mb-3">
                                            <label class="form-label" for="banner_right_{{ $i }}">Item {{ $i }}</label>
                                            <select
                                                class="form-select js-banner-select"
                                                id="banner_right_{{ $i }}"
                                                data-column="right"
                                                name="right[{{ $i }}]"
                                            >
                                                <option value="">-- Chọn sản phẩm --</option>
                                                @foreach ($bannerCandidateProducts as $product)
                                                    <option value="{{ $product->id }}" {{ (int) $selectedRight === (int) $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} (#{{ $product->id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <h6 class="mb-2">Danh sách cột trái</h6>
                            <ul class="list-group" id="bannerPreviewLeft">
                                <li class="list-group-item text-muted">Chưa chọn sản phẩm</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Danh sách cột phải</h6>
                            <ul class="list-group" id="bannerPreviewRight">
                                <li class="list-group-item text-muted">Chưa chọn sản phẩm</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btnApplyBannerProducts">Áp dụng</button>
                </div>
            </form>
        </div>
    </div>
</div>
