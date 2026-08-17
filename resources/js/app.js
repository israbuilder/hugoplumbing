import './bootstrap';

import Swiper from 'swiper';
import {
    Autoplay,
    EffectFade,
    Keyboard,
} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-fade';

window.Swiper = Swiper;
window.SwiperModules = {
    Autoplay,
    EffectFade,
    Keyboard,
};

document.addEventListener('alpine:init', () => {
    Alpine.data('tvDashboard', () => ({
        swiper: null,
        currentSlide: 0,
        slideCount: 0,
        clock: '',
        date: '',
        clockTimer: null,
        restarting: false,

        init() {
            this.updateClock();

            this.clockTimer = window.setInterval(() => {
                this.updateClock();
            }, 1000);

            this.$nextTick(() => {
                this.initializeSwiper();
            });

            window.addEventListener(
                'tv-dashboard-updated',
                () => {
                    this.restartSwiper();
                }
            );

            document.addEventListener(
                'livewire:navigating',
                () => {
                    this.destroySwiper();
                }
            );
        },

        initializeSwiper() {
            const element = this.$refs.swiper;

            if (! element) {
                return;
            }

            this.destroySwiper();

            this.swiper = new window.Swiper(element, {
                modules: [
                    window.SwiperModules.Autoplay,
                    window.SwiperModules.EffectFade,
                    window.SwiperModules.Keyboard,
                ],

                slidesPerView: 1,
                speed: 850,
                effect: 'fade',

                fadeEffect: {
                    crossFade: true,
                },

                loop: element.querySelectorAll(
                    '.swiper-slide'
                ).length > 1,

                allowTouchMove: false,

                autoplay: {
                    delay: 15000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: false,
                },

                keyboard: {
                    enabled: true,
                },

                on: {
                    init: (swiper) => {
                        this.slideCount =
                            swiper.slides.length;

                        this.currentSlide =
                            swiper.realIndex;
                    },

                    slideChange: (swiper) => {
                        this.currentSlide =
                            swiper.realIndex;
                    },
                },
            });
        },

        restartSwiper() {
            if (this.restarting) {
                return;
            }

            this.restarting = true;

            const previousIndex =
                this.swiper?.realIndex ?? 0;

            this.destroySwiper();

            window.setTimeout(() => {
                this.$nextTick(() => {
                    this.initializeSwiper();

                    if (
                        this.swiper
                        && previousIndex > 0
                        && previousIndex
                            < this.swiper.slides.length
                    ) {
                        this.swiper.slideToLoop(
                            previousIndex,
                            0
                        );
                    }

                    this.restarting = false;
                });
            }, 100);
        },

        destroySwiper() {
            if (! this.swiper) {
                return;
            }

            this.swiper.destroy(
                true,
                false
            );

            this.swiper = null;
        },

        previous() {
            this.swiper?.slidePrev();
        },

        next() {
            this.swiper?.slideNext();
        },

        toggleAutoplay() {
            if (! this.swiper?.autoplay) {
                return;
            }

            if (this.swiper.autoplay.running) {
                this.swiper.autoplay.stop();
            } else {
                this.swiper.autoplay.start();
            }
        },

        requestFullscreen() {
            const element =
                document.documentElement;

            if (! document.fullscreenElement) {
                element.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        },

        updateClock() {
            const now = new Date();

            this.clock = new Intl.DateTimeFormat(
                'en-US',
                {
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                }
            ).format(now);

            this.date = new Intl.DateTimeFormat(
                'es-US',
                {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                }
            ).format(now);
        },

        destroy() {
            this.destroySwiper();

            if (this.clockTimer) {
                window.clearInterval(
                    this.clockTimer
                );
            }
        },
    }));
});
