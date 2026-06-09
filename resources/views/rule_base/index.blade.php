@extends('layouts.app')

@section('title', 'Rule Base SPK')
@section('page-title', 'Rule Base SPK')
@section('page-subtitle', 'Aturan Forward Chaining pemupukan kelapa sawit')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-2">
        <p class="text-xs text-slate-400"><span class="font-semibold text-slate-700">{{ $ruleBases->count() }}</span> rule terdaftar</p>
        <a href="{{ route('rule-base.create') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold rounded-lg sm:rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20 flex-shrink-0">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span class="hidden sm:inline">Tambah Rule</span>
            <span class="sm:hidden">Tambah</span>
        </a>
    </div>

    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        {{-- Desktop Table --}}
        <div class="overflow-x-auto hidden sm:block">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-slate-200 bg-slate-50">
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Parameter Kondisi</th>
                    <th class="px-4 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase">Urea</th>
                    <th class="px-4 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase">KCl</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ruleBases as $i => $rule)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3.5 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3.5">
                            @php $parts = explode('|', $rule->parameter_kondisi); @endphp
                            <div class="flex flex-wrap gap-1">
                                @foreach($parts as $part)
                                    <span class="px-2 py-0.5 rounded border border-slate-200 bg-slate-50 text-slate-600 text-xs font-mono">{{ $part }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center font-semibold text-amber-600">{{ $rule->takaran_urea }}</td>
                        <td class="px-4 py-3.5 text-center font-semibold text-cyan-600">{{ $rule->takaran_kcl }}</td>
                        <td class="px-4 py-3.5">
                            @php $sc = match($rule->status_pemupukan) {
                                'Segera Pupuk' => 'bg-rose-50 text-rose-700 border-rose-100',
                                'Pemupukan Normal' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'Tunda Pemupukan' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-slate-100 text-slate-600 border-slate-200'
                            }; @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ $sc }}">{{ $rule->status_pemupukan }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('rule-base.edit', $rule) }}"
                                   class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('rule-base.destroy', $rule) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this.closest('form'), 'rule ini')" class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada rule. <a href="{{ route('rule-base.create') }}" class="text-emerald-600 font-semibold hover:underline">Tambah sekarang</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card Layout --}}
        <div class="sm:hidden divide-y divide-slate-100">
            @forelse($ruleBases as $i => $rule)
            @php
                $sc = match($rule->status_pemupukan) {
                    'Segera Pupuk' => 'bg-rose-100 text-rose-800',
                    'Pemupukan Normal' => 'bg-emerald-100 text-emerald-800',
                    'Tunda Pemupukan' => 'bg-amber-100 text-amber-800',
                    default => 'bg-slate-100 text-slate-700'
                };
            @endphp
            <div class="p-3.5 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] text-slate-400 font-medium mb-1">Rule #{{ $i + 1 }}</p>
                        @php $parts = explode('|', $rule->parameter_kondisi); @endphp
                        <div class="flex flex-wrap gap-1">
                            @foreach($parts as $part)
                                <span class="px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50 text-slate-700 text-[10px] font-mono">{{ $part }}</span>
                            @endforeach
                        </div>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sc }} flex-shrink-0">{{ $rule->status_pemupukan }}</span>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-600">
                    <span class="text-amber-700 font-semibold">Urea: {{ $rule->takaran_urea }}</span>
                    <span class="text-cyan-700 font-semibold">KCl: {{ $rule->takaran_kcl }}</span>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('rule-base.edit', $rule) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-medium rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('rule-base.destroy', $rule) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDelete(this.closest('form'), 'rule ini')"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-50 text-red-700 border border-red-200 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-slate-400">
                Belum ada rule. <a href="{{ route('rule-base.create') }}" class="text-emerald-600 font-semibold hover:underline">Tambah sekarang</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
