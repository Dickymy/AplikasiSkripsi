@extends('layouts.app')

@section('title', 'Edit Rule SPK')
@section('page-title', 'Edit Rule Base')
@section('page-subtitle', 'Perbarui aturan Forward Chaining')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        <form method="POST" action="{{ route('rule-base.update', $ruleBase) }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label for="parameter_kondisi" class="block text-sm font-medium text-slate-700 mb-2">Parameter Kondisi (IF) <span class="text-red-400">*</span></label>
                <input type="text" id="parameter_kondisi" name="parameter_kondisi" value="{{ old('parameter_kondisi', $ruleBase->parameter_kondisi) }}" required
                    class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 font-mono focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="takaran_urea" class="block text-sm font-medium text-slate-700 mb-2">Takaran Urea (kg/pokok)</label>
                    <input type="number" id="takaran_urea" name="takaran_urea" value="{{ old('takaran_urea', $ruleBase->takaran_urea) }}" step="0.01" min="0" required
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                </div>
                <div>
                    <label for="takaran_kcl" class="block text-sm font-medium text-slate-700 mb-2">Takaran KCl (kg/pokok)</label>
                    <input type="number" id="takaran_kcl" name="takaran_kcl" value="{{ old('takaran_kcl', $ruleBase->takaran_kcl) }}" step="0.01" min="0" required
                        class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                </div>
            </div>
            <div>
                <label for="status_pemupukan" class="block text-sm font-medium text-slate-700 mb-2">Status Pemupukan (THEN)</label>
                <select id="status_pemupukan" name="status_pemupukan" required
                    class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                    @foreach(['Segera Pupuk', 'Pemupukan Normal', 'Tunda Pemupukan'] as $s)
                        <option value="{{ $s }}" {{ old('status_pemupukan', $ruleBase->status_pemupukan) == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-emerald-600/20 hover:-translate-y-0.5">Perbarui Rule</button>
                <a href="{{ route('rule-base.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-sm font-medium rounded-xl transition-colors">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
