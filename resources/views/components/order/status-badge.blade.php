@props(['status'])

@php
$statusConfig = [
    'draft' => [
        'class' => 'bg-blue-100 text-blue-800',
        'icon' => 'fa-file-alt',
        'text' => 'Draft'
    ],
    'dikonfirmasi' => [
        'class' => 'bg-yellow-100 text-yellow-800',
        'icon' => 'fa-check-circle',
        'text' => 'Dikonfirmasi'
    ],
    'diproses' => [
        'class' => 'bg-orange-100 text-orange-800',
        'icon' => 'fa-cogs',
        'text' => 'Diproses'
    ],
    'sebagian_dikirim' => [
        'class' => 'bg-purple-100 text-purple-800',
        'icon' => 'fa-shipping-fast',
        'text' => 'Sebagian Dikirim'
    ],
    'selesai' => [
        'class' => 'bg-green-100 text-green-800',
        'icon' => 'fa-check-double',
        'text' => 'Selesai'
    ],
    'dibatalkan' => [
        'class' => 'bg-red-100 text-red-800',
        'icon' => 'fa-times-circle',
        'text' => 'Dibatalkan'
    ]
];

$config = $statusConfig[$status] ?? $statusConfig['draft'];
@endphp

<span class="px-3 py-1 {{ $config['class'] }} text-xs font-medium rounded-full">
    <i class="fas {{ $config['icon'] }} mr-1"></i>
    {{ $config['text'] }}
</span>