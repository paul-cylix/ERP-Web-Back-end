<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'general.notifications';

    protected $primaryKey = 'ID';

    public $timestamps = false;


    protected $fillable = [
        'ParentID',
        'levels',
        'FRM_NAME',
        'PROCESSID',
        'SENDERID',
        'RECEIVERID',
        'MESSAGE',
        'TS',
        'SETTLED',
        'ACTUALID',
        'SENDTOACTUALID',
        'UserFullName',
    ];

    
}
