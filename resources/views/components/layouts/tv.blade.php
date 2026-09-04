<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="robots" content="noindex, nofollow">

    <meta property="og:title" content="Sales Dashboard" />
    <meta property="og:description" content="Hugo Plumbing Sales Dashboard" />
    <meta property="og:url" content=https://crm.hugoplumbingtx.com />
    <meta property="og:image" content="{{asset('assets/img/dashboard.jpg')}}" />
    <meta property="og:type" content="" />
    <meta property="og:site_name" content=Hugo Plumbing Dashboard/>
    <meta name="twitter:card" content="" />
    <meta name="twitter:title" content={title} />
    <meta name="twitter:description" content={description} />
    <meta name="twitter:image" content="{{asset('assets/img/dashboard.jpg')}}" />
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