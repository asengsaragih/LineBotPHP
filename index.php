<?php
require __DIR__ . '/vendor/autoload.php';
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
// set false for production
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "8xtDfluem/Ge7UAtzNR/GglLh+pgcIg8YKg8PHRImy59p0oyvRyLCAwxTivzA5z9mioJCB1hrFNUyoZ04YJw2USLFGwi9pa5fSU6ZTZ1LUQJVf2dXXKtDZGgjEgTE6RXCnK3q6sp6/VSTgb4iuqnLAdB04t89/1O/w1cDnyilFU=";
$channel_secret = "30ea600d88c72fd96190fc57d7844cc4";
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
 
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Welcome at Slim Framework";
});
 
// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);
 
    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }
 
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                // $userMessage = $event['message']['text'];
                // if($event['message']['type'] == 'text')
                // {
                //     // send same message as reply to user
                //     $result = $bot->replyText($event['replyToken'], $event['message']['text']);
     
                //     // or we can use replyMessage() instead to send reply message
                //     // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                //     // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
     
                //     return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                // }
                if($event['source']['type'] == 'group' or $event['source']['type'] == 'room'){
                //message from group / room    
                    if($event['source']['userId']){
                        $userId     = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile    = $getprofile->getJSONDecodedBody();
                        $greetings  = new TextMessageBuilder("Halo, ".$profile['displayName']);
                     
                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                     
                    } else {
                        // send same message as reply to user
                        $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                    }
                } else {
                //message from single user
                }
            }
        } 
    }
 
});

//push message atau broadcast message
$app->get('/pushmessage', function($req, $res) use ($bot)
{
    // send push message to user
    $userId = 'Udb1551f3893cade017d14653f2b186c3';

    // $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
    // $result = $bot->pushMessage($userId, $textMessageBuilder);
    
    //yang dibawah untuk push stiker
    $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
    $bot->pushMessage($userId, $stickerMessageBuilder);
    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});

$app->get('/multicast', function($req, $res) use ($bot)
{
    // list of users
    $userList = [
        'U206d25c2ea6bd87c17655609xxxxxxxx',
        'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'];
 
    // send multicast message to user
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
    $result = $bot->multicast($userList, $textMessageBuilder);
   
    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});
$app->run();

?>