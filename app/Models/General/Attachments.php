<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachments extends Model
{
    use HasFactory;

    protected $table = 'general.attachments';

    // protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        // 'id',
        'INITID',
        'REQID',
        'filename',
        'filepath',
        'fileExtension',
        'originalFilename',
        'newFilename',
        'formName',
        'fileDestination',
        'created_at',
        'created_at',
    ];

    public function rfpMain(){
        return $this->belongsTo(RfpMain::class);
    }
}
