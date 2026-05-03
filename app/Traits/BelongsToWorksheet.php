<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToWorksheet
{
    protected static function bootBelongsToWorksheet()
    {
        static::addGlobalScope('worksheet', function (Builder $builder) {
            $activeWorksheetId = session('active_worksheet_id');
            
            if ($activeWorksheetId && $activeWorksheetId !== 'all') {
                $builder->where('worksheet_id', $activeWorksheetId);
            } elseif (!auth()->check() || !auth()->user()->isOwner()) {
                // For Kasir without a specific active worksheet selected, scope to all their allowed worksheets
                if (auth()->check() && !auth()->user()->isOwner()) {
                    $allowedIds = auth()->user()->worksheets->pluck('id');
                    $builder->whereIn('worksheet_id', $allowedIds);
                }
            }
            // If Owner and 'all', don't apply scope
        });

        static::creating(function ($model) {
            if (!$model->worksheet_id) {
                $activeWorksheetId = session('active_worksheet_id');
                if ($activeWorksheetId && $activeWorksheetId !== 'all') {
                    $model->worksheet_id = $activeWorksheetId;
                } elseif (auth()->check() && !auth()->user()->isOwner() && auth()->user()->worksheets->count() > 0) {
                    $model->worksheet_id = auth()->user()->worksheets->first()->id;
                }
            }
        });
    }
}
