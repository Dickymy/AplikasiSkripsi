{{--
    Searchable Select Component
    Usage: @include('components.searchable-select', [
        'name' => 'anggota_id',
        'label' => 'Pemilik Lahan',
        'placeholder' => 'Cari anggota...',
        'options' => $anggotas, // collection with id & display field
        'displayField' => 'nama',
        'selected' => old('anggota_id', $blokLahan->anggota_id ?? ''),
        'required' => true,
        'error' => $errors->first('anggota_id'),
        'helpText' => 'Belum ada? <a href="..." class="text-emerald-600 font-medium hover:underline">Tambah →</a>',
    ])
--}}

@php
    $uid = 'ss-' . $name . '-' . uniqid();
@endphp

<div class="relative" id="{{ $uid }}-wrapper">
    <label class="block text-sm font-medium text-slate-700 mb-2">
        {{ $label }} @if($required ?? false)<span class="text-red-400">*</span>@endif
    </label>

    {{-- Hidden input yang dikirim ke server --}}
    <input type="hidden" name="{{ $name }}" id="{{ $uid }}-value" value="{{ $selected ?? '' }}">

    {{-- Display input (searchable) --}}
    <div class="relative">
        <input type="text" id="{{ $uid }}-search"
            placeholder="{{ $placeholder ?? 'Cari...' }}"
            autocomplete="off"
            class="w-full px-4 py-3 pr-10 bg-white border {{ ($error ?? false) ? 'border-red-400' : 'border-slate-300' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
            value="{{ $selected ? ($options->firstWhere('id', $selected)?->{$displayField} ?? '') : '' }}">
        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </div>

    {{-- Dropdown results --}}
    <div id="{{ $uid }}-dropdown" class="absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden hidden max-h-48 overflow-y-auto">
        @foreach($options as $opt)
        <div class="ss-option px-4 py-2.5 hover:bg-emerald-50 cursor-pointer text-sm text-slate-700 border-b border-slate-50 last:border-0 transition-colors"
             data-value="{{ $opt->id }}" data-label="{{ $opt->{$displayField} }}">
            {{ $opt->{$displayField} }}
        </div>
        @endforeach
        <div class="ss-empty px-4 py-3 text-sm text-slate-400 text-center hidden">Tidak ditemukan</div>
    </div>

    @if($error ?? false)
        <p class="mt-1.5 text-xs text-red-500">{{ $error }}</p>
    @endif
    @if($helpText ?? false)
        <p class="mt-1.5 text-xs text-slate-400">{!! $helpText !!}</p>
    @endif
</div>

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id$="-wrapper"]').forEach(function(wrapper) {
        var id = wrapper.id.replace('-wrapper', '');
        var searchEl = document.getElementById(id + '-search');
        var valueEl = document.getElementById(id + '-value');
        var dropdown = document.getElementById(id + '-dropdown');
        if (!searchEl || !valueEl || !dropdown) return;

        var options = dropdown.querySelectorAll('.ss-option');
        var emptyEl = dropdown.querySelector('.ss-empty');

        searchEl.addEventListener('focus', function() { dropdown.classList.remove('hidden'); filterOptions(); });

        var debounceTimer;
        searchEl.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(filterOptions, 200);
        });

        function filterOptions() {
            var q = searchEl.value.toLowerCase().trim();
            var visible = 0;
            options.forEach(function(opt) {
                var show = opt.dataset.label.toLowerCase().includes(q);
                opt.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (emptyEl) emptyEl.style.display = visible === 0 ? '' : 'none';
        }

        options.forEach(function(opt) {
            opt.addEventListener('click', function() {
                valueEl.value = this.dataset.value;
                searchEl.value = this.dataset.label;
                dropdown.classList.add('hidden');
            });
        });

        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) dropdown.classList.add('hidden');
        });
    });
});
</script>
@endPushOnce
