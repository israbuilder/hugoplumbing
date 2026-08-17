<div
    class="tv-slide tv-slide-enter flex flex-col px-10 py-8"
>
    <div class="mb-7">
        <p
            class="text-sm font-black uppercase tracking-[0.28em] text-emerald-300"
        >
            Actividad del día
        </p>

        <h1 class="mt-2 text-4xl font-black">
            {{ $slide['title'] }}
        </h1>
    </div>

    <div class="mb-7 grid grid-cols-3 gap-5">
        <div class="tv-glass rounded-3xl p-6">
            <p
                class="text-sm uppercase tracking-wider text-slate-400"
            >
                Ventas de hoy
            </p>

            <p
                class="mt-3 text-5xl font-black text-emerald-300"
            >
                {{ Number::currency(
                    $slide['total_sales'],
                    in: $dashboard->currency
                ) }}
            </p>
        </div>

        <div class="tv-glass rounded-3xl p-6">
            <p
                class="text-sm uppercase tracking-wider text-slate-400"
            >
                Operaciones
            </p>

            <p class="mt-3 text-5xl font-black">
                {{ Number::format(
                    $slide['sales_count']
                ) }}
            </p>
        </div>

        <div class="tv-glass rounded-3xl p-6">
            <p
                class="text-sm uppercase tracking-wider text-slate-400"
            >
                Venta promedio
            </p>

            <p
                class="mt-3 text-5xl font-black text-sky-300"
            >
                {{ Number::currency(
                    $slide['average_sale'],
                    in: $dashboard->currency
                ) }}
            </p>
        </div>
    </div>

    <div
        class="tv-glass min-h-0 flex-1 overflow-hidden rounded-3xl"
    >
        <div
            class="grid grid-cols-12 border-b border-white/10 px-6 py-4 text-sm font-black uppercase tracking-wider text-slate-400"
        >
            <div class="col-span-4">Vendedor</div>
            <div class="col-span-4">Cliente</div>
            <div class="col-span-2">Hora</div>
            <div class="col-span-2 text-right">
                Importe
            </div>
        </div>

        @forelse($slide['recent_sales'] as $sale)
            <div
                class="grid grid-cols-12 items-center border-b border-white/5 px-6 py-4 last:border-0"
            >
                <div
                    class="col-span-4 text-lg font-black"
                >
                    {{ $sale['salesperson'] }}
                </div>

                <div
                    class="col-span-4 truncate text-slate-300"
                >
                    {{ $sale['customer'] }}
                </div>

                <div class="col-span-2 text-slate-400">
                    {{ $sale['sold_at'] }}
                </div>

                <div
                    class="col-span-2 text-right text-xl font-black text-emerald-300"
                >
                    {{ Number::currency(
                        $sale['amount'],
                        in: $sale['currency']
                    ) }}
                </div>
            </div>
        @empty
            <div
                class="grid h-52 place-items-center text-slate-400"
            >
                Todavía no hay ventas registradas hoy.
            </div>
        @endforelse
    </div>
</div>