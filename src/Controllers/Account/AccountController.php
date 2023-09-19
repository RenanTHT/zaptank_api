<?php

namespace App\Zaptank\Controllers\Account;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;
use App\Zaptank\Models\Email as EmailModel;

use App\Zaptank\Services\Token;
use App\Zaptank\Services\Email;

use App\Zaptank\Helpers\RequestLimiter;
use App\Zaptank\Helpers\IpAdress;

class AccountController {

    public function new(Request $request, Response $response, array $args) :Response {

        $email             = $_POST['email'];
        $password          = strtoupper(md5($_POST['password']));
        $phone             = $_POST['phone'];
        $ReferenceLocation = $_POST['ReferenceLocation'];
    
        $account = new Account;
        
        if(empty(trim($email)) || empty(trim($_POST['password'])) || empty(trim($phone))) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);
        } else if(!empty($account->selectByEmail($email))) {
            $body = json_encode([
                'success' => false,
                'message' => 'e-mail em uso.',
                'status_code' => 'email_exists'
            ]);
        } else if(!empty($account->selectByPhone($phone))) {
            $body = json_encode([
                'success' => false,
                'message' => 'telefone em uso.',
                'status_code' => 'phone_exists'
            ]);
        } else {

            $extension = strrchr($email, '@');
            $whitelist = array('gmail.com', 'outlook.com', 'hotmail.com', 'hotmail.com.br', 'yahoo.com','yahoo.com.br', 'live.com', 'icloud.com', 'outlook.pt', 'outlook.com.br', 'icloud.com.br', 'qq.com');
            $ex = explode('@', $email);
                    
            if(empty($extension) || !in_array(array_pop($ex), $whitelist)) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'E-mail inválido.',
                    'status_code' => 'invalid_email'
                ]);
            } else {
                $account->create($email, $password, $phone, $ReferenceLocation);
                $user = $account->selectByEmail($email);
                
                if(is_array($user) && !empty($user)) {
    
                    $uid = $user['UserId'];
                    $phone = $user['Telefone'];
    
                    $token = new Token;
    
                    $jwt_authentication_hash = $token->generateAuthenticationToken($payload = [
                        'sub' => $uid,
                        'email' => $email,
                        'phone' => $phone
                    ]);
    
                    $activation_token = md5(time());
                    $EncMail = $email;
    
                    $emailModel = new EmailModel;
                    $emailModel->insertEmailActivationToken($uid, $activation_token, $data = date('Y-m-d H:i:s'));
    
                    $emailService = new Email;
                    
                    $email_sent = $emailService->send(
                        $subject = 'Atendimento ZapTank - Ative sua conta', 
                        $body = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style><table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $email . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"> <span class="m_-5336645264442155576mail__title" style=""> <h1><font color="#ffffff">Esse e-mail é para que você tenha acesso total à sua conta ZapTank a ativação é bem rápida! Clique no botão para ativar sua conta.</font></h1> </span> </td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/active_account?token=' . $activation_token . '" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/active_account?token=' . $activation_token . '">Ativar conta</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"><p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 44px;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody></table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>', 
                        $altBody = 'Atendimento ZapTank - Ative sua conta', 
                        $email
                    );
    
                    $body = json_encode([
                        'success' => true,
                        'email_sent' => $email_sent,
                        'message' => 'usuário foi cadastrado com êxito.',
                        'status_code' => 'user_registered',
                        'data' => [
                            'userId' => $uid,
                            'email' => $email,
                            'password' => $password,
                            'phone' => $phone,
                            'jwt_authentication_hash' => $jwt_authentication_hash
                        ]
                    ]);
                } else {
                    $body = json_encode([
                        'success' => false,
                        'message' => 'houve um erro interno, o usuário não foi cadastrado.',
                        'status_code' => 'internal_error'
                    ]);
                }

            }            
        }

        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function recoverPassword(Request $request, Response $response, array $args) :Response {

        $requestLimiter = new RequestLimiter(IpAdress::getUserIp());
        $remainingTime = $requestLimiter->limitPasswordRecoveryRequests();

        if($remainingTime > 0) {
            $body = json_encode([
                'success' => false,
                'message' => "Aguarde {$remainingTime} segundos antes de fazer outra solicitação.",
                'status_code' => 'many_requests'
            ]); 

            $response->getBody()->write($body);
            return $response;
        }
        
        $requestLimiter->addRequestInformation(IpAdress::getUserIp(), 'last_password_recovery_time');

        if(empty(trim($_POST['email']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);
    
            $response->getBody()->write($body);
            return $response;
        }

        $body = json_encode([
            'success' => true,
            'message' => 'Email enviado com sucesso, caso não encontre nenhum email verifique o SPAM.',
            'status_code' => 'password_recover_email_sent'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}