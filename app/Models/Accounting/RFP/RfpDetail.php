<?php

namespace App\Models\Accounting\RFP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfpDetail extends Model
{
    use HasFactory;

    protected $table = 'accounting.rfp_details';
    protected $primaryKey = 'ID'; // latest update // i add this primary key
    public $timestamps = false;

    protected $fillable = [
        'RFPID',
        'PROJECTID',
        'ClientID',
        'CLIENTNAME',
        'TITLEID',
        'PAYEEID',
        'MAINID',
        'PROJECT',
        'DATENEEDED',
        'PAYEE',
        'MOP',
        'PURPOSED',
        'DESCRIPTION',
        'CURRENCY',
        'currency_id',
        'AMOUNT',
        'STATUS',
        'GUID',
        'RELEASEDCASH',
        'TS',
    ];


    public function rfpMain(){
        return $this->belongsTo(RfpMain::class);
    }

    public function setDateNeededAttribute($dateNeeded){
        $this->attributes['DATENEEDED'] = date_format($dateNeeded, 'Y-m-d');
    }
}
