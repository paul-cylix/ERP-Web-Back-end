<?php

namespace App\Models\Accounting\RE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReExpenseSetup extends Model
{
    use HasFactory;

    protected $table = 'accounting.reimbursement_expense_details';

    protected $primaryKey = 'id';

    public $timestamps = false;


    protected $fillable = [
        'REID',
        'payee_id',
        'PAYEE',
        'CLIENT_NAME',
        'TITLEID',
        'PRJID',
        'PROJECT',
        'DESCRIPTION',
        'AMOUNT',
        'GUID',
        'TS',
        'MAINID',
        'STATUS',
        'CLIENT_ID',
        'EXPENSE_TYPE',
        'DEPT',
        'RELEASEDCASH',
        'date_',
    ];

    public function setDateAttribute($date){
        $this->attributes['date_'] = date_format($date, 'Y-m-d');
    }

    public function setAmountAttribute($amount){
        $this->attributes['AMOUNT'] = number_format($amount, 2, '.', '');
    }
}
