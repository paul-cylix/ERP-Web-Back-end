<?php

namespace App\Models\Accounting\RFP;

use App\Models\General\ActualSign;
use App\Models\General\Attachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfpMain extends Model
{
    use HasFactory;

    protected $table = 'accounting.request_for_payment';

    protected $primaryKey = 'ID';

    public $timestamps = false;


    protected $fillable = [
        'DRAFT_IDEN',
        'DRAFTNUM',
        'DATE',
        'REQREF',
        'PROJECT',
        'Deadline',
        'AMOUNT',
        'STATUS',
        'UID',
        'FNAME',
        'LNAME',
        'DEPARTMENT',
        'REPORTING_MANAGER',
        'POSITION',
        'TS',
        'GUID',
        'COMMENTS',
        'ISRELEASED',
        'TITLEID',
        'webapp',
    ];

    public function setAmountAttribute($amount){
        $this->attributes['AMOUNT'] = number_format($amount, 2, '.', '');
    }

    public function setDeadlineAttribute($deadline){
        $this->attributes['Deadline'] = date_format($deadline, 'Y-m-d');
    }

    public function rfpDetail(){
        return $this->hasOne(RfpDetail::class, 'RFPID' ,'ID');
    }

    public function rfpLiquidation(){
        return $this->hasMany(RfpLiquidation::class, 'RFPID', 'ID');
    }

    public function actualSign(){
        return $this->hasMany(ActualSign::class, 'PROCESSID', 'ID');
    }

    public function attachments()
    {
        return $this->hasMany(Attachments::class, 'REQID', 'ID');
    }
}
