@props(['percentage'])

@php
$profitConfig = [
    'rugi' => [
        'class' => 'bg-red-100 text-red-800',
        'text' => 'Rugi'
    ],
    'rendah' => [
        'class' => 'bg-yellow-100 text-yellow-800',
        'text' => 'Rendah'
    ],
    'sedang' => [
        'class' => 'bg-blue-100 text-blue-800',
        'text' => 'Sedang'
    ],
    'tinggi' => [
        'class' => 'bg-green-100 text-green-800',
        'text' => 'Tinggi'
    ]
];

$category = 'rendah';
if ($percentage < 0) $category = 'rugi';
elseif ($percentage < 10) $category = 'rendah';
elseif ($percentage < 25) $category = 'sedang';
else $category = 'tinggi';

$config = $profitConfig[$category];
@endphp

<span class="px-2 py-1 {{ $config['class'] }} text-xs font-medium rounded-full">
    {{ $config['text'] }}
</span>