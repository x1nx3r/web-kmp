{{-- Status Badge Component --}}
@props(['status'])

@php
    $statusConfig = [
        'active' => [
            'class' => 'bg-green-100 text-green-800 border-green-200',
            'icon' => 'fas fa-check-circle',
            'label' => 'Aktif'
        ],
        'inactive' => [
            'class' => 'bg-red-100 text-red-800 border-red-200',
            'icon' => 'fas fa-times-circle',
            'label' => 'Tidak Aktif'
        ],
        'unknown' => [
            'class' => 'bg-gray-100 text-gray-800 border-gray-200',
            'icon' => 'fas fa-question-circle',
            'label' => 'Tidak Diketahui'
        ]
    ];
    $config = $statusConfig[$status ?? 'unknown'] ?? $statusConfig['unknown'];
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $config['class'] }}">
    <i class="{{ $config['icon'] }} mr-1.5 text-xs"></i>
    {{ $config['label'] }}
</span>