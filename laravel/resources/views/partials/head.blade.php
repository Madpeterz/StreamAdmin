<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name', 'StreamAdmin') }}</title>

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
