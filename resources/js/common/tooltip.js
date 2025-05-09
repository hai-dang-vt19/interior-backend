import { Tooltip } from 'bootstrap';

export const TooltipConfig = {
    init: function () {
        // Khởi tạo tất cả các tooltip
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
            html: true
        }));
    }
};

// Make TooltipConfig available globally
window.TooltipConfig = TooltipConfig; 