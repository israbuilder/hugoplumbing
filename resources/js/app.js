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

        isPaused: false,

        init() {

            this.updateClock();

            this.clockTimer = window.setInterval(() => {

                this.updateClock();

            }, 1000);

            this.$nextTick(() => {

                this.initializeSwiper();

            });

            /*
             * Livewire ejecuta este evento
             * después de refreshDashboard().
             */
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

            const numberOfSlides =
                element.querySelectorAll(
                    '.swiper-slide'
                ).length;

            this.slideCount = numberOfSlides;

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

                /*
                 * Solamente hacemos loop
                 * cuando realmente hay más de un slide.
                 */
                loop: numberOfSlides > 1,

                allowTouchMove: false,

                autoplay: numberOfSlides > 1
                    ? {
                        /*
                         * Este es el fallback.
                         *
                         * Tu data-swiper-autoplay
                         * individual tiene prioridad.
                         */
                        delay: 10000,

                        disableOnInteraction: false,

                        pauseOnMouseEnter: false,
                    }
                    : false,

                keyboard: {
                    enabled: true,
                },

                on: {

                    init: (swiper) => {

                        this.slideCount =
                            swiper.slides.length;

                        this.currentSlide =
                            swiper.realIndex ?? 0;

                    },

                    slideChange: (swiper) => {

                        this.currentSlide =
                            swiper.realIndex ?? 0;

                    },

                },

            });

            /*
             * Si el usuario había pausado
             * antes del refresh de Livewire,
             * respetamos la pausa.
             */
            if (
                this.isPaused
                && this.swiper?.autoplay
            ) {
                this.swiper.autoplay.stop();
            }

        },

        restartSwiper() {

            if (this.restarting) {
                return;
            }

            this.restarting = true;

            /*
             * Recordamos dónde estaba Swiper
             * antes del refresh.
             */
            const previousIndex =
                this.swiper?.realIndex ?? 0;

            this.destroySwiper();

            /*
             * Dejamos que Livewire termine
             * de actualizar el DOM.
             */
            window.setTimeout(() => {

                this.$nextTick(() => {

                    this.initializeSwiper();

                    if (
                        this.swiper
                        && previousIndex > 0
                        && previousIndex < this.slideCount
                    ) {

                        if (
                            typeof this.swiper.slideToLoop
                            === 'function'
                        ) {

                            this.swiper.slideToLoop(
                                previousIndex,
                                0
                            );

                        } else {

                            this.swiper.slideTo(
                                previousIndex,
                                0
                            );

                        }

                    }

                    /*
                     * Después de movernos al slide anterior,
                     * aseguramos que autoplay continúe.
                     */
                    if (
                        ! this.isPaused
                        && this.swiper?.autoplay
                    ) {

                        this.swiper.autoplay.start();

                    }

                    this.restarting = false;

                });

            }, 150);

        },

        destroySwiper() {

            if (! this.swiper) {
                return;
            }

            try {

                this.swiper.destroy(
                    true,
                    false
                );

            } catch (error) {

                console.warn(
                    'Error destroying Swiper:',
                    error
                );

            }

            this.swiper = null;

        },

        previous() {

            if (! this.swiper) {
                return;
            }

            this.swiper.slidePrev();

        },

        next() {

            if (! this.swiper) {
                return;
            }

            this.swiper.slideNext();

        },

        toggleAutoplay() {

            if (! this.swiper?.autoplay) {
                return;
            }

            if (this.isPaused) {

                this.swiper.autoplay.start();

                this.isPaused = false;

            } else {

                this.swiper.autoplay.stop();

                this.isPaused = true;

            }

        },

        requestFullscreen() {

            const element =
                document.documentElement;

            if (! document.fullscreenElement) {

                element
                    .requestFullscreen?.();

            } else {

                document
                    .exitFullscreen?.();

            }

        },

        updateClock() {

            const now = new Date();

            this.clock =
                new Intl.DateTimeFormat(
                    'en-US',
                    {
                        timeZone: 'America/Chicago',
                        hour: 'numeric',
                        minute: '2-digit',
                        second: '2-digit',
                    }
                ).format(now);

            this.date =
                new Intl.DateTimeFormat(
                    'en-US',
                    {
                        timeZone: 'America/Chicago',
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