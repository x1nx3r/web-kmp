@props(['priority'])

@php
$priorityConfig = [
    'rendah' => [
        'class' => 'bg-gray-100 text-gray-800',
        'icon' => 'fa-arrow-down',
        'text' => 'Rendah'
    ],
    'sedang' => [
        'class' => 'bg-blue-100 text-blue-800',
        'icon' => 'fa-minus',
        'text' => 'Sedang'
    ],
    'tinggi' => [
        'class' => 'bg-orange-100 text-orange-800',
        'icon' => 'fa-arrow-up',
        'text' => 'Tinggi'
    ]
];

$config = $priorityConfig[$priority] ?? $priorityConfig['sedang'];
@endphp

<span class="px-2 py-1 {{ $config['class'] }} text-xs font-medium rounded-full">
    <i class="fas {{ $config['icon'] }} mr-1"></i>
    {{ $config['text'] }}
</span>
