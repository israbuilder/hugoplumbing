<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="robots" content="noindex, nofollow">

    <meta property="og:title" content="Hugo Plumbing" />
    <meta property="og:description" content="Hugo Plumbing Sales Dashboard" />
    <meta property="og:url" content="https://crm.hugoplumbingtx.com/tv/race/dashboard" />

    <meta property="og:image" content="{{ asset('assets/img/dashboard.jpg') }}" />
    <meta property="og:image:secure_url" content="{{ asset('assets/img/dashboard.jpg') }}" />
    <meta property="og:image:type" content="image/jpg" />
    <meta property="og:image:width" content="1024" />
    <meta property="og:image:height" content="576" />

    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Hugo Plumbing Dashboard" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Dashboard" />
    <meta name="twitter:description" content="Sales Dashboard" />
    <meta name="twitter:image" content="{{ asset('assets/img/dashboard.jpg') }}" />
    <title>
        {{ $title ?? 'Sales TV Dashboard' }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @livewireStyles
</head>

<body class="h-full overflow-hidden bg-slate-950 text-white antialiased">
    {{ $slot }}

    @livewireScripts
</body>
</html>