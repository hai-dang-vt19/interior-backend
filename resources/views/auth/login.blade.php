@extends('auth.base')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-sm login-form">
                <div class="card-body p-4">
                    <h3 class="text-center mb-1 fw-bold">Chung Si Interior</h3>
                    <p class="text-center text-muted small mb-4">Đăng nhập quản trị</p>
                    <form id="loginForm" action="{{ route('admin.login.submit') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại @include('component.required-mark')</label>
                            <input type="text" class="form-control input-number" id="phone" name="phone"
                                value="0947508288" required>
                            <div class="invalid-feedback" id="phone-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu @include('component.required-mark')</label>
                            <input type="password" class="form-control" id="password" name="password" value="12345678"
                                required>
                            <div class="invalid-feedback" id="password-error"></div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Đăng nhập</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    Loading.hide();
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                            const input = $(`#${field}`);
                            const errorDiv = $(`#${field}-error`);

                            input.addClass('is-invalid');
                            errorDiv.text(errors[field][0]);
                        }
                    } else if (xhr.status === 401 || xhr.status === 403) {
                        Alert.error(xhr.responseJSON.error.msg);
                    }
                }
            });
        });
    });
</script>
@endsection
