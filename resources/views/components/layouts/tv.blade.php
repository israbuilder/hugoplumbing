<!DOCTYPE html>
<html
    lang="es"
    class="h-full bg-slate-950"
>
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="robots"
        content="noindex, nofollow"
    >

    <title>
        {{ $title ?? 'Sales TV Dashboard' }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @livewireStyles
</head>

<body
    class="h-full overflow-hidden bg-slate-950 text-white antialiased"
>
    {{ $slot }}

    @livewireScripts
</body>
</html>