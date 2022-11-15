<?php

use App\Http\Controllers\API\Accounting\CAF\CafController;
use App\Http\Controllers\API\Accounting\CurrencySetupController;
use App\Http\Controllers\API\Accounting\PC\PcController;
use App\Http\Controllers\API\Accounting\RE\ReController;
use App\Http\Controllers\API\Accounting\RFP\RfpController;
use App\Http\Controllers\API\Accounting\RFP\RfpMainActualSignController;
use App\Http\Controllers\API\Accounting\RFP\RfpMainController;
use App\Http\Controllers\API\Accounting\RFP\RfpMainDetailController;
use App\Http\Controllers\API\Accounting\RFP\RFPMainLiquidationController;
use App\Http\Controllers\API\CylixPortal\CylixPortalController;
use App\Http\Controllers\API\General\ActualSignController;
use App\Http\Controllers\API\General\AttachmentController;
use App\Http\Controllers\API\General\BusinessListController;
use App\Http\Controllers\API\General\BusinessProjectController;
use App\Http\Controllers\API\General\CustomController;
use App\Http\Controllers\API\General\ProjectBusinessController;
use App\Http\Controllers\API\General\SetupProjectController;
use App\Http\Controllers\API\General\SystemReportingManagerController;
use App\Http\Controllers\API\HumanResource\DTR\DtrController;
use App\Http\Controllers\API\HumanResource\EmployeeController;
use App\Http\Controllers\API\HumanResource\ITF\ItfController;
use App\Http\Controllers\API\HumanResource\LAF\LafController;
use App\Http\Controllers\API\HumanResource\OT\OtController;
use App\Http\Controllers\API\SalesOrder\SofController;
use App\Http\Controllers\API\SupplyChain\CartController;
use App\Http\Controllers\API\SupplyChain\ScController;
use App\Http\Controllers\API\User\LoginController;
use App\Http\Controllers\API\User\RegisterController;
use App\Http\Controllers\API\User\ProfileController;
use App\Http\Controllers\API\Workflow\ApprovalController;
use App\Http\Controllers\API\Workflow\ApprovedController;
use App\Http\Controllers\API\Workflow\ClarificationController;
use App\Http\Controllers\API\Workflow\InProgressController;
use App\Http\Controllers\API\Workflow\InputController;
use App\Http\Controllers\API\Workflow\ListController;
use App\Http\Controllers\API\Workflow\ParticipantsController;
use App\Http\Controllers\API\Workflow\RejectedController;
use App\Http\Controllers\API\Workflow\WithdrawnController;
use App\Http\Controllers\ApiController;
use App\Models\General\SystemReportingManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Illuminate\Support\Facades\Storage;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Accounting - RFP
Route::resource('rfp', RfpController::class);
Route::resource('rfp-main', RfpMainController::class);
Route::resource('rfp-main-detail', RfpMainDetailController::class,  ['only' => ['show']]);
Route::resource('rfp-main-liquidation', RFPMainLiquidationController::class,  ['only' => ['show']]);
Route::resource('rfp-main-actualsign', RfpMainActualSignController::class,  ['only' => ['show']]);

// Accounting - RE
Route::post('saveRe', [ReController::class, 'saveRE']);
Route::get('getRE/{id}', [ReController::class, 'getRE']);

Route::post('saveDraftRe', [ReController::class, 'saveDraftRe']);
Route::get('getREbyUserID/{userid}', [ReController::class, 'getREbyUserID']);
Route::get('getReGeneralAttachmentsByReqid/{reqid}/{loggeduserID}', [ReController::class, 'getReGeneralAttachmentsByReqid']);

route::get('get-ReExpense/{id}', [ReController::class, 'getExpense']);
route::get('get-ReTranspo/{id}', [ReController::class, 'getTranspo']);

// Accounting - PC
Route::post('savePc' ,[PcController::class, 'savePc']);
route::get('get-PcMain/{id}', [PcController::class, 'getPcMain']);

route::get('get-PcExpense/{id}', [PcController::class, 'getExpense']);
route::get('get-PcTranspo/{id}', [PcController::class, 'getTranspo']);


// Accounting - CAF
Route::post('saveCaf', [CafController::class, 'saveCaf']);
Route::get('getCaf/{id}', [CafController::class, 'getCaf']);
Route::post('approveCafInput', [CafController::class, 'approveCafInput']);









Route::resource('general-currencies', CurrencySetupController::class);


// General
Route::resource('general-managers', SystemReportingManagerController::class);
Route::resource('general-projects', SetupProjectController::class);
Route::get('general-getprojects/{compId}',[SetupProjectController::class, 'getprojects']);


// To Modify
// Route::resource('general-businesses', BusinessListController::class);
    Route::get('general-businesses',[BusinessListController::class, 'index']);
    Route::get('general-businesses/{compId}',[BusinessListController::class, 'showByCompId']);




Route::resource('general-businesses-projects', BusinessProjectController::class);
Route::resource('general-project-business', ProjectBusinessController::class);


// Route::resource('general-actual-sign', ActualSignController::class);
Route::get('general-actual-sign/{processid}/{frmname}/{compid}',[ActualSignController::class, 'getActualsign']);

// Custom Reporting Manager
Route::get('/reporting-manager/{id}', [CustomController::class, 'getReportingManager']);
Route::get('/business-client/{id}', [CustomController::class, 'getClient']);
Route::get('/business-list/{id}', [CustomController::class, 'getBusinessList']);



Route::get('/get-expenseType', [CustomController::class, 'expenseType']);
Route::get('/get-currencyType', [CustomController::class, 'currencyType']);
Route::get('/get-transpoSetup', [CustomController::class, 'transpoSetup']);
Route::get('/get-employees/{companyId}', [CustomController::class, 'getEmployees']);
Route::get('/get-reports', [CustomController::class, 'getMediumOfReport']);
Route::get('/get-leavetype', [CustomController::class, 'getLeaveType']);









// Route::post('/create-rfp', [RfpMainController::class, 'saveRFP'])->name('save.rfp');

// Human Resource
Route::resource('hr-employees', EmployeeController::class);




    // Comments
    // getNotification
    Route::get('notifications/{id}/{frmname}', [CustomController::class, 'getNotification']);

    // Get Status
    Route::get('status/{id}/{frmname}/{companyId}', [CustomController::class, 'getStatus']);




    

// //Students praktis
// Route::prefix('/student')->group(function(){
//     Route::get('/list', [ApiController::class, 'index'])->name('index.list');
// });




// API
Route::post('oauth/token', [AccessTokenController::class, 'issueToken']);
// Route::get('/sync', [LoginController::class, 'sync']);
// Route::get('/getdata', [LoginController::class, 'getdata']);



Route::prefix('/user')->group(function(){
    Route::post('login',[LoginController::class, 'login']);
    Route::get('companies/{id}',[LoginController::class, 'showCompanies']);

    Route::middleware('auth:api')->post('logout',[LoginController::class, 'logout']);
    Route::middleware('auth:api')->get('profile',[LoginController::class, 'profile']);
});



// Workflow
Route::get('getParticipants/{loggedUserId}/{companyId}', [ParticipantsController::class, 'getParticipants']);
Route::get('getWithdrawn/{loggedUserId}/{companyId}', [WithdrawnController::class, 'getWithdrawn']);
Route::get('getInProgress/{loggedUserId}/{companyId}', [InProgressController::class, 'getInProgress']);
Route::get('getApprovals/{loggedUserId}/{companyId}', [ApprovalController::class, 'getApprovals']);
Route::get('getRejected/{loggedUserId}/{companyId}', [RejectedController::class, 'getRejected']);
Route::get('getApproved/{loggedUserId}/{companyId}', [ApprovedController::class, 'getApproved']);
Route::get('getInputs/{loggedUserId}/{companyId}', [InputController::class, 'getInputs']);
Route::get('getClarification/{loggedUserId}/{companyId}', [ClarificationController::class, 'getClarification']);
Route::get('getLists/{companyId}', [ListController::class, 'getLists']);



// get recipient of clarification
Route::get('getRecipient/{processId}/{loggedUserId}/{companyId}/{formName}', [CustomController::class, 'getRecipient']);



// get inprogress id 
Route::get('get-Inprogress/{id}/{companyId}/{formName}', [CustomController::class, 'getInprogressId']);


Route::get('getRfpAttachments/{id}/{forms}', [AttachmentController::class, 'getAttachments']);

Route::post('withdraw-request', [CustomController::class, 'withdrawnByIDRemarks']);
Route::post('reject-request', [CustomController::class, 'rejectedByIDRemarks']);
Route::post('approve-request', [CustomController::class, 'approvedByIDRemarks']);
Route::post('send-clarity', [CustomController::class, 'sendClarity']);
Route::post('reply-request', [CustomController::class, 'clarifyReplyBtnRemarks']);
Route::post('inputs-clarity', [CustomController::class, 'clarifyBtnInputs']);

Route::get('createfolder', [CustomController::class, 'createFolder']);





Route::post('rfpLiquidation', [ApprovalController::class, 'rfpLiquidation']);

Route::post('validateOT', [OtController::class, 'validateOT']);
Route::post('validateActualOT', [OtController::class, 'validateActualOT']);

Route::post('save-ot', [OtController::class, 'saveOT']);
Route::get('ot-main/{id}', [OtController::class, 'getOtMain']);
Route::get('actual-ot-main/{id}', [OtController::class, 'getActualOtMain']);
Route::post('approve-npu-init', [OtController::class, 'approveOTbyInit']);

Route::post('save-itf', [ItfController::class, 'saveItf']);
Route::get('itf-main/{id}', [ItfController::class, 'getItfMain']);
Route::get('itf-details/{id}', [ItfController::class, 'getItfDetails']);
Route::get('itf-actual-details/{id}', [ItfController::class, 'getItfActualDetails']);
Route::post('approve-itf-actualinput', [ItfController::class, 'approveActualItfInputs']);


Route::post('validate-laf-insert', [LafController::class, 'checkIfLafExist']);
Route::post('save-laf', [LafController::class, 'saveLaf']);
Route::get('get-laf-main/{id}/{companyId}', [LafController::class, 'getLafMain']);



// Sales Order
// Business List
Route::get('customer-name/{companyId}', [SofController::class, 'customerName']);
// Business List by id
Route::get('customer-data/{companyId}/{businessId}', [SofController::class, 'customerName']);
// get address by selected business list / customer name
Route::get('customer-address/{customerId}', [SofController::class, 'getCustomerAddress']);
// get customer contact info
Route::get('customer-contact/{customerId}', [SofController::class, 'getContacts']);
// get a list of project code
Route::get('customer-projectcode/{customerId}',[SofController::class, 'getSetupProject']);
// get a customer delegates
Route::get('customer-delegates/{customerId}',[SofController::class, 'getDelegates']);
// get all coordinators
Route::get('customer-cooridnators',[SofController::class, 'getCoordinators']);
// get all coordinators
Route::get('selected-cooridnator/{id}',[SofController::class, 'getSelectedCoordinator']);
// get system and document details
Route::get('customer-system-details',[SofController::class, 'getSystemDetails']);
Route::get('customer-selected-system-details/{id}',[SofController::class, 'getSelectedSystemDetails']);
Route::get('customer-document-details',[SofController::class, 'getDocumentDetails']);
Route::get('customer-selected-document-details/{id}',[SofController::class, 'getSelectedDocumentDetails']);


// Insert System and Document Details
Route::post('customer-details-insert', [SofController::class, 'insertSofModalDetails']);
// check if project code exist
Route::post('customer-projectcode-check', [SofController::class, 'checkIfProjectCodeExist']);
// check if project code exist with Soid
Route::post('customer-projectcode-check-soid', [SofController::class, 'checkIfProjectCodeExistSoid']);
// create sof request
Route::post('save-sof', [SofController::class, 'saveSOF']);
// get sales_order.sales_orders by id
Route::get('get-salesorder/{id}', [SofController::class, 'getSalesOrder']);
// get sales_order systems
Route::get('get-salesorder-system/{id}', [SofController::class, 'getSalesOrderSystem']);
// get sales_order documents
Route::get('get-salesorder-document/{id}', [SofController::class, 'getSalesOrderDocument']);



// Supply Chain
Route::post('get-materials', [ScController::class, 'getMaterials']);
Route::post('get-search-materials', [ScController::class, 'searchMaterials']);

Route::get('get-category', [ScController::class, 'getCategory']);
Route::get('get-uom', [ScController::class, 'getUom']);
Route::get('get-subcategory', [ScController::class, 'getSubCategory']);
Route::get('get-brand', [ScController::class, 'getBrand']);
Route::post('cart-purchase', [ScController::class, 'purchase']);
Route::get('get-attachments-by-soid/{soid}', [ScController::class, 'getAttachmentsBySoid']);

Route::get('get-mrf/{req_id}/{companyid}', [ScController::class, 'getMrf']);
Route::post('mrf-change-status', [ScController::class, 'mrfChangeStatus']);






Route::post('cart-store', [CartController::class, 'store']);
Route::get('cartone-show/{loggedUserId}/{companyId}', [CartController::class, 'showCartOne']);
Route::get('cart-show/{loggedUserId}/{companyId}/{status}', [CartController::class, 'showCart']);
Route::post('cart-destroy', [CartController::class, 'destroy']);
Route::post('cart-checkout', [CartController::class, 'checkout']);



// Cylix Portal
Route::get('get-cp-index', [CylixPortalController::class, 'index']);
Route::post('save-user-attendance', [CylixPortalController::class, 'saveUserAttendance']);
Route::get('get-manager/{id}', [DtrController::class, 'checkManager']); // only for erpweb to access attendance approval


// Attendance Export Filter
Route::post('post-filtered-attendance', [CylixPortalController::class, 'getFilteredEmployeeAttendance']);

// Attendance Approval
Route::get('get-dtr-logs/{id}', [DtrController::class, 'index']); // get dtr of all users under this manager
Route::post('post-dtr-logs-approve', [DtrController::class, 'approveSelected']); // approve multiple row
// Route::post('post-hr-emp', [DtrController::class, 'approve']); // approve only 1 row





// for previewing image or pdf
Route::get('getFile', [AttachmentController::class, 'getFile']);
Route::get('downloadFile', [AttachmentController::class, 'downloadFile']);

// Registration
Route::post('register', [RegisterController::class, 'register']);
Route::get('show-user', [RegisterController::class, 'showUsers']);

// Change Password
Route::post('changePassword', [ProfileController::class, 'changePassword']);
























