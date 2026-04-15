<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id', 'id');
    }
}
