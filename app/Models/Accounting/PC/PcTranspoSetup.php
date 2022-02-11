<?php

namespace App\Models\Accounting\PC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PcTranspoSetup extends Model
{
    use HasFactory;

    protected $table = 'accounting.petty_cash_request_details';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'PCID',
        'PRJID',
        'payee_id',
        'PAYEE',
        'CLIENT_NAME',
        'DESTINATION_FRM',
        'DESTINATION_TO',
        'DESCRIPTION',
        'AMT_SPENT',
        'TITLEID',
        'MOT',
        'PROJECT',
        'GUID',
        'TS',
        'MAINID',
        'STATUS',
        'CLIENT_ID',
        'DEPT',
        'RELEASEDCASH',
        'date_',
        'ISLIQUIDATED'
    ];

    public function setDateAttribute($date){
        $this->attributes['date_'] = date_format($date, 'Y-m-d');
    }

    public function setAmountSpentAttribute($amount){
        $this->attributes['AMT_SPENT'] = number_format($amount, 2, '.', '');
    }
}

