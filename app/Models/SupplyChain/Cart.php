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

    protected $primaryKey = 'cart_id';

    public $timestamps = true;

    protected $fillable = [
        "cart_userid",
        "cart_companyid",
        "cart_group_detail_id",
        "cart_uom_id",
        "cart_uom_name",
        "cart_status",
        "cart_quantity",
    ];


    // userId
    // companyId
    // group_detail_id
    // uom_id
    // uom_name
    // status
    // quantity
}
