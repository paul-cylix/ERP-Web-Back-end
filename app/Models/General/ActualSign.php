<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualSign extends Model
{
    use HasFactory;

    protected $table = 'general.actual_sign';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'PROCESSID',
        'USER_GRP_IND',
        'FRM_NAME',
        'TaskTitle',
        'NS',
        'FRM_CLASS',
        'REMARKS',
        'STATUS',
        'UID_SIGN',
        'TS',
        'DUEDATE',
        'SIGNDATETIME',
        'ORDERS',
        'REFERENCE',
        'PODATE',
        'PONUM',
        'DATE',
        'INITID',
        'FNAME',
        'LNAME',
        'MI',
        'DEPARTMENT',
        'RM_ID',
        'REPORTING_MANAGER',
        'PROJECTID',
        'PROJECT',
        'COMPID',
        'COMPANY',
        'TYPE',
        'CLIENTID',
        'CLIENTNAME',
        'VENDORID',
        'VENDORNAME',
        'Max_approverCount',
        'GUID_GROUPS',
        'DoneApproving',
        'WebpageLink',
        'ApprovedRemarks',
        'Payee',
        'CurrentSender',
        'CurrentReceiver',
        'NOTIFICATIONID',
        'SENDTOID',
        'NRN',
        'imported_from_excel',
        'Amount',
        'webapp',

    ];

    public function rfpMain(){
        return $this->belongsTo(RfpMain::class);
    }

    public function setdueDateAttribute($dueDate){
        $this->attributes['DUEDATE'] = date_format($dueDate, 'Y-m-d');
    }

    public function setpoDateAttribute($poDate){
        $this->attributes['PODATE'] = date_format($poDate, 'Y-m-d');
    }

    public function setDateAttribute($date){
        $this->attributes['DATE'] = date_format($date, 'Y-m-d');
    }

}
