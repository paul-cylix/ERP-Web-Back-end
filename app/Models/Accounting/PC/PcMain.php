<?php

namespace App\Models\Accounting\PC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PcMain extends Model
{
    use HasFactory;

    protected $table = 'accounting.petty_cash_request';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'REQREF',
        'DRAFT_NUM',
        'DRAFT_IDEN',
        'UID',
        'LNAME',
        'FNAME',
        'DEPARTMENT',
        'REPORTING_MANAGER',
        'TRANS_DATE',
        'REQUESTED_DATE',
        'AMT_DUE_FRM_EMP',
        'REQUESTED_AMT',
        'DEADLINE',
        'DESCRIPTION',
        'STATUS',
        'GUID',
        'PROJECT',
        'TS',
        'PAYEE',
        'ISRELEASED',
        'RELEASEDCASH',
        'PRJID',
        'CLIENT_NAME',
        'CLIENT_ID',
        'TITLEID',
        'webapp',
    ];
    
    public function setTransDateAttribute($transdate){
        $this->attributes['TRANS_DATE'] = date_format($transdate, 'Y-m-d');
    }

    public function setRequestedDateAttribute($requestedDate){
        $this->attributes['REQUESTED_DATE'] = date_format($requestedDate, 'Y-m-d');
    }

    public function setAmountDueAttribute($amount){
        $this->attributes['AMT_DUE_FRM_EMP'] = number_format($amount, 2, '.', '');
    }

    public function setAmountSpentAttribute($amount){
        $this->attributes['REQUESTED_AMT'] = number_format($amount, 2, '.', '');
    }

    public function setDeadlineAttribute($deadline){
        $this->attributes['DEADLINE'] = date_format($deadline, 'Y-m-d');
    }

}
