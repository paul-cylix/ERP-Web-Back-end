<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetupProject extends Model
{
    use HasFactory;

    protected $table = 'general.setup_project';

    protected $primaryKey = 'project_id';

    public function businessList(){
        return $this->belongsTo(BusinessList::class, 'ClientID', 'Business_Number');
    }
    
}
