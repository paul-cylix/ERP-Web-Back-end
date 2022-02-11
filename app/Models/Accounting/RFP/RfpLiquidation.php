<?php

namespace App\Models\Accounting\RFP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfpLiquidation extends Model
{
    use HasFactory;

    protected $table = 'accounting.rfp_liquidation';

    public function rfpMain(){
        return $this->belongsTo(RfpMain::class);
    }
}
