<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleBase extends Model
{
    protected $fillable = [
        'parameter_kondisi',
        'takaran_urea',
        'takaran_kcl',
        'status_pemupukan',
    ];

    protected function casts(): array
    {
        return [
            'takaran_urea' => 'double',
            'takaran_kcl'  => 'double',
        ];
    }
}
