<?php

use Slim\Routing\RouteCollectorProxy;

use App\Zaptank\Middlewares\Account\checksIfAccountEmailIsNotVerified;
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
use App\Zaptank\Controllers\RankController;
use App\Zaptank\Controllers\InvoiceController;
use App\Zaptank\Controllers\TicketController;
use App\Zaptank\Controllers\Server\ServerController;
use App\Zaptank\Controllers\SurveyController;

$app->group('/', function(RouteCollectorProxy $group) {

    $group->post('account/phone/change', [AccountConfigController::class, 'changePhone']);
    $group->post('account/password/change', [AccountConfigController::class, 'changePassword']);
    $group->post('account/email/changenotverified', [AccountConfigController::class, 'changeEmailNotVerified']);
    $group->post('account/email/changerequest', [AccountConfigController::class, 'saveEmailChangeRequest']);
    $group->post('account/email/change', [AccountConfigController::class, 'changeEmail'])->add(new checkIfEmailChangeTokenIsValid);

    $group->get('character/check/{suv}', [CharacterController::class, 'checkIfCharacterWasCreated']);

    $group->group('character', function(RouteCollectorProxy $group) {

        $group->group('/config', function(RouteCollectorProxy $group) {
            $group->post('/changenick/{suv}', [CharacterConfigController::class, 'changenick'])->add(new ensureThatTheCharacterNewNicknameIsValid);
            $group->post('/clearbag/{suv}', [CharacterConfigController::class, 'clearbag']);
            $group->post('/giftcode/{suv}', [CharacterConfigController::class, 'redeemGiftCode'])->add(new checksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser);
        })->add(new checkIfCharacterWasNotCreated);

        $group->post('/create/{suv}', [CharacterController::class, 'new'])->add(new checkIfCharacterWasCreated)->add(new ensureThatTheCharacterNicknameIsValid);

    })->add(new checkIfServerSuvParameterIsInvalid);

    $group->group('invoice', function(RouteCollectorProxy $group){
        $group->post('/new/{suv}', [InvoiceController::class, 'new']);
    })->add(new checkIfServerSuvParameterIsInvalid);

    $group->group('ticket', function(RouteCollectorProxy $group) {
        $group->post('/new/{suv}', [TicketController::class, 'new'])->add(new checkIfCharacterWasNotCreated);
    })->add(new checkIfServerSuvParameterIsInvalid);
    
    $group->get('server/check/{suv}', [ServerController::class, 'CheckServerSuvToken']);

    $group->post('survey/save/{suv}', [SurveyController::class, 'store'])->add(new checkIfServerSuvParameterIsInvalid);

    $group->group('rank', function(RouteCollectorProxy $group) {
        $group->get('/temporada/list/{suv}', [RankController::class, 'listRankTemporada']);
        $group->get('/online/list/{suv}', [RankController::class, 'listRankOnline']);
        $group->get('/poder/list/{suv}', [RankController::class, 'listRankPoder']);
        $group->get('/pvp/list/{suv}', [RankController::class, 'listRankPvp']);
    })->add(new checkIfServerSuvParameterIsInvalid);

})->add(new ensureJwtAuthTokenIsValid);

$app->post('/account/new', [AccountController::class, 'new']);
$app->post('/auth/login', [AuthController::class, 'make']);