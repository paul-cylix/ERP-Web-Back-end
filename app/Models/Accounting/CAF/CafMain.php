<?php

namespace App\Models\Accounting\CAF;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CafMain extends Model
{
    use HasFactory;

    protected $table = 'accounting.cash_advance_request';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'draft_iden',
        'draft_reference',
        'reference',
        'requested_date',
        'date_needed',
        'requested_amount',
        'date_from',
        'date_to',
        'employee_id',
        'employee_name',
        'installment_amount',
        'approved_amount',
        'purpose',
        'status',
        'IsReleased',
        'GUID',
        'ts',
        'UID',
        'fname',
        'lname',
        'position',
        'reporting_manager',
        'department',
        'commnets',
        'TITLEID',
        'webapp',
    ];
    
    public function setrequested_date($requested_date){
        $this->attributes['requested_date'] = date_format($requested_date, 'Y-m-d');
    }

    public function setdate_needed($date_needed){
        $this->attributes['date_needed'] = date_format($date_needed, 'Y-m-d');
    }

    public function setdate_from($date_from){
        $this->attributes['date_from'] = date_format($date_from, 'Y-m-d');
    }

    public function setdate_to($date_to){
        $this->attributes['date_to'] = date_format($date_to, 'Y-m-d');
    }

    public function setrequested_amount($requested_amount){
        $this->attributes['requested_amount'] = number_format($requested_amount, 2, '.', '');
    }

    public function setinstallment_amount($installment_amount){
        $this->attributes['installment_amount'] = number_format($installment_amount, 2, '.', '');
    }

    public function setapproved_amount($approved_amount){
        $this->attributes['approved_amount'] = number_format($approved_amount, 2, '.', '');
    }



}
