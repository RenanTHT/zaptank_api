<?php

namespace App\Zaptank\Controllers\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Models\Account;
use App\Zaptank\Services\Token;
use App\Zaptank\Services\Email;
use App\Zaptank\Helpers\Cryptography;

class ConfigController {
    
    public function changePhone(Request $request, Response $response) : Response {

        $phone = $_POST['phone'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->validate($jwt);

        $uid = $payload['sub'];

        if(empty($phone)) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);
        } else if(strlen($phone) < 19) {
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

        /*
        $file = fopen('log.txt', 'a');
        fwrite($file, date('d/m/Y H:i:s') . "\n");
        fclose($file);
        */
        
        $response->getBody()->write($body);
        return $response;
    }


    public function changePassword(Request $request, Response $response) : Response {

        $oldpass = md5($_POST['oldpass']);
        $newpass = md5($_POST['newpass']);

        if(empty($oldpass)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não informou a senha antiga.',
                'status_code' => 'empty_oldpass'
            ]);
        } else if(empty($newpass)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não informou a nova senha.',
                'status_code' => 'empty_oldpass'
            ]);            
        } else {
            $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

            $token = new Token;
            $payload = $token->validate($jwt);
    
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

    public function changeEmailNotVerified(Request $request, Response $response) :Response {
        
        $current_email = $_POST['current_email'];
        $new_email = $_POST['new_email'];
    
        if(empty($current_email) || empty($new_email)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não preencheu todos os campos solicitados.',
                'status_code' => 'empty_fields'
            ]);
        } else {
            $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

            $token = new Token;
            $payload = $token->validate($jwt);
    
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
    
    public function changeEmail(Request $request, Response $response) :Response {
        $response->getBody()->write('changeEmail');
        return $response;
    }
}