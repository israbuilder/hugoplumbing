<div
    class="tv-slide tv-slide-enter grid place-items-center px-10 py-8"
>

    @if($slide['announcement'])
        <div
            class="tv-glass grid w-full max-w-6xl grid-cols-12 overflow-hidden rounded-[2.5rem]"
        >
            @if($slide['announcement']['image_url'])
                <div class="col-span-5 min-h-[500px]">
                    <img
                        src="{{ $slide['announcement']['image_url'] }}"
                        alt="{{ $slide['announcement']['title'] }}"
                        class="h-full w-full object-cover"
                    >
                </div>

                <div
                    class="col-span-7 flex flex-col justify-center p-12"
                >
            @else
                <div
                    class="col-span-12 flex min-h-[500px] flex-col justify-center p-16 text-center"
                >
            @endif
                    <span
                        class="text-sm font-black uppercase tracking-[0.3em] text-sky-300"
                    >
                        Announcement
                    </span>

                    <h1
                        class="mt-5 text-5xl font-black leading-tight"
                    >
                        {{ $slide['announcement']['title'] }}
                    </h1>

                    <div
                        class="mt-7 whitespace-pre-line text-2xl leading-relaxed text-slate-300"
                    >
                        {{ $slide['announcement']['message'] }}
                    </div>
                </div>
        </div>
    @else
        @include('livewire.tv.slides.partials.empty', [
            'message' => 'No active announcement.',
        ])
    @endif
</div>