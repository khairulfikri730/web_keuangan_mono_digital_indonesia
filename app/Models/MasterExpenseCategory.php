<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterExpenseCategory extends Model
{
    protected $fillable = ['worksheet_id', 'name', 'color', 'is_pos_hidden'];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }
}
