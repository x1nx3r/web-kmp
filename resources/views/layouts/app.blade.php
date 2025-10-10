<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'PT Kamil Maju Persada'))</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/image/logo/ptkmp-logo.png') }}?v=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/image/logo/ptkmp-logo.png') }}?v=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/image/logo/ptkmp-logo.png') }}?v=1.0">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#10B981">
    <meta name="msapplication-TileColor" content="#10B981">
    <meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Prevent Alpine.js components from flashing before initialization */
        [x-cloak] { display: none !important; }

        .card-placeholder {
            background: rgba(220, 20, 60, 0.1);
            border: 2px solid #14dc2f;
            border-radius: 12px;
        }
        .indonesia-map {
            background-image: url('data:image/svg+xml;charset=utf-8,...');
            background-size: cover;
            background-position: center;
        }



        /* Glass effect */
        .glass-effect {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* Smooth animations */
        .smooth-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div id="app" class="min-h-screen">
        {{-- Sidebar --}}
        @include('components.sidebar')

        {{-- Header --}}
        @include('components.header')

        {{-- Content --}}
        <main class="lg:ml-64 pt-16 lg:pt-24 p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    @livewireScripts
    
    {{-- Client Materials Management Functions --}}
    <script>
        // Material management functions
        function manageMaterials(klienId) {
            // Show modal or redirect to manage materials page
            console.log('Managing materials for client ID:', klienId);
            // TODO: Implement modal or redirect to materials management page
            alert('Fitur Manage Materials akan dikembangkan - Klien ID: ' + klienId);
        }

        function viewPriceHistory(klienId) {
            // Show price history modal
            console.log('Viewing price history for client ID:', klienId);
            // TODO: Implement price history modal
            alert('Fitur Price History akan dikembangkan - Klien ID: ' + klienId);
        }

        function addMaterialsForClient(klienId) {
            // Show add materials modal
            console.log('Adding materials for client ID:', klienId);
            // TODO: Implement add materials modal
            alert('Fitur Add Materials akan dikembangkan - Klien ID: ' + klienId);
        }

        function editMaterial(materialId) {
            // Show edit material modal
            console.log('Editing material ID:', materialId);
            // TODO: Implement edit material modal
            alert('Fitur Edit Material akan dikembangkan - Material ID: ' + materialId);
        }

        function viewMaterialHistory(materialId) {
            // Show material price history modal
            console.log('Viewing material history for ID:', materialId);
            // TODO: Implement material history modal
            alert('Fitur Material History akan dikembangkan - Material ID: ' + materialId);
        }
    </script>
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
