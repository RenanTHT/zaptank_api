<?php

namespace App\Zaptank\Controllers\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Models\Account;
use App\Zaptank\Models\Email as EmailModel;

use App\Zaptank\Database;
use App\Zaptank\Services\Token;
use App\Zaptank\Services\Email;

use App\Zaptank\Helpers\RequestLimiter;
use App\Zaptank\Helpers\IpAdress;
use App\Zaptank\Helpers\Date;
use App\Zaptank\Helpers\Time;
use App\Zaptank\Helpers\Cryptography;
use App\Zaptank\Helpers\Validator;

class AccountConfigController {
    
    public function changePhone(Request $request, Response $response, array $args) : Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);

        $uid = $payload['sub'];

        if(!isset($_POST['phone']) || empty(trim($_POST['phone']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);

            $response->getBody()->write($body);
            return $response;
        } 
        
        $phone = $_POST['phone'];

        if(strlen($phone) < 19) {
            $body = json_encode([
                'success' => false,
                'message' => 'Por favor, preencha o número de telefone corretamente.',
                'status_code' => 'invalid_number'                
            ]);
        } else {
            $account = new Account;

            if(!empty($account->selectByPhone($phone))) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'O telefone está sendo usado.',
                    'status_code' => 'phone_exists'                
                ]);                
            } else {
                $account->updatePhone($uid, $phone);                

                $body = json_encode([
                    'success' => true,
                    'message' => 'Telefone alterado com sucesso!',
                    'status_code' => 'phone_changed'                
                ]);                 
            }
        }

        $response->getBody()->write($body);
        return $response;
    }


    public function changePassword(Request $request, Response $response, array $args) : Response {

        if(!isset($_POST['oldpass']) || empty(trim($_POST['oldpass']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não informou a senha antiga.',
                'status_code' => 'empty_oldpass'
            ]);
        } else if(!isset($_POST['newpass']) || empty(trim($_POST['newpass']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não informou a nova senha.',
                'status_code' => 'empty_oldpass'
            ]);            
        } else {           
            $oldpass = md5($_POST['oldpass']);
            $newpass = md5($_POST['newpass']);

            $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

            $token = new Token;
            $payload = $token->decode($jwt);
    
            $uid = $payload['sub'];
            $email = $payload['email'];

            $account = new Account;
            
            if(empty($account->selectByUserAndPassword($email, $oldpass))) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'Senha antiga incorreta, confira os dados digitados...',
                    'status_code' => 'incorrect_old_password'
                ]);                
            } else {
                $user = $account->selectByEmail($email);
                
                if($user['VerifiedEmail'] == false) {
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Por segurança, para alterar sua senha você deve verificar seu e-mail.',
                        'status_code' => 'unverified_email'
                    ]);
                } else if(!empty($account->selectByUserAndPassword($email, $newpass))) {   
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Nova senha digitada é a mesma que a antiga...',
                        'status_code' => 'unverified_email'
                    ]);                
                } else {
                    if($account->updatePassword($uid, $newpass)) {

                        $cryptography = new Cryptography;
                        $EncMail = $cryptography->EncryptText($email);
                        
                        $emailService = new Email;
                        $emailService->send(
                            $subject = 'Alerta de segurança: ' . $email . ', verifique o acesso à sua conta do ZapTank',
                            $body = ' <style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style><table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $email . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"> <span class="m_-5336645264442155576mail__title" style=""> <h1><font color="#ffffff">Você está recebendo este aviso pois sua senha foi alterada</font></h1> </span> </td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/">Verificar atividade</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"><p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 44px;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody></table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>',
                            $altBody = 'Sua senha foi alterada!',
                            $email
                        );
    
                        $body = json_encode([
                            'success' => true,
                            'message' => 'Senha alterada com sucesso, realize o login novamente.',
                            'status_code' => 'password_changed'
                        ]);               
                    } else {
                        $body = json_encode([
                            'success' => false,
                            'message' => 'Falha ao alterar a senha, desculpe. Não foi possível se conectar com o banco de dados.',
                            'status_code' => 'internal_error'
                        ]);     
                    }
                }
            }
        }
        
        $response->getBody()->write($body);
        return $response;
    }

    public function changeEmailNotVerified(Request $request, Response $response, array $args) :Response {
        
        if((!isset($_POST['current_email']) || empty(trim($_POST['current_email']))) || (!isset($_POST['new_email']) || empty(trim($_POST['new_email'])))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não preencheu todos os campos solicitados.',
                'status_code' => 'empty_fields'
            ]);
        } else {
            $current_email = $_POST['current_email'];
            $new_email = $_POST['new_email'];

            if(Validator::validateEmail($new_email) == false) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'E-mail inválido.',
                    'status_code' => 'invalid_email'
                ]);

                $response->getBody()->write($body);
                return $response;
            }

            $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

            $token = new Token;
            $payload = $token->decode($jwt);
    
            $account_email = $payload['email'];

            $account = new Account;
            $user = $account->selectByEmail($current_email);

            if(empty($user) || $current_email != $account_email) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'E-mail atual informado é inválido.',
                    'status_code' => 'invalid_current_email'
                ]); 
            } else if($user['VerifiedEmail'] == true) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'Para alterar o e-mail de uma conta verificada faça login na sua conta e procure por central de configurações.',
                    'status_code' => 'email_already_verified'
                ]); 
            } else if(!empty($account->selectByEmail($new_email))) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'Já existe alguém com esse endereço de e-mail.',
                    'status_code' => 'email_in_use'
                ]); 
            } else {
                $account->updateEmail($current_email, $new_email);
                
                $body = json_encode([
                    'success' => true,
                    'message' => 'E-mail alterado com sucesso, realize o login novamente.',
                    'status_code' => 'email_changed'
                ]);
            }
        }

        $response->getBody()->write($body);
        return $response;
    }    
    
    public function saveEmailChangeRequest(Request $request, Response $response, array $args) :Response {

        if(!isset($_POST['email']) || empty(trim($_POST['email']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'O e-mail deve ser informado.',
                'status_code' => 'empty_fields'
            ]);

            $response->getBody()->write($body);
            return $response;
        } 

        $email = $_POST['email'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);

        $uid = $payload['sub']; 
        $account_email = $payload['email'];

        if($email != $account_email) {
            $body = json_encode([
                'success' => false,
                'message' => 'O e-mail informado é inválido e não está associado a conta.',
                'status_code' => 'invalid_email'
            ]);
        } else {
            $emailModel = new EmailModel;
            $emailChangeRequest = $emailModel->selectEmailChangeRequest($uid);

            if(empty($emailChangeRequest)) {

                $account = new Account;
                $user = $account->selectByEmail($email);
    
                if($user['VerifiedEmail'] == 0) {
                    $body = json_encode([
                        'success' => false,
                        'message' => "{$email} não foi verificado, verifique-o primeiro para prosseguir com a alteração!",
                        'status_code' => 'unverified_email'
                    ]);
                } else {
                    $cryptography = new Cryptography;
                    $EncMail = $cryptography->EncryptText($email);
                    $token = md5(time());
                    
                    $emailModel->insertEmailChangeRequest($uid, $token, $date = Date::getDate($pattern = 'd/m/Y H:i:s'));
    
                    $emailService = new Email;
                    $email_sent = $emailService->send(
                        $subject = "Alerta de segurança: {$email}, verifique o acesso à sua conta do ZapTank",
                        $body = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style><table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $email . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"> <span class="m_-5336645264442155576mail__title" style=""> <h1><font color="#ffffff">Recebemos um pedido para alterar seu e-mail!</font></h1> </span> </td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/change_mail?token=' . $token . '" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/change_mail?token=' . $token . '">Alterar e-mail</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"><p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 44px;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody></table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>',
                        $altBody = 'Troca de e-mail',
                        $email
                    );
    
                    if($email_sent) {
                        $body = json_encode([
                            'success' => true,
                            'message' => 'Email enviado com sucesso, caso não encontre nenhum email verifique o SPAM.',
                            'email_sent' => true,
                            'status_code' => 'email_sent'
                        ]);                    
                    } else {
                        $body = json_encode([
                            'success' => false,
                            'message' => 'Seu e-mail não foi enviado, estamos com uma demanda de e-mails acima do normal. Nossos engenheiros foram notificados e estão resolvendo o mais rápido possível.',
                            'email_sent' => false,
                            'status_code' => 'email_not_sent'
                        ]);                
                    }
                }                  
            } else {

                $last_email_change_request = date('Y-m-d H:i:s', strtotime($emailChangeRequest['Date']));
                $interval = Date::difference($last_email_change_request, Date::getDate());
    
                if($interval->i < 2 && $interval->h == 0 && $interval->d == 0 && $interval->m == 0 && $interval->y == 0) {
                    $lastEmailChangeRequest = date('H:i:s', strtotime($emailChangeRequest['Date']));
    
                    $body = json_encode([
                        'success' => false,
                        'message' => "Aguarde 2 minutos para enviar outro e-mail, você enviou um e-mail em {$lastEmailChangeRequest}",
                        'status_code' => 'many_attempts'
                    ]);
                } else {
                    $account = new Account;
                    $user = $account->selectByEmail($email);
        
                    if($user['VerifiedEmail'] == 0) {
                        $body = json_encode([
                            'success' => false,
                            'message' => "{$email} não foi verificado, verifique-o primeiro para prosseguir com a alteração!",
                            'status_code' => 'unverified_email'
                        ]);
                    } else {
                        $cryptography = new Cryptography;
                        $EncMail = $cryptography->EncryptText($email);
                        $token = md5(time());
                        
                        $emailModel->insertEmailChangeRequest($uid, $token, $date = Date::getDate($pattern = 'd/m/Y H:i:s'));
        
                        $emailService = new Email;
                        $email_sent = $emailService->send(
                            $subject = "Alerta de segurança: {$email}, verifique o acesso à sua conta do ZapTank",
                            $body = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style><table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $email . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"> <span class="m_-5336645264442155576mail__title" style=""> <h1><font color="#ffffff">Recebemos um pedido para alterar seu e-mail!</font></h1> </span> </td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/change_mail?token=' . $token . '" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/change_mail?token=' . $token . '">Alterar e-mail</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"><p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 44px;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody></table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>',
                            $altBody = 'Troca de e-mail',
                            $email
                        );
        
                        if($email_sent) {
                            $body = json_encode([
                                'success' => true,
                                'message' => 'Email enviado com sucesso, caso não encontre nenhum email verifique o SPAM.',
                                'email_sent' => true,
                                'status_code' => 'email_sent'
                            ]);                    
                        } else {
                            $body = json_encode([
                                'success' => false,
                                'message' => 'Seu e-mail não foi enviado, estamos com uma demanda de e-mails acima do normal. Nossos engenheiros foram notificados e estão resolvendo o mais rápido possível.',
                                'email_sent' => false,
                                'status_code' => 'email_not_sent'
                            ]);                
                        }
                    }            
                }
            }
        }

        $response->getBody()->write($body);
        return $response;
    }

    public function changeEmail(Request $request, Response $response, array $args) :Response {

        if(!isset($_POST['new_email']) || empty(trim($_POST['new_email']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você deve informar o novo e-mail.',
                'status_code' => 'empty_fields'
            ]);
        } else {
            $new_email = $_POST['new_email'];

            if(Validator::validateEmail($new_email) == false) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'E-mail inválido.',
                    'status_code' => 'invalid_email'
                ]);

                $response->getBody()->write($body);
                return $response;
            }
            
            $account = new Account; 

            if(!empty($account->selectByEmail($new_email))) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'Já existe alguém com esse endereço de e-mail...',
                    'status_code' => 'email_in_use'
                ]);
            } else {
                $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

                $token = new Token;
                $payload = $token->decode($jwt);
        
                $uid = $payload['sub']; 
                $account_email = $payload['email'];
                
                $account->updateEmail($account_email, $new_email);

                $database = new Database;

                $database->get()->query("UPDATE {$_ENV['BASE_SERVER']}.dbo.change_email SET IsChanged=1 WHERE userID = '$uid'");

                $body = json_encode([
                    'success' => true,
                    'message' => 'E-mail alterado com sucesso, realize o login novamente.',
                    'status_code' => 'email_changed'
                ]);
            }
        }

        $response->getBody()->write($body);
        return $response;
    }

    public function saveEmailActivationRequest(Request $request, Response $response) :Response {

        $requestLimiter = new RequestLimiter(IpAdress::getUserIp());
        $remainingTime = $requestLimiter->limitEmailActivationRequest();
        $requestTime = date('H:i:s', strtotime("+$remainingTime seconds", strtotime(Time::get())));

        if($remainingTime > 0) {
            $body = json_encode([
                'success' => false,
                'message' => "Aguarde {$remainingTime} segundos antes de fazer outra solicitação.",
                'status_code' => 'many_requests'
            ]); 

            $response->getBody()->write($body);
            return $response;
        }

        if(!isset($_POST['email']) || empty(trim($_POST['email']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você deve informar o e-mail.',
                'status_code' => 'empty_fields'
            ]);

            $response->getBody()->write($body);
            return $response;
        }

        $email = $_POST['email'];

        $cryptography = new Cryptography;
        $EncMail = $cryptography->EncryptText($email);

        $account = new Account;
        $user = $account->selectByEmail($email);

        if(empty($user)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Este e-mail não existe.',
                'status_code' => 'unknown_email'
            ]);
    
            $response->getBody()->write($body);
            return $response;            
        } else {
            $userId = $user['UserId'];
        }

        if($user['VerifiedEmail']) {
            $body = json_encode([
                'success' => false,
                'message' => 'Esta conta já foi ativada!',
                'status_code' => 'user_has_already_been_verified'
            ]);

            $response->getBody()->write($body);
            return $response;
        }

        $token = md5(time());
		$date = date('d/m/Y H:i');

        $emailModel = new EmailModel;
        $emailModel->insertEmailActivationToken($userId, $token, $date = Date::getDate());

        $emailService = new Email;
        $email_sent = $emailService->send(
            $subject = 'Atendimento ZapTank - Ative sua conta',
            $body = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style><table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $email . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"> <span class="m_-5336645264442155576mail__title" style=""> <h1><font color="#ffffff">Esse e-mail é para que você tenha acesso total à sua conta ZapTank a ativação é bem rápida! Clique no botão para ativar sua conta.</font></h1> </span> </td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/active_account?token=' . $token . '" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/active_account?token=' . $token . '">Ativar conta</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"><p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 44px;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody></table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>',
            $altBody = 'Atendimento ZapTank - Ative sua conta',
            $email
        );

        if($email_sent) {

            $requestLimiter->addRequestInformation(IpAdress::getUserIp(), 'last_email_activation_time', Time::get());

            $body = json_encode([
                'success' => true,
                'message' => 'Enviamos um e-mail para verificar sua conta, caso não encontre nenhum email verifique o SPAM.',
                'status_code' => 'verification_email_sent'
            ]);

            $response->getBody()->write($body);
            return $response;
        } else {
            $body = json_encode([
                'success' => false,
                'message' => 'Seu e-mail não foi enviado, estamos com uma demanda de e-mails acima do normal. Nossos engenheiros foram notificados e estão resolvendo o mais rápido possível.',
                'status_code' => 'verification_email_not_sent'
            ]);

            $response->getBody()->write($body);
            return $response;
        }
    }

    public function checkIfEmailIsVerified(Request $request, Response $response) :Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);

        $account_email = $payload['email'];

        $account = new Account;
        $user = $account->selectByEmail($account_email);

        $body = json_encode([
            'email_is_verified' => boolval($user['VerifiedEmail'])
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}