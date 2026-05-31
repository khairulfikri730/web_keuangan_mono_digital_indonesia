<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterExpenseCategory extends Model
{
    protected $fillable = ['worksheet_id', 'name', 'color'];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }
}
