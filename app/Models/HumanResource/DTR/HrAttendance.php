<?php

namespace App\Models\HumanResource\DTR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrAttendance extends Model
{
    use HasFactory;

    protected $table = 'humanresource.hr_emp_attendance';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];

    
}
