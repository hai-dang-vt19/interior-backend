import $ from 'jquery';

/**
 * Khởi tạo Swiper cho trang site (hero đôi, swiper danh mục).
 * Phụ thuộc Swiper global từ CDN (site base layout).
 */
export const SwiperConfig = {
    init: function () {
        if (typeof window.Swiper === 'undefined') {
            return;
        }
        const Swiper = window.Swiper;

        const $heroFront = $('#site-hero-swiper-front');
        const $heroBack = $('#site-hero-swiper-back');
        if ($heroFront.length && $heroBack.length) {
            const heroAutoplay = { delay: 5500, disableOnInteraction: false };
            new Swiper($heroFront.get(0), {
                direction: 'vertical',
                loop: true,
                speed: 600,
                slidesPerView: 1,
                allowTouchMove: false,
                autoplay: { ...heroAutoplay, reverseDirection: true },
            });
            new Swiper($heroBack.get(0), {
                direction: 'vertical',
                loop: true,
                speed: 600,
                slidesPerView: 1,
                allowTouchMove: false,
                autoplay: heroAutoplay,
            });
        }

        $('[data-category-swiper]').each(function () {
            new Swiper(this, {
                slidesPerView: 1.15,
                spaceBetween: 14,
                breakpoints: {
                    576: { slidesPerView: 2.1, spaceBetween: 16 },
                    768: { slidesPerView: 2.5, spaceBetween: 18 },
                    992: { slidesPerView: 3, spaceBetween: 20 },
                },
            });
        });
    },
};
