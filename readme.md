# CONFIGURAR PROJETO

## Configurações essênciais

#### Passo 1: Instalar dependências do projeto

```
composer require slim/slim
composer require slim/psr7
composer require vlucas/phpdotenv
composer require firebase/php-jwt
composer require phpmailer/phpmailer
composer require monolog/monolog
```


#### Passo 2: Configurar cors da api no index.php

```$allowedOrigins = ['https://developer.redezaptank.com.br', 'https://appws.picpay.com', 'https://api.openpix.com.br'];```


#### Passo 3: Configurar arquivo .env

**renomear .env.example para .env e alterar variáveis**

_Variáveis banco de dados SQL_
```
DB_HOST = ""
DB_USER = ""
DB_PASSWORD = ""
DB_DRIVER = ""
BASE_SERVER = ""
```

_Variáveis servidor de e-mail_
```
SMTP_HOST = ''
SMTP_USERNAME = ''
SMTP_PASSWORD = ''
SMTP_PORT = ''
```

_Variáveis gateway de pagamento_
```
PICPAY_TOKEN = ''
PAGARME_TOKEN = ''
OPENPIX_TOKEN = ''
```

**Url solicitada na alteração do status de pagamento**
```
PICPAY_CALLBACK_URL = 'https://api.redezaptank.com.br/payment/notification/picpay'
```

**Keys**

_Mesmas do globalconn.php_
```
PUBLIC_KEY = ''
PRIVATE_KEY = ''
```

_Key usada para gerar token de autênticação de usuário_
```
SECRET_KEY = ''
```

**Links**

```
RESOURCE = "https://cdn.redezaptank.com.br/resourcev127/image"
```


## PERSONALIZAR CONFIGURAÇÕES ##

**Configurações chargeback**

```
ENABLE_CHARGEBACK = 1
CHARGEBACK_AVAILABLE_AT = '05/05/2023 às 4:00 A.M'
```

**Configurações de login**

_Tempo de expiração_
```
LOGIN_EXPIRATION_TIME_IN_SECONDS = 86400
```


**Limitar requisições**

```
INTERVAL_IN_SECONDS_FOR_PASSWORD_RECOVERY = 120
INTERVAL_IN_SECONDS_FOR_EMAIL_ACTIVATION = 120
```


**Links**

_Página grupos do whatsapp_
```
WHATSAPP = '/selectwhats'
```