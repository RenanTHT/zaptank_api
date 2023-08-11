<?php

use Slim\Routing\RouteCollectorProxy;

use App\Zaptank\Middlewares\Auth\ensureJwtAuthTokenIsValid;
use App\Zaptank\Middlewares\Email\checkIfEmailChangeTokenIsValid;
use App\Zaptank\Middlewares\Character\ensureThatTheCharacterNewNicknameIsValid;
use App\Zaptank\Middlewares\Gift\ChecksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser;

use App\Zaptank\Controllers\AuthController;
use App\Zaptank\Controllers\Account\AccountController;
use App\Zaptank\Controllers\Account\AccountConfigController;
use App\Zaptank\Controllers\Character\CharacterController;
use App\Zaptank\Controllers\Character\CharacterAccountConfigController;
use App\Zaptank\Controllers\Server\ServerController;

$app->group('', function(RouteCollectorProxy $group) {

    $group->post('/account/phone/change', [AccountConfigController::class, 'changePhone']);
    $group->post('/account/password/change', [AccountConfigController::class, 'changePassword']);
    $group->post('/account/email/changenotverified', [AccountConfigController::class, 'changeEmailNotVerified']);
    $group->post('/account/email/changerequest', [AccountConfigController::class, 'saveEmailChangeRequest']);
    $group->post('/account/email/change', [AccountConfigController::class, 'changeEmail'])->add(new checkIfEmailChangeTokenIsValid);
  
    // Criar grupo de rota /character/config e adicionar middlewares para verificar se usuário possui personagem e se parâmetro suv é valido
    $group->post('/character/config/changenick', [CharacterAccountConfigController::class, 'changenick'])->add(new ensureThatTheCharacterNewNicknameIsValid);
    $group->post('/character/config/clearbag', [CharacterAccountConfigController::class, 'clearbag']);
    $group->post('/character/config/giftcode', [CharacterAccountConfigController::class, 'redeemGiftCode'])->add(new ChecksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser);

    // checa se personagem foi criado
    $group->get('/character/check', [CharacterController::class, 'checkIfCharacterWasCreated']);
    
    $group->get('/server/check/{suv}', [ServerController::class, 'CheckServerSuvToken']);
})->add(new ensureJwtAuthTokenIsValid);

$app->post('/account/new', [AccountController::class, 'new']);
$app->post('/auth/login', [AuthController::class, 'make']);    