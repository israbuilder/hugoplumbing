<x-filament-panels::page>

    {{-- MapLibre --}}
    @once
        <link
            href="https://unpkg.com/maplibre-gl@5.7.1/dist/maplibre-gl.css"
            rel="stylesheet"
        >

        <script src="https://unpkg.com/maplibre-gl@5.7.1/dist/maplibre-gl.js"></script>
    @endonce

    <style>
        .local-rank-grid-page {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .lrg-controls {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 1rem;
            padding: 1.25rem;
            border-radius: 1rem;
            background: rgb(255 255 255);
            border: 1px solid rgb(229 231 235);
        }

        .dark .lrg-controls {
            background: rgb(17 24 39);
            border-color: rgb(55 65 81);
        }

        .lrg-field {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .lrg-field label {
            font-size: .75rem;
            font-weight: 700;
            color: rgb(107 114 128);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .lrg-field select,
        .lrg-field input {
            width: 100%;
            border-radius: .65rem;
            border: 1px solid rgb(209 213 219);
            padding: .65rem .75rem;
            background: white;
        }

        .dark .lrg-field select,
        .dark .lrg-field input {
            background: rgb(31 41 55);
            border-color: rgb(75 85 99);
            color: white;
        }

        .lrg-actions {
            display: flex;
            align-items: flex-end;
            gap: .5rem;
        }

        .lrg-button {
            height: 42px;
            padding: 0 1rem;
            border-radius: .65rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
        }

        .lrg-button-primary {
            background: rgb(234 179 8);
            color: rgb(17 24 39);
        }

        .lrg-button-secondary {
            background: rgb(229 231 235);
            color: rgb(31 41 55);
        }

        .dark .lrg-button-secondary {
            background: rgb(55 65 81);
            color: white;
        }

        .lrg-stats {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 1rem;
        }

        .lrg-stat {
            background: white;
            border: 1px solid rgb(229 231 235);
            border-radius: 1rem;
            padding: 1rem 1.15rem;
        }

        .dark .lrg-stat {
            background: rgb(17 24 39);
            border-color: rgb(55 65 81);
        }

        .lrg-stat-label {
            color: rgb(107 114 128);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .lrg-stat-value {
            margin-top: .3rem;
            font-size: 1.55rem;
            font-weight: 800;
        }

        .lrg-map-wrapper {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid rgb(229 231 235);
            background: rgb(243 244 246);
        }

        #local-rank-map {
            width: 100%;
            height: 680px;
        }

        .lrg-marker {
            display: flex;
            justify-content: center;
            align-items: center;

            width: 42px;
            height: 42px;

            border-radius: 999px;

            border: 3px solid white;

            box-shadow:
                0 3px 8px rgba(0,0,0,.30);

            color: white;
            font-size: 14px;
            font-weight: 900;

            cursor: pointer;
        }

        .lrg-rank-1-3 {
            background: #16a34a;
        }

        .lrg-rank-4-10 {
            background: #eab308;
            color: #111827;
        }

        .lrg-rank-11-20 {
            background: #dc2626;
        }

        .lrg-rank-21-plus {
            background: #7f1d1d;
        }

        .lrg-rank-not-found {
            background: #111827;
        }

        .lrg-rank-processing {
            background: #6b7280;
        }

        .lrg-center {
            width: 48px;
            height: 48px;
            border: 4px solid #2563eb;
        }

        .lrg-popup {
            min-width: 260px;
        }

        .lrg-popup-title {
            font-weight: 800;
            font-size: 15px;
            margin-bottom: 5px;
        }

        .lrg-popup-rank {
            font-size: 24px;
            font-weight: 900;
        }

        .lrg-popup-meta {
            font-size: 12px;
            color: #6b7280;
            margin-top: 3px;
        }

        .lrg-competitors {
            margin-top: 12px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        .lrg-competitor {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 3px 0;
            font-size: 12px;
        }

        @media(max-width: 1100px) {
            .lrg-controls {
                grid-template-columns: repeat(2, 1fr);
            }

            .lrg-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(max-width: 700px) {
            .lrg-controls,
            .lrg-stats {
                grid-template-columns: 1fr;
            }

            #local-rank-map {
                height: 520px;
            }
        }
    </style>

    <div class="local-rank-grid-page">

        {{-- Controls --}}
        <div class="lrg-controls">

            <div class="lrg-field">
                <label>Business</label>

                <select wire:model.live="locationId">
                    @foreach($this->locations as $location)
                        <option value="{{ $location->id }}">
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lrg-field">
                <label>Keyword</label>

                <select wire:model.live="keywordId">
                    @foreach($this->keywords as $keyword)
                        <option value="{{ $keyword->id }}">
                            {{ $keyword->keyword }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lrg-field">
                <label>Grid</label>

                <select wire:model="gridSize">
                    <option value="3">3 × 3</option>
                    <option value="5">5 × 5</option>
                    <option value="7">7 × 7</option>
                    <option value="9">9 × 9</option>
                    <option value="11">11 × 11</option>
                    <option value="13">13 × 13</option>
                </select>
            </div>

            <div class="lrg-field">
                <label>Radius Miles</label>

                <input
                    type="number"
                    min=".5"
                    step=".5"
                    wire:model="radiusMiles"
                >
            </div>

            <div class="lrg-field">
                <label>Scan History</label>

                <div class="lrg-field">

                    <label>
                        Compare With
                    </label>

                    <select
                        wire:model.live="compareScanId"
                    >

                        <option value="">
                            No comparison
                        </option>

                        @foreach($this->scans as $scan)

                            @if(
                                (int) $scan->id !==
                                (int) $scanId
                            )

                                <option
                                    value="{{ $scan->id }}"
                                >
                                    #{{ $scan->id }}
                                    —
                                    {{ $scan->created_at->format(
                                        'M j, Y g:i A'
                                    ) }}
                                </option>

                            @endif

                        @endforeach

                    </select>

                </div>
                <select wire:model.live="scanId">
                    @foreach($this->scans as $scan)
                        <option value="{{ $scan->id }}">
                            #{{ $scan->id }}
                            —
                            {{ $scan->created_at->format('M j, Y g:i A') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lrg-actions">
                <button
                    type="button"
                    wire:click="runScan"
                    wire:loading.attr="disabled"
                    class="lrg-button lrg-button-primary"
                >
                    Run
                </button>

                <button
                    type="button"
                    wire:click="refreshScan"
                    class="lrg-button lrg-button-secondary"
                >
                    Refresh
                </button>
            </div>
        </div>

        @php
            $scan = $mapData['scan'] ?? null;
        @endphp

        {{-- Stats --}}
        @if($scan)
            <div class="lrg-stats">

                <div class="lrg-stat">
                    <div class="lrg-stat-label">
                        Average Rank
                    </div>

                    <div class="lrg-stat-value">
                        {{ $scan['average_rank'] !== null
                            ? number_format($scan['average_rank'], 1)
                            : '—'
                        }}
                    </div>
                </div>

                <div class="lrg-stat">
                    <div class="lrg-stat-label">
                        Top 3 Coverage
                    </div>

                    <div class="lrg-stat-value">
                        {{ number_format(
                            $scan['top_3_percentage'] ?? 0,
                            1
                        ) }}%
                    </div>
                </div>

                <div class="lrg-stat">
                    <div class="lrg-stat-label">
                        Top 10 Coverage
                    </div>

                    <div class="lrg-stat-value">
                        {{ number_format(
                            $scan['top_10_percentage'] ?? 0,
                            1
                        ) }}%
                    </div>
                </div>

                <div class="lrg-stat">
                    <div class="lrg-stat-label">
                        Visibility
                    </div>

                    <div class="lrg-stat-value">
                        {{ number_format(
                            $scan['visibility_score'] ?? 0,
                            1
                        ) }}%
                    </div>
                </div>

                <div class="lrg-stat">
                    <div class="lrg-stat-label">
                        Progress
                    </div>

                    <div class="lrg-stat-value">
                        {{ $scan['completed_points'] }}
                        /
                        {{ $scan['total_points'] }}
                    </div>
                </div>

                <div class="lrg-stat">
                    <div class="lrg-stat-label">
                        API Cost
                    </div>

                    <div class="lrg-stat-value">
                        ${{ number_format(
                            $scan['provider_cost'] ?? 0,
                            4
                        ) }}
                    </div>
                </div>

            </div>

            @if(
    !empty($comparisonData['current']) &&
    !empty($comparisonData['previous'])
)

    @php

        $current =
            $comparisonData['current'];

        $previous =
            $comparisonData['previous'];

    @endphp

    <div class="lrg-comparison">

        <h3>
            Scan Comparison
        </h3>

        <table
            style="
                width:100%;
                background:white;
                border-radius:12px;
                overflow:hidden;
            "
        >

            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Previous</th>
                    <th>Current</th>
                    <th>Change</th>
                </tr>
            </thead>

            <tbody>

                <tr>

                    <td>
                        Map Coverage
                    </td>

                    <td>
                        {{ number_format(
                            $previous['coverage'] ?? 0,
                            1
                        ) }}%
                    </td>

                    <td>
                        {{ number_format(
                            $current['coverage'] ?? 0,
                            1
                        ) }}%
                    </td>

                    <td>
                        {{
                            number_format(
                                ($current['coverage'] ?? 0)
                                -
                                ($previous['coverage'] ?? 0),
                                1
                            )
                        }}%
                    </td>

                </tr>

                <tr>

                    <td>
                        Top 3
                    </td>

                    <td>
                        {{ number_format(
                            $previous['top_3'] ?? 0,
                            1
                        ) }}%
                    </td>

                    <td>
                        {{ number_format(
                            $current['top_3'] ?? 0,
                            1
                        ) }}%
                    </td>

                    <td>
                        {{
                            number_format(
                                ($current['top_3'] ?? 0)
                                -
                                ($previous['top_3'] ?? 0),
                                1
                            )
                        }}%
                    </td>

                </tr>

                <tr>

                    <td>
                        Top 10
                    </td>

                    <td>
                        {{ number_format(
                            $previous['top_10'] ?? 0,
                            1
                        ) }}%
                    </td>

                    <td>
                        {{ number_format(
                            $current['top_10'] ?? 0,
                            1
                        ) }}%
                    </td>

                    <td>
                        {{
                            number_format(
                                ($current['top_10'] ?? 0)
                                -
                                ($previous['top_10'] ?? 0),
                                1
                            )
                        }}%
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

@endif
        @endif

        {{-- Map --}}
        <div class="lrg-map-wrapper">
            <div
                id="local-rank-map"
                wire:ignore
            ></div>
        </div>

        @if(count($competitors))

    <div
        style="
            background:white;
            border:1px solid #e5e7eb;
            border-radius:16px;
            padding:20px;
        "
    >

        <h2
            style="
                font-size:20px;
                font-weight:800;
                margin-bottom:16px;
            "
        >
            Top Local Competitors
        </h2>

        <div
            style="
                overflow-x:auto;
            "
        >

            <table
                style="
                    width:100%;
                    border-collapse:collapse;
                "
            >

                <thead>

                    <tr>

                        <th
                            style="
                                text-align:left;
                                padding:10px;
                            "
                        >
                            Competitor
                        </th>

                        <th>
                            Appearances
                        </th>

                        <th>
                            Avg Rank
                        </th>

                        <th>
                            Best
                        </th>

                        <th>
                            Above Us
                        </th>

                        <th>
                            Rating
                        </th>

                        <th>
                            Reviews
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @foreach(
                        array_slice(
                            $competitors,
                            0,
                            20
                        )
                        as $competitor
                    )

                        <tr
                            style="
                                border-top:
                                    1px solid #e5e7eb;
                            "
                        >

                            <td
                                style="
                                    padding:10px;
                                    font-weight:700;
                                "
                            >
                                {{
                                    $competitor['name']
                                }}
                            </td>

                            <td
                                style="
                                    text-align:center;
                                "
                            >
                                {{
                                    $competitor[
                                        'appearances'
                                    ]
                                }}
                            </td>

                            <td
                                style="
                                    text-align:center;
                                "
                            >
                                {{
                                    $competitor[
                                        'average_rank'
                                    ]
                                    ?? '—'
                                }}
                            </td>

                            <td
                                style="
                                    text-align:center;
                                "
                            >
                                #{{

                                    $competitor[
                                        'best_rank'
                                    ]
                                    ?? '—'

                                }}
                            </td>

                            <td
                                style="
                                    text-align:center;
                                "
                            >
                                {{
                                    $competitor[
                                        'above_us'
                                    ]
                                }}
                            </td>

                            <td
                                style="
                                    text-align:center;
                                "
                            >
                                {{
                                    $competitor[
                                        'rating'
                                    ]
                                    ?? '—'
                                }}
                            </td>

                            <td
                                style="
                                    text-align:center;
                                "
                            >
                                {{
                                    number_format(
                                        $competitor[
                                            'reviews'
                                        ]
                                        ?? 0
                                    )
                                }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@endif

    </div>

    <script>
        document.addEventListener('livewire:init', () => {

            let map = null;
            let markers = [];

            const initialData = @js($mapData);

            function createMap(data) {

                const centerLat =
                    data?.scan?.center_latitude
                    ?? data?.location?.latitude
                    ?? 29.7604;

                const centerLng =
                    data?.scan?.center_longitude
                    ?? data?.location?.longitude
                    ?? -95.3698;

                if (!map) {
                    map = new maplibregl.Map({
                        container: 'local-rank-map',

                        center: [
                            centerLng,
                            centerLat
                        ],

                        zoom: 10,

                        style: {
                            version: 8,

                            sources: {
                                osm: {
                                    type: 'raster',

                                    tiles: [
                                        'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
                                    ],

                                    tileSize: 256,

                                    attribution:
                                        '© OpenStreetMap contributors'
                                }
                            },

                            layers: [
                                {
                                    id: 'osm',
                                    type: 'raster',
                                    source: 'osm'
                                }
                            ]
                        }
                    });

                    map.addControl(
                        new maplibregl.NavigationControl(),
                        'top-right'
                    );
                }

                renderPoints(data);
            }

            function clearMarkers() {
                markers.forEach(marker => marker.remove());
                markers = [];
            }

            function rankClass(point) {
                if (point.status !== 'completed') {
                    return 'lrg-rank-processing';
                }

                if (!point.found || !point.rank) {
                    return 'lrg-rank-not-found';
                }

                if (point.rank <= 3) {
                    return 'lrg-rank-1-3';
                }

                if (point.rank <= 10) {
                    return 'lrg-rank-4-10';
                }

                if (point.rank <= 20) {
                    return 'lrg-rank-11-20';
                }

                return 'lrg-rank-21-plus';
            }

            function markerText(point) {
                if (point.status !== 'completed') {
                    return '…';
                }

                if (!point.found || !point.rank) {
                    return '20+';
                }

                return point.rank;
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            }

            function popupHtml(point) {

                let html = `
                    <div class="lrg-popup">

                        <div class="lrg-popup-title">
                            ${escapeHtml(
                                point.business_name
                                ?? 'Business not found'
                            )}
                        </div>

                        <div class="lrg-popup-rank">
                            ${
                                point.rank
                                    ? '#' + point.rank
                                    : 'Not found'
                            }
                        </div>

                        <div class="lrg-popup-meta">
                            Distance:
                            ${Number(
                                point.distance_miles ?? 0
                            ).toFixed(2)}
                            miles
                        </div>

                        <div class="lrg-popup-meta">
                            ${Number(point.latitude).toFixed(6)},
                            ${Number(point.longitude).toFixed(6)}
                        </div>
                `;

                if (
                    Array.isArray(point.competitors) &&
                    point.competitors.length
                ) {
                    html += `
                        <div class="lrg-competitors">

                            <strong>
                                Google Maps Results
                            </strong>
                    `;

                    point.competitors
                        .slice(0, 10)
                        .forEach(item => {

                            html += `
                                <div class="lrg-competitor">

                                    <span>
                                        #${item.rank ?? '—'}
                                        ${escapeHtml(item.name)}
                                    </span>

                                    <span>
                                        ${
                                            item.rating
                                                ? '★ ' + item.rating
                                                : ''
                                        }
                                    </span>

                                </div>
                            `;
                        });

                    html += `</div>`;
                }

                html += `</div>`;

                return html;
            }

            function renderPoints(data) {

                clearMarkers();

                if (
                    !data ||
                    !Array.isArray(data.points) ||
                    !data.points.length
                ) {
                    return;
                }

                const bounds = new maplibregl.LngLatBounds();

                data.points.forEach(point => {

                    const element =
                        document.createElement('div');

                    element.className =
                        `lrg-marker ${rankClass(point)}`;

                    if (point.is_center) {
                        element.classList.add(
                            'lrg-center'
                        );
                    }

                    element.textContent =
                        markerText(point);

                    const popup =
                        new maplibregl.Popup({
                            offset: 28,
                            maxWidth: '380px'
                        })
                        .setHTML(
                            popupHtml(point)
                        );

                    const marker =
                        new maplibregl.Marker({
                            element,
                            anchor: 'center'
                        })
                        .setLngLat([
                            Number(point.longitude),
                            Number(point.latitude)
                        ])
                        .setPopup(popup)
                        .addTo(map);

                    markers.push(marker);

                    bounds.extend([
                        Number(point.longitude),
                        Number(point.latitude)
                    ]);
                });

                if (!bounds.isEmpty()) {
                    map.fitBounds(bounds, {
                        padding: 80,
                        maxZoom: 14,
                        duration: 700
                    });
                }
            }

            createMap(initialData);

            Livewire.on(
                'local-rank-map-updated',
                event => {

                    const data =
                        event?.data ??
                        event?.[0]?.data ??
                        event?.[0] ??
                        event;

                    createMap(data);
                }
            );

        });
    </script>

</x-filament-panels::page>