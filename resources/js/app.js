import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.js';
import Alert from './common/sweetalert';
import { Loading } from './common/loading';
import { Common } from './common/common';
import { Validation } from './common/validation';
import { FlatpickrConfig } from './common/flatpickr';
import { TooltipConfig } from './common/tooltip';
import $ from 'jquery';

// Make Alert, Loading, Ajax and jQuery available globally
window.Alert = Alert;
window.Loading = Loading;
window.$ = $;
window.jQuery = $;

// Initialize common functions
$(function () {
    Common.init();
    Validation.init();
    FlatpickrConfig.init();
    TooltipConfig.init();
});

// Example usage alert
// Alert.success('Thao tác thành công!');
// Alert.error('Đã có lỗi xảy ra!');
// Alert.warning('Vui lòng kiểm tra lại!');
// Alert.info('Thông tin cập nhật!');
// Alert.confirm({
//     title: 'Xóa sản phẩm',
//     text: 'Bạn có chắc chắn muốn xóa sản phẩm này?',
//     confirmButtonText: 'Xóa',
//     cancelButtonText: 'Hủy'
// }).then((result) => {
//     if (result.isConfirmed) {
//         // Xử lý khi người dùng xác nhận
//     }
// });
