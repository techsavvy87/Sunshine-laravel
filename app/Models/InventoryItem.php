<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id', 'id');
    }

    public function attributes()
    {
        return $this->hasMany(InventoryAttribute::class, 'item_id', 'id');
    }
}
