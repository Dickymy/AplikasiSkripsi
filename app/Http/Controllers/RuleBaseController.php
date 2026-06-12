<?php

namespace App\Http\Controllers;

use App\Models\RuleBase;
use Illuminate\Http\Request;

class RuleBaseController extends Controller
{
    public function index()
    {
        $ruleBases = RuleBase::orderBy('parameter_kondisi')->get();
        return view('rule_base.index', compact('ruleBases'));
    }

    public function info()
    {
        return view('rule_base.info');
    }

    public function create()
    {
        return view('rule_base.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parameter_kondisi' => ['required', 'string', 'max:255'],
            'takaran_urea'      => ['required', 'numeric', 'min:0'],
            'takaran_kcl'       => ['required', 'numeric', 'min:0'],
            'status_pemupukan'  => ['required', 'string', 'max:100'],
        ]);

        RuleBase::create($validated);
        return redirect()->route('rule-base.index')->with('success', 'Rule berhasil ditambahkan.');
    }

    public function edit(RuleBase $ruleBase)
    {
        return view('rule_base.edit', compact('ruleBase'));
    }

    public function update(Request $request, RuleBase $ruleBase)
    {
        $validated = $request->validate([
            'parameter_kondisi' => ['required', 'string', 'max:255'],
            'takaran_urea'      => ['required', 'numeric', 'min:0'],
            'takaran_kcl'       => ['required', 'numeric', 'min:0'],
            'status_pemupukan'  => ['required', 'string', 'max:100'],
        ]);

        $ruleBase->update($validated);
        return redirect()->route('rule-base.index')->with('success', 'Rule berhasil diperbarui.');
    }

    public function destroy(RuleBase $ruleBase)
    {
        $ruleBase->delete();
        return redirect()->route('rule-base.index')->with('success', 'Rule berhasil dihapus.');
    }
}
