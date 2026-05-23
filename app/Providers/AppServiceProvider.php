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

                if ($activeWorksheetId) {
                    $activeWorksheet = $worksheets->firstWhere('id', $activeWorksheetId);
                }

                // Auto-assign first worksheet if none is active
                if (!$activeWorksheet && $worksheets->count() > 0) {
                    $activeWorksheet = $worksheets->first();
                    session(['active_worksheet_id' => $activeWorksheet->id]);
                    $activeWorksheetId = $activeWorksheet->id;
                }

                $view->with('userWorksheets', $worksheets);
                $view->with('activeWorksheet', $activeWorksheet);
                $view->with('activeWorksheetId', session('active_worksheet_id'));
            }
        });
    }
}
