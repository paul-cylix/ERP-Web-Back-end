<?php

namespace App\Models\HumanResource\OT;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtMain extends Model
{
    use HasFactory;

    protected $table = 'humanresource.overtime_request';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'draft_iden',
        'draft_reference',
        'reference',
        'request_date',
        'overtime_date',
        'ot_in',
        'ot_out',
        'ot_totalhrs',
        'employee_id',
        'employee_name',
        'purpose',
        'status',
        'UID',
        'fname',
        'lname',
        'department',
        'reporting_manager',
        'position',
        'ts',
        'GUID',
        'comments',
        'ot_in_actual',
        'ot_out_actual',
        'ot_totalhrs_actual',
        'main_id',
        'remarks',
        'cust_id',
        'cust_name',
        'GUID_Attach',
        'TITLEID',
        'PRJID',
        'prev_status',
    ];


    public function setRequestDateAttribute($transdate){
        $this->attributes['request_date'] = date_format($transdate, 'Y-m-d');
    }

    public function setOvertimeDateAttribute($transdate){
        $this->attributes['overtime_date'] = date_format($transdate, 'Y-m-d');
    }

    public function setOtInAttribute($transdate){
        $this->attributes['ot_in'] = date_format($transdate, 'Y-m-d H:i:s');
    }

    public function setOtOutAttribute($transdate){
        $this->attributes['ot_out'] = date_format($transdate, 'Y-m-d H:i:s');
    }

    public function setOtInActualAttribute($transdate){
        $this->attributes['ot_in_actual'] = date_format($transdate, 'Y-m-d H:i:s');
    }

    public function setOtOutActualAttribute($transdate){
        $this->attributes['ot_out_actual'] = date_format($transdate, 'Y-m-d H:i:s');
    }


}
