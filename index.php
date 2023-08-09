<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

use App\Zaptank\Controllers\AuthController;
use App\Zaptank\Controllers\Account\AccountController;
use App\Zaptank\Controllers\Account\ConfigController;
use App\Zaptank\Controllers\Character\CharacterConfigController;
use App\Zaptank\Controllers\Server\ServerController;

use App\Zaptank\Middlewares\Auth\ensureJwtAuthTokenIsValid;
use App\Zaptank\Middlewares\Email\checkIfEmailChangeTokenIsValid;
use App\Zaptank\Middlewares\Character\ensureThatTheCharacterNewNicknameIsValid;
use App\Zaptank\Middlewares\Gift\ChecksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

// $app->addErrorMiddleware(false, true, true);

$app->setBasePath('/zaptank_api');

$app->add(function ($request, $handler) use ($app) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->group('', function(RouteCollectorProxy $group) {

    $group->post('/account/phone/change', [ConfigController::class, 'changePhone']);
    $group->post('/account/password/change', [ConfigController::class, 'changePassword']);
    $group->post('/account/email/changenotverified', [ConfigController::class, 'changeEmailNotVerified']);
    $group->post('/account/email/changerequest', [ConfigController::class, 'saveEmailChangeRequest']);
    $group->post('/account/email/change', [ConfigController::class, 'changeEmail'])->add(new checkIfEmailChangeTokenIsValid);

    // Criar grupo de rota /character/config e adicionar middlewares para verificar se usuário possui personagem e se parâmetro suv é valido
    $group->post('/character/config/changenick', [CharacterConfigController::class, 'changenick'])->add(new ensureThatTheCharacterNewNicknameIsValid);
    $group->post('/character/config/clearbag', [CharacterConfigController::class, 'clearbag']);
    $group->post('/character/config/giftcode', [CharacterConfigController::class, 'redeemGiftCode'])->add(new ChecksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser);
})->add(new ensureJwtAuthTokenIsValid);

$app->get('/server/check/{suv}', [ServerController::class, 'CheckServerSuvToken']);

$app->post('/account/new', [AccountController::class, 'new']);
$app->post('/auth/login', [AuthController::class, 'make']);    

$app->run();