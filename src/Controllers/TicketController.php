<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Ticket;
use App\Zaptank\Models\Character;
use App\Zaptank\Models\Server;
use App\Zaptank\Models\Account;
use App\Zaptank\Models\Admin;

use App\Zaptank\Services\Token;
use App\Zaptank\Services\Email;
use App\Zaptank\Helpers\Cryptography;

class TicketController {

    public function new(Request $request, Response $response, array $args) :Response {
        
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
        $suv = $args['suv'];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];        

        $account = new Account;
        $user = $account->selectByEmail($account_email);

        if($user['VerifiedEmail'] == false) {
            $body = json_encode([
                'success' => false,
                'message' => 'Por segurança, para utilizar a função você deve verificar seu e-mail.',
                'status_code' => 'unverified_email'
            ]); 

            $response->getBody()->write($body);
            return $response;
        }

        if((!isset($_POST['subject']) || !isset($_POST['description']) || !isset($_POST['phone'])) || (empty(trim($_POST['subject'])) || empty(trim($_POST['description'])) || empty(trim($_POST['phone'])))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não preencheu todos os campos solicitados.',
                'status_code' => 'empty_fields'
            ]);       
            
            $response->getBody()->write($body);
            return $response;            
        } else {
            $category = $_POST['subject'];
            $description = $_POST['description'];
            $phone = $_POST['phone'];
        }
        
        if(mb_strlen($description) < 10) {
            $body = json_encode([
                'success' => false,
                'message' => 'A descrição do problema deve conter mais do que 10 caracteres...',
                'status_code' => 'short_description'
            ]);       
            
            $response->getBody()->write($body);
            return $response;                 
        }

        $ticket = new Ticket;

        if($ticket->getCountOfOpenTickets($account_email) == 3) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você tem muitos tickets abertos, aguarde a resolução dos tickets.',
                'status_code' => 'ticket_limit_exceeded'
            ]);       
            
            $response->getBody()->write($body);
            return $response;             
        }

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        if($decryptServer == false) {
            $response = new Response();
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $serverId = $server->Id;
        $baseUser = $server->baseUser;

        $character = new Character;
        $character->search($account_email, $baseUser);
        $characterId = $character->Id;
        $nickname = $character->nickName;

        $ticket->create($account_email, $nickname, $characterId, $description, $category, $phone, $serverId);

        $cryptography = new Cryptography;
        $EncMail = $cryptography->EncryptText($account_email);

        $emailService = new Email;
        $emailService->send(
            $subject = 'Recebemos seu ticket!',
            $body = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style><table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $account_email . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"> <span class="m_-5336645264442155576mail__title" style=""> <h1><font color="#ffffff">Recebemos o seu ticket :) iremos analisar o seu caso e retornamos com uma resposta em até 24 horas. O nosso suporte funciona 24 horas por dia 7 dias por semana.</b></font></h1> <h4><font color="#ffffff">Mensagem do Ticket: ' . $description . '</b></font></h4> </span> </td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/">Voltar para o Jogo</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"> <p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 0;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody></table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>',
            $altBody = 'Recebemos seu ticket!',
            $account_email
        );

        $admin = new Admin;
        $administrators = $admin->select();

        foreach ($administrators as $administrator) {

            $adminEmail = $administrator['UserName'];

            $user = $account->selectByEmail($adminEmail);

            if(!empty($user)) {
                $GetMail = $user['Email'];

                $emailService->send(
                    $subject = 'Novo ticket!',
                    $body = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style><table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $account_email . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"> <span class="m_-5336645264442155576mail__title" style=""> <h1><font color="#ffffff">Um novo ticket foi aberto</b></font></h1> <h4><font color="#ffffff">Mensagem do Ticket: ' . $category . '</b></font></h4> </span> </td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/viewtickets?suv=' . $suv . '" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/viewtickets?suv=' . $suv . '">Verificar Ticket</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"> <p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 0;font-size:14px;color:#999">E-mail enviado automaticamente, não responda.</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody></table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>',
                    $altBody = 'Novo ticket!',
                    $GetMail
                );                
            }
        }        

        if($category == 'Problemas de Login') {
            $body = json_encode([
                'success' => true,
                'message' => 'Ticket criado com successo!',
                'status_code' => 'ticket_created_with_advice'
            ]);
        } else {
            $body = json_encode([
                'success' => true,
                'message' => 'Sua solicitação foi aberta, entraremos em contato com você através do seu e-mail ou telefone.',
                'status_code' => 'ticket_created'
            ]);         
        }        
        
        $response->getBody()->write($body);
        return $response;
    }

    public function list(Request $request, Response $response, array $args) :Response {

        $ticket = new Ticket;

        $body = json_encode([
            'data' => $ticket->selectUnresolvedTickets()
        ]);

        $response->getBody()->write($body);
        return $response;
    }

    public function close(Request $request, Response $response, array $args) :Response {

        if (
            (!isset($_POST['ticket_id']) || empty(trim($_POST['ticket_id']))) ||
            (!isset($_POST['close']) || trim($_POST['close']) == '' || trim($_POST['close']) == 0)
        ) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não preencheu todos os campos solicitados.',
                'status_code' => 'empty_fields'
            ]);
        
            $response->getBody()->write($body);
            return $response;
        }

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
        $ticketId = $_POST['ticket_id'];
        $close = $_POST['close'];

        $token = new Token;
        $payload = $token->decode($jwt);
        $ticketViewerEmail = $payload['email']; // e-mail do administrador

        $ticket = new Ticket;
        $ticketDetails = $ticket->selectById($ticketId);
        $ticketCreator = $ticketDetails['Email'];

        $ticket->updateStatus($ticketId, $status = 1);
        $ticket->updateSolvedBy($ticketId, $solvedBy = $ticketViewerEmail);

        $cryptography = new Cryptography;
        $EncMail = $cryptography->EncryptText($ticketCreator);

        switch($close) {
            case 1:
                $EncRef = $cryptography->EncryptText($ticketId);
                $emailBody = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style> <table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $ticketCreator . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"><span class="m_-5336645264442155576mail__title" style=""><h1><font color="#ffffff">Um Administrador verificou e resolveu o seu caso, foram aplicadas as medidas necessárias para solucionar o seu problema, que tal avaliar o atendimento do nosso estagiário? Ficaremos muito felizes com sua contribuição :)</font></h1></span></td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/evaluation?ref=' . $EncRef . '" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/evaluation?ref=' . $EncRef . '">Avaliar atendimento</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"> <p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 0;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody> </table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>';
                break;
            case 2:
                $emailBody = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style> <table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $ticketCreator . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"><span class="m_-5336645264442155576mail__title" style=""><h1><font color="#ffffff">Você abriu um ticket e falhamos em entrar em contato com você através dos métodos fornecidos, para obter suporte por favor abra outro ticket com meios de contatos válidos.</font></h1></span></td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/">Voltar</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"> <p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 0;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody> </table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>';
                break;
            case 3:
                $emailBody = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto);body{font-family: "Roboto", sans-serif; font-size: 48px;}</style> <table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:"Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $ticketCreator . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"><img alt="DDTank" width="90" style="width:90px;text-align:center"></td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"><span class="m_-5336645264442155576mail__title" style=""><h1><font color="#ffffff">Você abriu um ticket e não conseguimos compreender as informações descritas, para obter suporte por favor abra outro ticket com mais informações detalhadas.</font></h1></span></td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <tr> <td align="center"> <table class="m_-5336645264442155576mail__buttons-container" align="center" width="200" border="0" cellpadding="0" cellspacing="0" style="border-radius:4px;height:48px;width:240px;table-layout:fixed;margin:32px auto"> <tbody> <tr> <td style="border-radius:4px;height:30px;font-family:"Proxima nova",Helvetica,Arial,sans-serif" bgcolor="#d65900"><a href="https://redezaptank.com.br/" style="padding:10px 3px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#fff;text-decoration:none;text-align:center" target="_blank" data-saferedirecturl="https://redezaptank.com.br/">Voltar</a></td></tr></tbody> </table> </td></tr><tr> <td align="center"> <p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 0;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site. Se você não deseja receber e-mails, <a href="https://redezaptank.com.br/unsubscribemaillist?mail=' . $EncMail . '" style="color:rgb(227, 72, 0);text-decoration:none" target="_blank" data-saferedirecturl="">cancele o recebimento</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody> </table><small class="text-muted"><?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?></small> </p></div></div>';
                break;
            default:
                $body = json_encode([
                    'success' => true,
                    'message' => 'O Ticket foi fechado e o jogador foi notificado, por favor, certifique-se de ter resolvido o problema do jogador para evitar tickets duplicados.',
                    'status_code' => 'ticket_was_closed'
                ]);
        
                $response->getBody()->write($body);
            return $response;
                break;
        }

        $emailService = new Email;
        $emailService->send(
            $subject = 'DDTank - Seu ticket foi fechado!',
            $body = $emailBody,
            $altBody = 'DDTank - Seu ticket foi fechado!',
            $ticketCreator
        );

        $body = json_encode([
            'success' => true,
            'message' => 'O Ticket foi fechado e o jogador foi notificado, por favor, certifique-se de ter resolvido o problema do jogador para evitar tickets duplicados.',
            'status_code' => 'ticket_was_closed'
        ]);

        $response->getBody()->write($body);
        return $response;
    }

    public function evaluateService(Request $request, Response $response, array $args) :Response {

        if (
            (!isset($_POST['rating']) || empty(trim($_POST['rating']))) ||
            (!isset($_POST['text']) || empty(trim($_POST['text'])))
        ) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não preencheu todos os campos solicitados.',
                'status_code' => 'empty_fields'
            ]);
        
            $response->getBody()->write($body);
            return $response;
        }
        
        $encryptedTicketId = $args['reference'];
        $rating = $_POST['rating'];
        $text = $_POST['text'];
        
        $cryptography = new Cryptography;
        $ticketId = $cryptography->DecryptText($encryptedTicketId);

        $ticket = new Ticket;
        $ticketDetails = $ticket->selectById($ticketId);
        
        if(empty($ticketDetails)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Não conseguimos encontrar este ticket.',
                'status_code' => 'ticket_not_found'
            ]);
    
            $response->getBody()->write($body);
            return $response;
        }

        if($ticketDetails['IsEvaluation'] == 1) {
            $body = json_encode([
                'success' => false,
                'message' => 'Não foi possível concluir a avaliação para esse ticket ele já foi avaliado.',
                'status_code' => 'already_evaluated'
            ]);
    
            $response->getBody()->write($body);
            return $response;
        }

        $ticket->updateEvaluationStars($ticketId, $rating);
        $ticket->updateEvaluationText($ticketId, $text);
        $ticket->updateIsEvaluation($ticketId, $isEvaluation = 1);

        $body = json_encode([
            'success' => true,
            'message' => 'Valeu :) obrigado(a) pelo seu feedback!',
            'status_code' => 'feedback_sent',
            'data' => $ticketDetails
        ]);

        $response->getBody()->write($body);
        return $response;
    }

    public function getDetails(Request $request, Response $response, array $args) :Response {

        $encryptedTicketId = $args['reference'];

        $cryptography = new Cryptography;
        $ticketId = $cryptography->DecryptText($encryptedTicketId);

        $ticket = new Ticket;
        $ticketDetails = $ticket->selectById($ticketId);
        
        if(empty($ticketDetails)) {
            $body = json_encode([
                'reference_is_valid' => false,
                'is_evaluation' => false
            ]);
    
            $response->getBody()->write($body);
            return $response;
        } else {

            $isEvaluation = $ticketDetails['IsEvaluation'];
    
            $body = json_encode([
                'reference_is_valid' => true,
                'is_evaluation' => $isEvaluation
            ]);

            $response->getBody()->write($body);
            return $response;
        }
    }
}