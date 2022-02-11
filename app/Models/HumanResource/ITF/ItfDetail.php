<?php

namespace App\Models\HumanResource\ITF;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItfDetail extends Model
{
    use HasFactory;

    protected $table = 'humanresource.itinerary_details';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'main_id',
        'client_id',
        'client_name',
        'time_start',
        'time_end',
        'actual_start',
        'actual_end',
        'purpose',
        'ts',
        'updated_by',
        'updated_ts',
    ];


    public function setRequestDateAttribute($transdate){
        $this->attributes['request_date'] = date_format($transdate, 'Y-m-d');
    }

    public function setDateNeededAttribute($transdate){
        $this->attributes['date_needed'] = date_format($transdate, 'Y-m-d');
    }

    public function setTimeStartAttribute($transdate){
        $this->attributes['time_start'] = date_format($transdate, 'Y-m-d H:i:s');
    }

    public function setTimeEndAttribute($transdate){
        $this->attributes['time_end'] = date_format($transdate, 'Y-m-d H:i:s');
    }

    public function setActualStartAttribute($transdate){
        $this->attributes['actual_start'] = date_format($transdate, 'Y-m-d H:i:s');
    }

    public function setActualEndAttribute($transdate){
        $this->attributes['actual_end'] = date_format($transdate, 'Y-m-d H:i:s');
    }

    public function setUpdatedTsAttribute($transdate){
        $this->attributes['updated_ts'] = date_format($transdate, 'Y-m-d H:i:s');
    }

}
