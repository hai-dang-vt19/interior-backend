import Swal from 'sweetalert2';

// Cấu hình mặc định cho SweetAlert2
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Các hàm thông báo thông dụng
const Alert = {
    // Thông báo thành công
    success: (message) => {
        Toast.fire({
            icon: 'success',
            title: message
        });
    },

    // Thông báo lỗi
    error: (message) => {
        Toast.fire({
            icon: 'error',
            title: message
        });
    },

    // Thông báo cảnh báo
    warning: (message) => {
        Toast.fire({
            icon: 'warning',
            title: message
        });
    },

    // Thông báo thông tin
    info: (message) => {
        Toast.fire({
            icon: 'info',
            title: message
        });
    },

    // Xác nhận hành động
    confirm: (options) => {
        return Swal.fire({
            title: options.title || 'Bạn có chắc chắn?',
            text: options.text || 'Bạn sẽ không thể hoàn tác hành động này!',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: options.confirmButtonText || 'Đồng ý',
            cancelButtonText: options.cancelButtonText || 'Hủy'
        });
    },
    
    delete: (options) => {
        return Swal.fire({
            title: options.title || 'Bạn có chắc chắn xóa không?',
            text: options.text || 'Bạn sẽ không thể hoàn tác hành động này!',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: options.confirmButtonText || 'Đồng ý',
            denyButtonText: options.cancelButtonText || 'Hủy'
        });
    }
};

// Export để sử dụng trong các file khác
export default Alert; 