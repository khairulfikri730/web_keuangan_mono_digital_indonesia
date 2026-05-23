<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory, \App\Traits\BelongsToWorksheet;

    protected $fillable = [
        'worksheet_id', 'opened_by', 'assigned_users', 'closed_by', 'opening_cash', 'closing_cash',
        'expected_cash', 'discrepancy', 'cash_sales', 'bank_sales', 'cash_expenses', 'bank_expenses',
        'total_sales', 'total_transactions', 'status', 'notes',
        'opened_at', 'closed_at',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'discrepancy' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'bank_sales' => 'decimal:2',
        'cash_expenses' => 'decimal:2',
        'bank_expenses' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'assigned_users' => 'array',
    ];

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function cashflows()
    {
        return $this->hasMany(Cashflow::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public static function activeShift(): ?Shift
    {
        return self::withoutGlobalScopes()->where('status', 'open')->latest()->first();
    }

    /**
     * Get the active shift for a specific user.
     * If the user is assigned to the shift, or the shift has no assigned users, return it.
     */
    public static function activeShiftForUser(int $userId): ?Shift
    {
        return self::withoutGlobalScopes()->where('status', 'open')
            ->latest()
            ->get()
            ->first(function ($shift) use ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user && in_array($user->role, ['owner', 'admin'])) {
                    return true;
                }

                $assigned = $shift->assigned_users;
                // If assigned_users is empty/null, the shift is open to everyone
                if (empty($assigned)) {
                    return true;
                }
                return in_array($userId, $assigned);
            });
    }

    /**
     * Check if a user is assigned to this shift.
     */
    public function isUserAssigned(int $userId): bool
    {
        $user = \App\Models\User::find($userId);
        if ($user && in_array($user->role, ['owner', 'admin'])) {
            return true;
        }

        $assigned = $this->assigned_users;
        if (empty($assigned)) {
            return true; // no restriction = everyone can use it
        }
        return in_array($userId, $assigned);
    }

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }

    /**
     * Get the formatted duration of the shift.
     * 
     * @return string
     */
    public function getDuration(): string
    {
        $start = $this->opened_at;
        $end = $this->closed_at ?? now();
        
        if (!$start) return '-';

        $diff = $start->diff($end);
        
        $parts = [];
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' jam';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' menit';
        }
        
        if (empty($parts)) {
            return $diff->s . ' detik';
        }
        
        return implode(' ', $parts);
    }
}
