<?php

namespace App\Models\HumanResource\ITF;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItfMain extends Model
{
    use HasFactory;

    protected $table = 'humanresource.itinerary_main';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'draft_iden',
        'draft_reference',
        'reference',
        'request_date',
        'date_needed',
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
        'TITLEID',
        'webapp'
    ];


    public function setRequestDateAttribute($transdate){
        $this->attributes['request_date'] = date_format($transdate, 'Y-m-d');
    }

    public function setDateNeededAttribute($transdate){
        $this->attributes['date_needed'] = date_format($transdate, 'Y-m-d');
    }




}
