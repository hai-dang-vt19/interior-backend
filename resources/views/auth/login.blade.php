@extends('auth.base')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-4">
            <div class="login-form">
                <h3 class="text-center mb-4">Chung Si Interior <br> Admintration</h3>
                <form id="loginForm" action="{{ route('admin.login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control input-number" id="phone" name="phone" value="0947508288" required>
                        <div class="invalid-feedback" id="phone-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" value="12345678" required>
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
@endsection

@section('scripts')
<script type="module">
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            // Reset error states
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
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                            const input = $(`#${field}`);
                            const errorDiv = $(`#${field}-error`);

                            input.addClass('is-invalid');
                            errorDiv.text(errors[field][0]);
                        }
                    } else if (xhr.status === 401 || xhr.status === 403) {
                        // Authentication errors
                        Alert.error(xhr.responseJSON.error.msg);
                    }
                }
            });
        });
    });
</script>
@endsection
