<?php

namespace App\Models\Accounting\PC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PcExpenseSetup extends Model
{
    use HasFactory;

    protected $table = 'accounting.petty_cash_expense_details';

    protected $primaryKey = 'id';

    public $timestamps = false;


    protected $fillable = [
        'PCID',
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
        'ISLIQUIDATED'
    ];

    public function setDateAttribute($date){
        $this->attributes['date_'] = date_format($date, 'Y-m-d');
    }

    public function setAmountAttribute($amount){
        $this->attributes['AMOUNT'] = number_format($amount, 2, '.', '');
    }
}
