<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    public function parent()
    {
        return $this->belongsTo(InventoryCategory::class, 'parent_id');
    }
}
