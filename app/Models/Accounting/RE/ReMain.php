<?php

namespace App\Models\Accounting\RE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReMain extends Model
{
    use HasFactory;

    protected $table = 'accounting.reimbursement_request';

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
        'PAYEEID',
        'PAYEE',
        'TRANS_DATE',
        'REQUESTED_DATE',
        'AMT_DUE_TO_COMP',
        'AMT_DUE_FRM_EMP',
        'TOTAL_AMT_SPENT',
        'DEADLINE',
        'DESCRIPTION',
        'STATUS',
        'GUID',
        'PROJECT',
        'TS',
        'DESTINATION_FROM',
        'DESTINATION_TO',
        'ISRELEASED',
        'PRJID',
        'RELEASEDCASH',
        'CLIENT_NAME',
        'TITLEID',
        'MAINID',
        'CLIENTID',
        'webapp',
    ];
    
    

    public function setAmountDueAttribute($amount){
        $this->attributes['AMT_DUE_FRM_EMP'] = number_format($amount, 2, '.', '');
    }

    public function setAmountSpentAttribute($amount){
        $this->attributes['TOTAL_AMT_SPENT'] = number_format($amount, 2, '.', '');
    }

    public function setTransDateAttribute($transdate){
        $this->attributes['TRANS_DATE'] = date_format($transdate, 'Y-m-d');
    }

    public function setDateNeededAttribute($deadline){
        $this->attributes['Deadline'] = date_format($deadline, 'Y-m-d');
    }








}


