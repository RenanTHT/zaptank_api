<?php

use Slim\Routing\RouteCollectorProxy;

use App\Zaptank\Middlewares\Account\checksIfAccountEmailIsNotVerified;
use App\Zaptank\Middlewares\Account\checkIfTheUserDoesNotHaveAdministratorPermissions;
use App\Zaptank\Middlewares\Auth\ensureJwtAuthTokenIsValid;
use App\Zaptank\Middlewares\Email\checkIfEmailChangeTokenIsValid;
use App\Zaptank\Middlewares\Character\ensureThatTheCharacterNewNicknameIsValid;
use App\Zaptank\Middlewares\Character\ensureThatTheCharacterNicknameIsValid;
use App\Zaptank\Middlewares\Gift\checksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser;
use App\Zaptank\Middlewares\Server\checkIfServerSuvParameterIsInvalid;
use App\Zaptank\Middlewares\Character\checkIfCharacterWasNotCreated;
use App\Zaptank\Middlewares\Character\checkIfCharacterWasCreated;

use App\Zaptank\Controllers\PageController;
use App\Zaptank\Controllers\PlayController;
use App\Zaptank\Controllers\AuthController;
use App\Zaptank\Controllers\AdminController;
use App\Zaptank\Controllers\Account\AccountController;
use App\Zaptank\Controllers\Account\AccountConfigController;
use App\Zaptank\Controllers\Character\CharacterController;
use App\Zaptank\Controllers\Character\CharacterConfigController;
use App\Zaptank\Controllers\RankController;
use App\Zaptank\Controllers\InvoiceController;
use App\Zaptank\Controllers\RechargeController;
use App\Zaptank\Controllers\VirtualBagController;
use App\Zaptank\Controllers\TicketController;
use App\Zaptank\Controllers\VipController;
use App\Zaptank\Controllers\Payments\PaymentController;
use App\Zaptank\Controllers\Payments\PaymentNotificationController;
use App\Zaptank\Controllers\Server\ServerController;
use App\Zaptank\Controllers\SurveyController;
use App\Zaptank\Controllers\EmailController;

$app->post('/auth/login', [AuthController::class, 'make']);
$app->post('/account/new', [AccountController::class, 'new']);

$app->group('/', function(RouteCollectorProxy $group) {

    $group->group('', function(RouteCollectorProxy $group){

        $group->get('serverlist/{suv}', [PageController::class, 'serverList']);
        $group->get('play/{suv}', [playController::class, 'play']);

        $group->get('character/style/{suv}', [CharacterController::class, 'getStyle']);
        $group->post('character/config/changenick/{suv}', [CharacterConfigController::class, 'changenick'])->add(new ensureThatTheCharacterNewNicknameIsValid);
        $group->post('character/config/clearbag/{suv}', [CharacterConfigController::class, 'clearbag']);
        $group->post('character/config/giftcode/{suv}', [CharacterConfigController::class, 'redeemGiftCode'])->add(new checksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser);
    
        $group->get('backpack/list/{suv}', [VirtualBagController::class, 'listItems']);
        $group->post('backpack/item/send/{suv}', [VirtualBagController::class, 'sendItem']);
        
        $group->post('invoice/new/{suv}', [InvoiceController::class, 'new']);
        $group->get('invoice/details/{suv}', [InvoiceController::class, 'get']);
        $group->get('invoice/status/{suv}', [InvoiceController::class, 'status']);

        $group->get('chargeback/check/{suv}', [RechargeController::class, 'checkChargebackDetails']);
        $group->post('chargeback/collect/{suv}', [RechargeController::class, 'collectChargeback']);

        $group->post('ticket/new/{suv}', [TicketController::class, 'new']);
        $group->get('ticket/list/{suv}', [TicketController::class, 'list'])->add(new checkIfTheUserDoesNotHaveAdministratorPermissions);
        $group->post('ticket/close/{suv}', [TicketController::class, 'close'])->add(new checkIfTheUserDoesNotHaveAdministratorPermissions);

        $group->post('survey/save/{suv}', [SurveyController::class, 'store']);

        $group->get('rank/temporada/list/{suv}', [RankController::class, 'listRankTemporada']);
        $group->get('rank/online/list/{suv}', [RankController::class, 'listRankOnline']);
        $group->get('rank/poder/list/{suv}', [RankController::class, 'listRankPoder']);
        $group->get('rank/pvp/list/{suv}', [RankController::class, 'listRankPvp']);

        $group->post('payment/pix/{gateway}/new/{suv}', [PaymentController::class, 'newPixPayment']);

        $group->get('vip/list/{suv}', [VipController::class, 'list']);
        $group->get('vip/{id}/details/{suv}', [VipController::class, 'details']);

    })->add(new checkIfServerSuvParameterIsInvalid)->add(new checkIfCharacterWasNotCreated);   

    $group->post('account/phone/change', [AccountConfigController::class, 'changePhone']);
    $group->post('account/password/change', [AccountConfigController::class, 'changePassword']);
    $group->post('account/email/changenotverified', [AccountConfigController::class, 'changeEmailNotVerified']);
    $group->post('account/email/changerequest', [AccountConfigController::class, 'saveEmailChangeRequest']);
    $group->post('account/email/change', [AccountConfigController::class, 'changeEmail'])->add(new checkIfEmailChangeTokenIsValid);
    $group->get('account/email/verified/check', [AccountConfigController::class, 'checkIfEmailIsVerified']);
    $group->post('account/email/activation/request', [AccountConfigController::class, 'saveEmailActivationRequest']);

    $group->get('character/check/{suv}', [CharacterController::class, 'checkIfCharacterWasCreated']);
    $group->post('character/create/{suv}', [CharacterController::class, 'new'])->add(new checkIfCharacterWasCreated)->add(new ensureThatTheCharacterNicknameIsValid);
  
    $group->post('ticket/evaluate/{reference}', [TicketController::class, 'evaluateService']);
    $group->get('ticket/details/{reference}', [TicketController::class, 'getDetails']);
    
    $group->get('server/check/{suv}', [ServerController::class, 'CheckServerSuvToken']);
    $group->get('admin/check_permission', [AdminController::class, 'checkPermission']);

})->add(new ensureJwtAuthTokenIsValid);

$app->post('/payment/notification/picpay', [PaymentNotificationController::class, 'picpayNotification']);
$app->post('/payment/notification/openpix', [PaymentNotificationController::class, 'openpixNotification']);

$app->post('/account/email/activate/{token}', [AccountController::class, 'activateEmail']);
$app->get('/account/email/change/token/check/{token}', [AccountController::class, 'checkEmailChangeToken']);
$app->post('/account/password/recover/request', [AccountController::class, 'recoverPasswordRequest']);
$app->post('/account/password/recover', [AccountController::class, 'recoverPassword']);
$app->get('/account/password/recover/token/check/{token}', [AccountController::class, 'checkResetPasswordToken']);

$app->post('/unsubscribemaillist/{email_token}', [EmailController::class, 'unsubscribe']);