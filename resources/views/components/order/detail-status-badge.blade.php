@props(['status'])

@php
$statusConfig = [
    'menunggu' => [
        'class' => 'bg-gray-100 text-gray-800',
        'text' => 'Menunggu'
    ],
    'diproses' => [
        'class' => 'bg-orange-100 text-orange-800',
        'text' => 'Diproses'
    ],
    'sebagian_dikirim' => [
        'class' => 'bg-purple-100 text-purple-800',
        'text' => 'Sebagian Dikirim'
    ],
    'selesai' => [
        'class' => 'bg-green-100 text-green-800',
        'text' => 'Selesai'
    ]
];

$config = $statusConfig[$status] ?? $statusConfig['menunggu'];
@endphp

<span class="px-2 py-1 {{ $config['class'] }} text-xs font-medium rounded-full mt-1 inline-block">
    {{ $config['text'] }}
</span>