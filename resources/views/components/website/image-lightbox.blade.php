
<div
    x-data="{
        images: [],
        currentIndex: 0,
        autoplay: false,
        timer: null,
        interval: 4000,

        get currentImage() {
            return this.images[this.currentIndex] || {};
        },

        openGallery(data) {
            if (!data.images || !data.images.length) return;

            this.stopAutoplay();

            this.images = data.images;

            this.currentIndex = Math.min(
                Math.max(Number(data.index) || 0, 0),
                this.images.length - 1
            );

            if (!this.$refs.lightbox.open) {
                this.$refs.lightbox.showModal();
                document.body.classList.add('overflow-hidden');
            }

            if (
                data.autoplay &&
                !window.matchMedia('(prefers-reduced-motion: reduce)').matches
            ) {
                this.startAutoplay();
            }
        },

        closeGallery() {
            this.stopAutoplay();
            this.$refs.lightbox.close();
        },

        nextImage() {
            if (!this.images.length) return;

            this.currentIndex =
                (this.currentIndex + 1) % this.images.length;
        },

        previousImage() {
            if (!this.images.length) return;

            this.currentIndex =
                (this.currentIndex - 1 + this.images.length)
                % this.images.length;
        },

        manualNext() {
            this.nextImage();

            if (this.autoplay) {
                this.startAutoplay();
            }
        },

        manualPrevious() {
            this.previousImage();

            if (this.autoplay) {
                this.startAutoplay();
            }
        },

        startAutoplay() {
            this.stopAutoplay();

            if (
                this.images.length <= 1 ||
                !this.$refs.lightbox.open
            ) return;

            this.autoplay = true;

            this.timer = setInterval(() => {
                this.nextImage();
            }, this.interval);
        },

        stopAutoplay() {
            if (this.timer !== null) {
                clearInterval(this.timer);
                this.timer = null;
            }

            this.autoplay = false;
        },

        toggleAutoplay() {
            if (this.autoplay) {
                this.stopAutoplay();
            } else {
                this.startAutoplay();
            }
        },

        destroy() {
            this.stopAutoplay();
        }
    }"

    @open-image-gallery.window="openGallery($event.detail)"
>

    <dialog
        x-ref="lightbox"
        @cancel.prevent="closeGallery()"
        @close="
            stopAutoplay();
            document.body.classList.remove('overflow-hidden');
        "
        @click.self="closeGallery()"
        @keydown.right.prevent="manualNext()"
        @keydown.left.prevent="manualPrevious()"
        aria-labelledby="image-lightbox-title"
        class="fixed inset-0 m-auto max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-[680px] overflow-y-auto border-0 bg-ivory p-0 text-navy backdrop:bg-[#071522]/80"
    >

        <div class="p-5 sm:p-8 lg:p-9">

            {{-- Popup Header --}}
            <div class="mb-6 flex items-center justify-between gap-4">

                <h2
                    id="image-lightbox-title"
                    x-text="currentImage.title"
                    class="font-heading text-[24px] font-normal leading-tight text-navy sm:text-[28px]"
                ></h2>

                <button
                    type="button"
                    @click="closeGallery()"
                    aria-label="Close image gallery"
                    class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center text-navy transition-colors hover:text-dark-gold"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.3"
                    >
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>

            </div>

            {{-- Main Image --}}
            <div class="flex aspect-[1.5] max-h-[60dvh] items-center justify-center overflow-hidden">

                <template x-if="currentImage.src">

                    <img
                        :src="currentImage.src"
                        :alt="currentImage.title"
                        class="h-full w-full object-contain"
                    >

                </template>

            </div>

            {{-- Gallery Controls --}}
            <div class="mt-6 flex items-center justify-between gap-3">

                {{-- Previous --}}
                <button
                    type="button"
                    @click="manualPrevious()"
                    :disabled="images.length <= 1"
                    class="cursor-pointer border-b border-gold/70 pb-2.5 text-[13px] text-navy transition-colors hover:text-dark-gold disabled:opacity-40"
                >
                    ← Previous
                </button>

                {{-- Counter and Auto-play --}}
                <div class="flex items-center gap-3">

                    <span
                        :aria-live="autoplay ? 'off' : 'polite'"
                        class="text-[13px] font-medium text-navy"
                        x-text="images.length
                            ? (currentIndex + 1) + ' / ' + images.length
                            : '0 / 0'"
                    ></span>

                    <button
                        type="button"
                        @click="toggleAutoplay()"
                        :disabled="images.length <= 1"
                        :aria-label="autoplay
                            ? 'Pause slideshow'
                            : 'Play slideshow'"
                        :title="autoplay
                            ? 'Pause slideshow'
                            : 'Play slideshow'"
                        class="flex h-8 w-8 cursor-pointer items-center justify-center text-navy transition-colors hover:text-dark-gold disabled:opacity-40"
                    >

                        {{-- Pause Icon --}}
                        <svg
                            x-show="autoplay"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <rect x="6" y="4" width="4" height="16"/>
                            <rect x="14" y="4" width="4" height="16"/>
                        </svg>

                        {{-- Play Icon --}}
                        <svg
                            x-show="!autoplay"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path d="M8 5v14l11-7z"/>
                        </svg>

                    </button>

                </div>

                {{-- Next --}}
                <button
                    type="button"
                    @click="manualNext()"
                    :disabled="images.length <= 1"
                    class="cursor-pointer border-b border-gold/70 pb-2.5 text-[13px] text-navy transition-colors hover:text-dark-gold disabled:opacity-40"
                >
                    Next →
                </button>

            </div>

        </div>

    </dialog>

</div>
