{{-- Status Badge Component --}}
@props(['status'])

@php
    $statusConfig = [
        'active' => [
            'class' => 'bg-green-100 text-green-800 border-green-200',
            'icon' => 'fas fa-check-circle',
            'label' => 'Aktif'
        ],
        'aktif' => [
            'class' => 'bg-green-100 text-green-800 border-green-200',
            'icon' => 'fas fa-check-circle',
            'label' => 'Aktif'
        ],
        'inactive' => [
            'class' => 'bg-red-100 text-red-800 border-red-200',
            'icon' => 'fas fa-times-circle',
            'label' => 'Tidak Aktif'
        ],
        'non_aktif' => [
            'class' => 'bg-red-100 text-red-800 border-red-200',
            'icon' => 'fas fa-times-circle',
            'label' => 'Non-Aktif'
        ],
        'pending' => [
            'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'icon' => 'fas fa-clock',
            'label' => 'Menunggu'
        ],
        'approved' => [
            'class' => 'bg-green-100 text-green-800 border-green-200',
            'icon' => 'fas fa-check',
            'label' => 'Disetujui'
        ],
        'rejected' => [
            'class' => 'bg-red-100 text-red-800 border-red-200',
            'icon' => 'fas fa-ban',
            'label' => 'Ditolak'
        ],
        'under_review' => [
            'class' => 'bg-blue-100 text-blue-800 border-blue-200',
            'icon' => 'fas fa-search',
            'label' => 'Direview'
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