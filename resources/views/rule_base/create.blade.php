@extends('layouts.app')

@section('title', 'Tambah Rule SPK')
@section('page-title', 'Tambah Rule Base')
@section('page-subtitle', 'Tambah aturan Forward Chaining baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 space-y-5">
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-100 text-blue-800 text-xs">
            <p class="font-semibold mb-1">Format Parameter Kondisi:</p>
            <code class="text-blue-700 px-1.5 py-0.5 rounded bg-blue-100/50 font-mono">KATEGORI_UMUR|JENIS_TANAH|TOPOGRAFI</code>
            <p class="mt-1 text-blue-600">Contoh: <code class="font-mono">Remaja|Berpasir|Datar 0-15°</code></p>
        </div>

        <form method="POST" action="{{ route('rule-base.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="parameter_kondisi" class="block text-sm font-medium text-slate-700 mb-2">Parameter Kondisi (IF) <span class="text-red-400">*</span></label>
                <input type="text" id="parameter_kondisi" name="parameter_kondisi" value="{{ old('parameter_kondisi') }}" required
                    placeholder="Remaja|Berpasir|Datar 0-15°"
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('parameter_kondisi') ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-emerald-500' }} rounded-xl text-sm text-slate-800 font-mono placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors">
                @error('parameter_kondisi') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="takaran_urea" class="block text-sm font-medium text-slate-700 mb-2">Takaran Urea (kg/pokok) <span class="text-red-400">*</span></label>
                    <input type="number" id="takaran_urea" name="takaran_urea" value="{{ old('takaran_urea') }}" step="0.01" min="0" required placeholder="1.50"
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('takaran_urea') ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors">
                    @error('takaran_urea') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="takaran_kcl" class="block text-sm font-medium text-slate-700 mb-2">Takaran KCl (kg/pokok) <span class="text-red-400">*</span></label>
                    <input type="number" id="takaran_kcl" name="takaran_kcl" value="{{ old('takaran_kcl') }}" step="0.01" min="0" required placeholder="1.00"
                        class="w-full px-4 py-3 bg-white border {{ $errors->has('takaran_kcl') ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-emerald-500' }} rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors">
                    @error('takaran_kcl') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="status_pemupukan" class="block text-sm font-medium text-slate-700 mb-2">Status Pemupukan (THEN) <span class="text-red-400">*</span></label>
                <select id="status_pemupukan" name="status_pemupukan" required
                    class="w-full px-4 py-3 bg-white border {{ $errors->has('status_pemupukan') ? 'border-red-400 focus:ring-red-400' : 'border-slate-300 focus:ring-emerald-500' }} rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors">
                    <option value="">-- Pilih Status --</option>
                    <option value="Segera Pupuk" {{ old('status_pemupukan') == 'Segera Pupuk' ? 'selected' : '' }}>Segera Pupuk</option>
                    <option value="Pemupukan Normal" {{ old('status_pemupukan') == 'Pemupukan Normal' ? 'selected' : '' }}>Pemupukan Normal</option>
                    <option value="Tunda Pemupukan" {{ old('status_pemupukan') == 'Tunda Pemupukan' ? 'selected' : '' }}>Tunda Pemupukan</option>
                </select>
                @error('status_pemupukan') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5">Simpan Rule</button>
                <a href="{{ route('rule-base.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-sm font-medium rounded-xl transition-colors">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
