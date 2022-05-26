<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    const CHECKED = 1;
    const UNCHECK = 0;

    const AVAILABLE = 1;


    public $timestamps = true;

    protected $fillable = [
        "userId",
        "companyId",
        "item_code",
        "uom_id",
        "uom_name",
        "status",
        "quantity",
    ];
}
