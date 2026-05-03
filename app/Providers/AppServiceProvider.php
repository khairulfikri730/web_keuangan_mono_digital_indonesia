<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                if ($user->isOwner()) {
                    $worksheets = \App\Models\Worksheet::all();
                } else {
                    $worksheets = $user->worksheets;
                }

                $activeWorksheetId = session('active_worksheet_id');
                $activeWorksheet = null;

                if ($activeWorksheetId && $activeWorksheetId !== 'all') {
                    $activeWorksheet = $worksheets->firstWhere('id', $activeWorksheetId);
                }

                // Auto-assign first worksheet for Kasir if none is active
                if (!$user->isOwner() && !$activeWorksheet && $worksheets->count() > 0) {
                    $activeWorksheet = $worksheets->first();
                    session(['active_worksheet_id' => $activeWorksheet->id]);
                }

                // If Kasir has no worksheets, this could be problematic, but we handle it in UI
                $view->with('userWorksheets', $worksheets);
                $view->with('activeWorksheet', $activeWorksheet);
                $view->with('activeWorksheetId', session('active_worksheet_id', $user->isOwner() ? 'all' : null));
            }
        });
    }
}
