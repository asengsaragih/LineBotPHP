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
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature, $httpClient)
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
 
    // kode aplikasi nanti disini
    $data = json_decode($body, true);
    if (is_array($data['events'])) {
        foreach ($data['events'] as $event) {
            date_default_timezone_set("Asia/Jakarta");
            $userMessage = strtolower($event['message']['text']);

            switch ($userMessage) {
                case '/menu':
                    $flexTemplate = file_get_contents("menu.json"); // template flex message
                    $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                        'replyToken' => $event['replyToken'],
                        'messages'   => [
                                    [
                                'type'     => 'flex',
                                'altText'  => 'Test Flex Message',
                                'contents' => json_decode($flexTemplate)
                            ]
                        ],
                    ]);
                    break;
                case '/flex':
                    $flexTemplate = file_get_contents("jadwal.json"); // template flex message
                    $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                        'replyToken' => $event['replyToken'],
                        'messages'   => [
                                    [
                                'type'     => 'flex',
                                'altText'  => 'Test Flex Message',
                                'contents' => json_decode($flexTemplate)
                            ]
                        ],
                    ]);
                    break;
                case '/kuliah':
                    $nama_hari = date("l");
                    $jadwal_Matkul = "";

                    if ($nama_hari == "Monday") {
                        $jadwal_Matkul = "Mobpro Lanjut \nD4 \n08.30 - 10.30\n\n B.Indonesia \nA3 \n11.30 - 13.30";
                    } elseif ($nama_hari == "Tuesday") {
                        # code...
                        $jadwal_Matkul = "Kuliah Hari Ini Kosong";
                    } elseif ($nama_hari == "Wednesday") {
                        # code...
                        $jadwal_Matkul = "Vvpl \nA5 \n06.30 - 11.30\n\nManajemen Proyek IT \nB3 \n12.30 - 16.30";
                    } elseif ($nama_hari == "Thursday") {
                        # code...
                        $jadwal_Matkul = "Mobpro Lanjut \nC1 \n08.30 - 12.30\n\nPBS \nB3 \n15.30 - 17.30";
                    } elseif ($nama_hari == "Friday") {
                        # code...
                        $jadwal_Matkul = "Pengembangan Profesionalisme \nC1 \n09.30 - 11.30\n\nPBS \nB3 \n13.30 - 17.30";
                    } elseif ($nama_hari == "Saturday") {
                        # code...
                        $jadwal_Matkul = "Kuliah Hari Ini Kosong";
                    } else {
                        $jadwal_Matkul = "Minggu ngga ada kuliah, selamat berlibur, jangan lupa TP";
                    }

                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($jadwal_Matkul);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/info':
                    # code...
                    $sumber = "https://asengsaragih.000webhostapp.com/LineBotAndroid/readInfo.php";
                    $konten = file_get_contents($sumber);
                    $dataInfo = json_decode($konten, true);
                    $text_gabungan = "";
                    foreach ($dataInfo as $d) { 
                        $tanggal = $d['tanggal_pengumpulan'];
                        $keterangan = $d['keterangan_tugas'];
                        $tahun = substr($tanggal,0,4);
                        $bulan = substr($tanggal,-4,-2);
                        $hari = substr($tanggal,6);  
                        $gabungan_tanggal = $hari."-".$bulan."-".$tahun;
                        $text_gabungan .= "{$gabungan_tanggal}"." || "."{$keterangan}"."\n\n";
                    }
                    if ($text_gabungan == null) {
                        $text_gabungan = "Kosong";
                    }
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text_gabungan);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/tentang':
                    $tentang = "-- TENTANG APLIKASI --\n\n"."Bot Line ini di buat oleh aldi. yang bertujuan, agar lock screen / wallpaper smartphone kalian ngga gambar jadwal perkuliahan lagi. jika kalian menemukan bug dalam bot ini maka segera hubungi Line : aldi_saragih \n\n"."Hatur Nuhun";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($tentang);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/waldos':
                    $waldos = "--- INFO WALI DOSEN ---\n\n"."Nama : Indra Azimi, S.T., M.T.\n"."NIP : 14870060\n"."Telp : 081345679546";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($waldos);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case 'punten':
                    if ($event['source']['type'] == 'group' or $event['source']['type'] == 'room' ) {
                        # code...
                        $userId     = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile    = $getprofile->getJSONDecodedBody();
                        $greetings  = new TextMessageBuilder("Mangga, ".$profile['displayName']." ".$profile['userId']);
                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                        break;
                    } else {
                        break;
                    }
                case '/cek':
                    $textMessageBuilder1 = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("pesan 1");
                    $textMessageBuilder2 = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("pesan 2");

                    $multiMessaegBuiler = new MultiMessageBuilder();
                    $multiMessaegBuiler->add($textMessageBuilder1);
                    $multiMessaegBuiler->add($textMessageBuilder2);

                    $result = $bot->replyMessage($event['replyToken'], $multiMessaegBuiler);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();

                    break;
                    
                default:
                    break;
            }

        }
    }
 
    return $response->withStatus(400, 'No event sent!');
 
});
//push message atau broadcast message
$app->get('/pushmessage', function($req, $res) use ($bot)
{
    // send push message to user
?>
    <form method="GET">
        <input type="text" name="pesan" placeholder="Masukkan Pesan" required>
        <br><br>
        <input type="submit" name="submit">
    </form>
<?php
    if (isset($_GET['submit'])) {
        $isiPesan = $_GET['pesan'];
        $userId = 'Udb1551f3893cade017d14653f2b186c3';
        $textMessageBuilder = new TextMessageBuilder($isiPesan);
        $result = $bot->pushMessage($userId, $textMessageBuilder);
        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
        header('Location: index.php/pushmessage');
    }
    
    //yang dibawah untuk push stiker
    // $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
    // $bot->pushMessage($userId, $stickerMessageBuilder);
});

$app->get('/multicast', function($req, $res) use ($bot)
{
    // list of users
    $userList = ['Udb1551f3893cade017d14653f2b186c3'];
 
    // send multicast message to user
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
    $result = $bot->multicast($userList, $textMessageBuilder);
   
    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});
$app->run();

?>