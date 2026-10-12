<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COMPONENTS = [
        'photographer_fee' => 'photographer_fee_note',
        'overtime_fee' => 'overtime_fee_note',
        'bonus' => 'bonus_note',
        'deduction' => 'deduction_note',
    ];

    public function up(): void
    {
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('component'); // photographer_fee, overtime_fee, bonus, deduction
            $table->integer('amount')->default(0);
            $table->string('note')->nullable();
            $table->string('source')->default('manual'); // manual, overtime_auto, top_performer, migrated
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['component', 'date']);
        });

        $this->migrateLegacyValues();
    }

    private function migrateLegacyValues(): void
    {
        $now = now();

        foreach (DB::table('payrolls')->get() as $payroll) {
            $rows = [];

            foreach (self::COMPONENTS as $component => $noteColumn) {
                if ((int) $payroll->{$component} === 0) {
                    continue;
                }

                $rows[] = [
                    'user_id' => $payroll->user_id,
                    'date' => $payroll->period . '-01',
                    'component' => $component,
                    'amount' => (int) $payroll->{$component},
                    'note' => $payroll->{$noteColumn},
                    'source' => 'migrated',
                    'reference_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                DB::table('payroll_entries')->insert($rows);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
