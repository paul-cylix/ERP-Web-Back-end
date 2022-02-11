<?php

namespace App\Models\HumanResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

protected $table = 'humanresource.employees';

protected $primaryKey = 'SysPK_Empl';

public $timestamps = false;

protected $fillable = [
    'UserID_Empl',
    'Name_Empl',
    'Prefix',
    'FirstName_Empl',
    'MiddleName_Empl',
    'LastName_Empl',
    'Suffix',
    'Group_Empl',
    'Branch_Empl',
    'Department_Empl',
    'Position_Empl',
    'Hiring_Entity',
    'Bank_Name',
    'Account_No',
    'TimeStart_Empl',
    'TimeEnd_Empl',
    'Birthday',
    'Status_Empl',
    'Type_Empl',
    'salary_type',
    'isMinimumWage',
    'MonthlyRate',
    'DailyRate',
    'DoleRate_Empl',
    'Allowance',
    'COLA',
    'COLA_PayType',
    'OT_PayType',
    'Restday',
    'Weight',
    'Height',
    'FeetSize',
    'ShirtSize',
    'Gender',
    'CivilStatus',
    'BloodType',
    'schedule',
    'schedule_type',
    'Religion',
    'schedule_id',
    'CompanyID',
    'CompanyName',
    'Office_Branch_ID',
    'Office_Branch_Name',
    'DepartmentID',
    'DepartmentName',
    'PositionID',
    'PositionName',
    'SiteID',
    'SiteName',
    'Job_Level',
    'Rank',
    'DateHired',
    'DateRegularized',
    'DateContractEnd',
    'DateTerminated',
    'TerminationType',
    'SSS_No',
    'PHIC_No',
    'HDMF_No',
    'TIN_No',
    'CRN_No',
    'ModifiedID',
    'ModifiedDate',
    'updated',
    'imported_from_excel',
    'emp_prefix',
    'WithBio',
    'chkSSS',
    'chkHDMF',
    'chkPHIC',
    'chkWTAX',
    'Old_UserID_Empl',
];

public function user(){
    return $this->hasOne(User::class, 'employee_id' ,'SysPK_Empl');
}
}

