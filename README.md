# About smsportalen.no

The following document continues in norwegian, since the service is only for
Norway.

Smsportalen.no er en norsk tjeneste for å integrere SMS inn i applikasjoner eller 
sende SMS fra et web-grensesnitt. Det er ingen faste avgifter ved tjenesten,
kun kostnaden per SMS.

Koden i denne pakken er for å integrere SMS-sending i PHP-applikasjoner samt
en modul som integrerer SMS-sending i applikasjoner som kjører Yii Framework 2.

https://www.smsportalen.no/

Denne koden er utviklet av [Gammelsæter Data](https://www.gdata.no/).  
Smsportalen.no er utviklet av [IT Data AS (Adcom Molde)](https://adcom.no/).

## Tilgang
Du må ta kontakt med Adcom Molde for å opprette et kundeforhold og få en brukerkonto. 

## Gyldige telefonnummer
API-et støtter kun norske mobiltelefonnummer eller 12-sifrede 
datanummer. Landsnummer foran telefonnummeret er ikke tillatt. Denne koden vil validere
telefonnummeret før sending til API skjer.

Eksempler på godkjente nummer:
* 90909090
* 40404040
* 580000000000
* 589999999999

## Sende SMS
Eksempel på kode for å sende SMS:
```php
use \gdata\smsportalen\Api;

$username = 'dittbrukernavn';
$token = 'jf340fj430fj430fj0rjf30jgf043hg043gh(H';

try {
    $api = new Api($username, $token);
    // $api->setDebugMode(); // Uncomment this to set in debug mode (not sending SMS) 
    $api->send(['90000000', '40000000'], 'Dette er en test på SMS');
}
catch (\Exception $e) {
    $msg = "En feil skjedde med kode {$e->getCode()}: ".$e->getMessage();
    die($msg);
}
```
Du kan velge å pinge API for å sjekke at API-et er oppe gjennom
`$api->ping();`.

Merk at når du får exception, kan du sjekke koden for å finne ut hva som 
feilet:

```php
catch (Exception $e) {
    $code = $e->getCode(); 
    if ($code === Api::EXCEPTION_CODE_RECIPIENT_LIMIT) {
        // gjør noe
    }
    else if ($code === Api::EXCEPTION_CODE_URL_PARSE_FAIL) {
        // ...
    }
    else if ($code === Api::EXCEPTION_CODE_RESPONSE_PARSE_FAIL) {
        // ...    
    }
    else if ($code === Api::EXCEPTION_CODE_PHONENUMBER_INVALID) {
        // ...
    }     
}
```
## Yii Framework v2
Set up the component in your application configuration, normally at `config/web.php`:
```php
    ...
    'components'=>[
        ...
        'sms'=>[
            'class' => 'gdata\smsportalen\Component',
            'username' => 'username',
            'token' => 'then token you got from Adcom',
            // Enable debug mode when application is in debug mode to simulate
            // sending SMS:
            // 'debug' => YII_DEBUG
            // Or uncomment this to set in debug mode whatever the application is in:
            // 'debug' => true
        ],
        ...
    ]
    ...
```
Use the component in your code like this:
```php
// Please use try/catch to handle errors
$api = Yii::$app->sms: /* @var $api gdata\smsportalen\Api */
$api->send(['90000000','4000000'], 'Content of SMS');
```

## Debug-modus
Debug-modus vil ikke sende SMS, men du vil få en simulert sending med en respons som sier
at SMS ble sendt. Merk at send-metoden fremdelse vil validere telefonnumrene og prioritering
som vanlig.