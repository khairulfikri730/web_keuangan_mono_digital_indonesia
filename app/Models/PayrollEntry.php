<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollEntry extends Model
{
    public const COMPONENTS = [
        'photographer_fee' => 'Motret / Project',
        'overtime_fee' => 'Lembur',
        'bonus' => 'Bonus Tambahan',
        'deduction' => 'Potongan / Kasbon',
    ];

    protected $fillable = [
        'user_id', 'date', 'component', 'amount', 'note', 'source', 'reference_id'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function componentLabel(string $component): string
    {
        return self::COMPONENTS[$component] ?? $component;
    }

    /** Total rupiah per komponen untuk satu crew pada rentang tanggal. */
    public static function totals(int $userId, string $from, string $to): array
    {
        return self::where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->selectRaw('component, SUM(amount) as total')
            ->groupBy('component')
            ->pluck('total', 'component')
            ->map(fn ($total) => (int) $total)
            ->all();
    }
}
