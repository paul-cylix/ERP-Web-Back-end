<?php

namespace App\Models\Accounting\RE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReTranspoSetup extends Model
{
    use HasFactory;

    protected $table = 'accounting.reimbursement_request_details';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'REID',
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
    ];

    public function setDateAttribute($date){
        $this->attributes['date_'] = date_format($date, 'Y-m-d');
    }

    public function setAmountSpentAttribute($amount){
        $this->attributes['AMT_SPENT'] = number_format($amount, 2, '.', '');
    }
}
