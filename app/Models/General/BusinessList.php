<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessList extends Model
{
    use HasFactory;

    protected $table = 'general.business_list';

    protected $primaryKey = 'Business_Number';

    public function setupProject(){
        return $this->hasMany(SetupProject::class, 'ClientID', 'Business_Number');
    }

}
