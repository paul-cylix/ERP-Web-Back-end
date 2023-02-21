<?php

namespace App\Models\HumanResource\LAF;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LafMain extends Model
{
    protected $table = 'humanresource.leave_request';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'main_id',
        'draft_iden',
        'draft_reference',
        'reference',
        'request_date',
        'date_needed',
        'employee_id',
        'employee_name',
        'medium_of_report',
        'report_time',
        'leave_type',
        'leave_date',
        'leave_paytype',
        'leave_halfday',
        'num_days',
        'reason',
        'status',
        'UID',
        'fname',
        'lname',
        'position',
        'reporting_manager',
        'department',
        'ts',
        'GUID',
        'comments',
        'TITLEID',
        'webapp'

    ];


    public function setRequestDateAttribute($transdate){
        $this->attributes['request_date'] = date_format($transdate, 'Y-m-d');
    }

    public function setDateNeededAttribute($transdate){
        $this->attributes['date_needed'] = date_format($transdate, 'Y-m-d');
    }

    public function setReportTimeAttribute($transdate){
        $this->attributes['report_time'] = date_format($transdate, 'Y-m-d H:i:s');
    }

    public function setLeaveDateAttribute($transdate){
        $this->attributes['leave_date'] = date_format($transdate, 'Y-m-d');
    }


}
