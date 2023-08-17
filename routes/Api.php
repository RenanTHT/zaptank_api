<?php

use Slim\Routing\RouteCollectorProxy;

use App\Zaptank\Middlewares\Auth\ensureJwtAuthTokenIsValid;
use App\Zaptank\Middlewares\Email\checkIfEmailChangeTokenIsValid;
use App\Zaptank\Middlewares\Character\ensureThatTheCharacterNewNicknameIsValid;
use App\Zaptank\Middlewares\Character\ensureThatTheCharacterNicknameIsValid;
use App\Zaptank\Middlewares\Gift\checksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser;
use App\Zaptank\Middlewares\Server\checkIfServerSuvParameterIsInvalid;
use App\Zaptank\Middlewares\Character\checkIfCharacterWasNotCreated;
use App\Zaptank\Middlewares\Character\checkIfCharacterWasCreated;

use App\Zaptank\Controllers\AuthController;
use App\Zaptank\Controllers\Account\AccountController;
use App\Zaptank\Controllers\Account\AccountConfigController;
use App\Zaptank\Controllers\Character\CharacterController;
use App\Zaptank\Controllers\Character\CharacterConfigController;
use App\Zaptank\Controllers\Server\ServerController;

$app->group('/', function(RouteCollectorProxy $group) {

    $group->post('account/phone/change', [AccountConfigController::class, 'changePhone']);
    $group->post('account/password/change', [AccountConfigController::class, 'changePassword']);
    $group->post('account/email/changenotverified', [AccountConfigController::class, 'changeEmailNotVerified']);
    $group->post('account/email/changerequest', [AccountConfigController::class, 'saveEmailChangeRequest']);
    $group->post('account/email/change', [AccountConfigController::class, 'changeEmail'])->add(new checkIfEmailChangeTokenIsValid);
    
    $group->post('character/create/{suv}', [CharacterController::class, 'new'])
    ->add(new checkIfServerSuvParameterIsInvalid)
    ->add(new checkIfCharacterWasCreated)
    ->add(new ensureThatTheCharacterNicknameIsValid);

    $group->group('character/config', function(RouteCollectorProxy $group) {
        $group->post('/changenick/{suv}', [CharacterConfigController::class, 'changenick'])->add(new ensureThatTheCharacterNewNicknameIsValid);
        $group->post('/clearbag/{suv}', [CharacterConfigController::class, 'clearbag']);
        $group->post('/giftcode/{suv}', [CharacterConfigController::class, 'redeemGiftCode'])->add(new checksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser);
    })
    ->add(new checkIfServerSuvParameterIsInvalid)
    ->add(new checkIfCharacterWasNotCreated);

    $group->get('character/check', [CharacterController::class, 'checkIfCharacterWasCreated']);
    
    $group->get('server/check/{suv}', [ServerController::class, 'CheckServerSuvToken']);
})->add(new ensureJwtAuthTokenIsValid);

$app->post('/account/new', [AccountController::class, 'new']);
$app->post('/auth/login', [AuthController::class, 'make']);