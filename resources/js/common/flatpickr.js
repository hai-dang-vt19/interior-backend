import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import 'flatpickr/dist/l10n/vn';

class FlatpickrConfig {
    static init() {
        // Cấu hình mặc định cho tất cả các flatpickr
        flatpickr(".flatpickr", {
            dateFormat: "d/m/Y",
            locale: "vn",
            allowInput: true,
            disableMobile: true
        });

        // Cấu hình cho flatpickr chỉ chọn ngày
        flatpickr(".flatpickr-date", {
            dateFormat: "d/m/Y",
            locale: "vn",
            allowInput: true,
            disableMobile: true
        });

        // Cấu hình cho flatpickr chọn ngày và giờ
        flatpickr(".flatpickr-datetime", {
            dateFormat: "d/m/Y H:i",
            enableTime: true,
            time_24hr: true,
            locale: "vn",
            allowInput: true,
            disableMobile: true
        });

        // Cấu hình cho flatpickr chọn khoảng thời gian
        flatpickr(".flatpickr-range", {
            mode: "range",
            dateFormat: "d/m/Y",
            locale: "vn",
            allowInput: true,
            disableMobile: true,
            locale: {
                rangeSeparator: " - "
            }
        });
    }

    // Hàm khởi tạo flatpickr với cấu hình tùy chỉnh
    static initCustom(selector, options = {}) {
        const defaultOptions = {
            dateFormat: "d/m/Y",
            locale: "vn",
            allowInput: true,
            disableMobile: true
        };

        return flatpickr(selector, {
            ...defaultOptions,
            ...options
        });
    }
}

// Export để sử dụng ở các file khác
export { FlatpickrConfig };
