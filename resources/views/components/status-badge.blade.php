{{--
    Status Badge Component (D6)
    Usage: @include('components.status-badge', ['status' => $status])
    Or:    @include('components.status-badge', ['status' => $status, 'size' => 'sm'])
--}}
@php
    $size = $size ?? 'md';
    $sizeClass = match($size) {
        'sm' => 'px-1.5 py-0.5 text-[9px]',
        'lg' => 'px-3 py-1 text-xs',
        default => 'px-2 py-0.5 text-[10px]',
    };

    $statusConfig = match($status ?? null) {
        'Darurat' => [
            'bg' => 'bg-red-50 text-red-700 ring-1 ring-red-200',
            'label' => 'Defisiensi Berat',
        ],
        'Segera' => [
            'bg' => 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
            'label' => 'Perlu Pupuk',
        ],
        'Normal' => [
            'bg' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
            'label' => 'Sehat',
        ],
        'Tunda' => [
            'bg' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
            'label' => 'Tunda Pupuk',
        ],
        default => [
            'bg' => 'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
            'label' => 'Belum Dicek',
        ],
    };
@endphp

<span class="inline-flex items-center rounded-full font-semibold {{ $sizeClass }} {{ $statusConfig['bg'] }}">
    {{ $statusConfig['label'] }}
</span>
