<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    const UNCHECK = 1;
    const CHECKED = 2;
    const PURCHASED = 3;

    const AVAILABLE = 1;


    public $timestamps = true;

    protected $fillable = [
        "userId",
        "companyId",
        "group_detail_id",
        "uom_id",
        "uom_name",
        "status",
        "quantity",
    ];
}
