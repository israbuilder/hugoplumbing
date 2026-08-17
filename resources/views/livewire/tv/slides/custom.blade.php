<div
    class="tv-slide tv-slide-enter grid place-items-center px-10 py-8"
>
    <div
        class="tv-glass w-full max-w-5xl rounded-[2rem] p-12 text-center"
    >
        <h1 class="text-5xl font-black">
            {{ $slide['title'] }}
        </h1>

        @if($slide['subtitle'])
            <p class="mt-4 text-2xl text-slate-400">
                {{ $slide['subtitle'] }}
            </p>
        @endif

        @if($slide['content'])
            <div
                class="mt-8 whitespace-pre-line text-xl leading-relaxed text-slate-300"
            >
                {{ $slide['content'] }}
            </div>
        @endif
    </div>
</div>