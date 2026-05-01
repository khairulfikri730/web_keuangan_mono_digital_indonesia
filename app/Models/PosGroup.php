<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosGroup extends Model
{
    protected $fillable = ['name', 'color', 'position'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'pos_group_product')
                    ->withPivot('position')
                    ->orderBy('pos_group_product.position');
    }
}
