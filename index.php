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
            $idGrupKelas = "Ca01deda556b50eb3941eb720de9a60fe";
            $idGrupPrivate = "C0964f2cf09b447618a304da9c2219993";
            $id_aseng = "Udb1551f3893cade017d14653f2b186c3";
            $cekGrup = $event['source']['groupId'];
            $idRoom = $event['source']['roomId'];
            $idGroup = $event['source']['groupId'];
            $grup = $event['source']['type'] == 'group';

            $errorTextInfo = "Hanya Bisa Dilakukan Digrup Kelas D3IF41-03";
            $errorText = "Tidak Bisa Menjalankan Perintah Ini";
            $datanullTextInfo = "Data Kosong";
            $manyDataTextInfo = "Data Terlalu Banyak";

            $userId     = $event['source']['userId'];
            $getprofile = $bot->getProfile($userId);
            $profile    = $getprofile->getJSONDecodedBody();
            $namaPengirim = $profile['displayName'];
            $idPengirim = $profile['userId'];

            $senin = "PBS\nA6\n08.30 - 10.30\n\nMobpro Lanjut\nA7\n12.30 - 16.30";
            $selasa = "Bhs Indonesia\nKU3.02.13\n10.30 - 12.30\n\nMobpro Lanjut\nD4\n14.30 - 16.30";
            $rabu = "Manajemen Proyek IT\nKU3.07.21\n12.30 - 14.30";
            $kamis = "Vvpl\nKU3.07.15\n06.30 - 11.30\n\nPBS\nA6\n12.30 - 16.30";
            $jumat = "Manajemen Proyek IT\nKU3.07.01\n09.30 - 11.30\n\nPengembangan Profesionalisme\nC2\n15.30 - 17.30";
            $seluruh_jadwal = "--Senin--\n\n".$senin."\n\n"."--Selasa--\n\n".$selasa."\n\n"."--Rabu--\n\n".$rabu."\n\n"."--Kamis--\n\n".$kamis."\n\n"."--Jum'at--\n\n".$jumat;

            $conn = mysqli_connect('localhost','id9483525_asengsaragih','ganteng00','id9483525_aseng');
            $date_now = date('Ymd');

            // function searchData($search_word) {
            //     $conn = mysqli_connect('localhost','id9483525_asengsaragih','ganteng00','id9483525_aseng');
            //     $sql = "SELECT * FROM mahasiswa WHERE panggilan LIKE '%$search_word%' OR nim LIKE '%$search_word%' OR nama LIKE '%$search_word%'";
            //     $execute_query = mysqli_query($conn, $sql);
            //     $rowCount = mysqli_num_rows($execute_query);
            // }

            function filterWord($string, $filters) {
                foreach ($filters as $banned_word) {
                    if (stristr($string, $banned_word)) {
                        return false;
                    }
                }
                return true;
            }

            $filters = array('/cari');

            if (!filterWord($userMessage,$filters)) {
                $search_word = substr($userMessage, 6);

                $sql = "SELECT * FROM mahasiswa WHERE panggilan LIKE '%$search_word%' OR nim LIKE '%$search_word%' OR nama LIKE '%$search_word%'";

                $execute_query = mysqli_query($conn, $sql);
                $rowCount = mysqli_num_rows($execute_query);

                if ($rowCount == 0) {
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($datanullTextInfo);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                } elseif ($rowCount >= 4) {
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($manyDataTextInfo);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                } elseif ($rowCount > 0 && $rowCount <= 3) {
                    $textMahasiswa = "";
                    while ($key = mysqli_fetch_array($execute_query)) {
                        $nama_mahasiswa = $key['nama'];
                        $nim = $key['nim'];
                        $panggilan = $key['panggilan'];
                        $telp = $key['telp'];
                        $asal = $key['asal'];
                        $tglLahir = $key['tanggalLahir'];

                        if ($cekGrup == $idGrupKelas || $cekGrup == $idGrupPrivate) {
                            if ($idPengirim == $id_aseng) {
                                $textMahasiswa .= "Nim : {$nim} \nNama : {$nama_mahasiswa} \nTelp : 0$telp \nTanggal Lahir : {$tglLahir} \n\n";
                            } else {
                                $textMahasiswa .= "Nim : {$nim} \nNama : {$nama_mahasiswa} \nTelp : 0$telp \nTanggal Lahir : {$tglLahir} \n\n";
                            }
                        } else {
                            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($errorTextInfo);
                            $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                            return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                        }
                    }

                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($textMahasiswa);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                }
            }


            switch ($userMessage) {
                case '/covid':
                    $link = file_get_contents("https://api.kawalcorona.com/indonesia/provinsi?fbclid=IwAR2gfnx-6RwdKhNLpH5hM5t3X7I8jd0op8l7sdNuKtSncpa8OwhPXeMuuMc");
                    $row = json_decode($link, true);
                    $text = "";

                    for ($i = 0; $i < count($row); $i++) {
                        $provinsi =  $row[$i]['attributes']['Provinsi'];
                        $positive =  $row[$i]['attributes']['Kasus_Posi'];
                        $sembuh =  $row[$i]['attributes']['Kasus_Semb'];
                        $meninggal =  $row[$i]['attributes']['Kasus_Meni'];

                        $text .=
                            "Provinsi : "."{$provinsi}"."\n".
                            "Positif : "."{$positive}"."\n".
                            "Sembuh : "."{$sembuh}"."\n".
                            "Meninggal : "."{$meninggal}"."\n\n";

                    }

                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text."\n Stay Safe All");
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
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
                case '/jadwal':
                    $flexTemplate = file_get_contents("jadwal_harian.json"); // template flex message
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
                        $jadwal_Matkul = "PBS\nA6\n08.30 - 10.30\n\nMobpro Lanjut\nA7\n12.30 - 16.30";
                    } elseif ($nama_hari == "Tuesday") {
                        # code...
                        $jadwal_Matkul = "Bhs Indonesia\nKU3.02.13\n10.30 - 12.30\n\nMobpro Lanjut\nD4\n14.30 - 16.30";
                    } elseif ($nama_hari == "Wednesday") {
                        # code...
                        $jadwal_Matkul = "Manajemen Proyek IT\nKU3.07.21\n12.30 - 14.30";
                    } elseif ($nama_hari == "Thursday") {
                        # code...
                        $jadwal_Matkul = "Vvpl\nKU3.07.15\n06.30 - 11.30\n\nPBS\nA6\n12.30 - 16.30";
                    } elseif ($nama_hari == "Friday") {
                        # code...
                        $jadwal_Matkul = "Manajemen Proyek IT\nKU3.07.01\n09.30 - 11.30\n\nPengembangan Profesionalisme\nC2\n15.30 - 17.30";
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
                    if ($cekGrup == $idGrupKelas) {
                        # code...
                        // $sumber = "https://asengsaragih.000webhostapp.com/LineBotAndroid/readInfo.php";
                        // $konten = file_get_contents($sumber);
                        // $dataInfo = json_decode($konten, true);
                        // $text_gabungan = "";
                        // foreach ($dataInfo as $d) {
                        //     $tanggal = $d['tanggal_pengumpulan'];
                        //     $keterangan = $d['keterangan_tugas'];
                        //     $tahun = substr($tanggal,0,4);
                        //     $bulan = substr($tanggal,-4,-2);
                        //     $hari = substr($tanggal,6);
                        //     $gabungan_tanggal = $hari."-".$bulan."-".$tahun;
                        //     $text_gabungan .= "{$gabungan_tanggal}"." || "."{$keterangan}"."\n\n";
                        // }
                        // if ($text_gabungan == null) {
                        //     $text_gabungan = "Kosong";
                        // }
                        // $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text_gabungan);
                        // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                        // return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                        $query = mysqli_query($conn, "select * from jadwal where tanggal_pengumpulan >= $date_now order by tanggal_pengumpulan asc");
                        $teks_awalan = "------ INFO -----";
                        $teks_akhir = "Untuk Penambahan Info, Silahkan Chat KM atau Aldi";
                        $teks_info = "";
                        while ($key = mysqli_fetch_array($query)) {
                            $tanggal_peng = $key['tanggal_pengumpulan'];
                            $keterangan_tgs = $key['keterangan_tugas'];
                            $tahun = substr($tanggal_peng,0,4);
                            $bulan = substr($tanggal_peng,-4,-2);
                            $hari = substr($tanggal_peng,6);
                            $gabungan_tanggal = $hari."-".$bulan."-".$tahun;
                            $teks_info .= "{$gabungan_tanggal}"." || "."{$keterangan_tgs}"."\n";
                        }
                        if($teks_info == null){
                            $teks_info = "Kosong\n";
                        }
                        $teks_gabungan = $teks_awalan."\n\n".$teks_info."\n".$teks_akhir;
                        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($teks_gabungan);
                        $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                        return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                        break;
                    } else {
                        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($errorTextInfo);
                        $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                        return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                        break;
                    }

                case '/tentang':
                    $tentang = "-- TENTANG APLIKASI --\n\n"."Bot Line ini di buat oleh aldi. yang bertujuan, agar lock screen / wallpaper smartphone kalian ngga gambar jadwal perkuliahan lagi. jika kalian menemukan bug dalam bot ini maka segera hubungi Line : aldi_saragih \n\n"."Hatur Nuhun";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($tentang);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/dosen':
                    $flexTemplate = file_get_contents("dosen.json"); // template flex message
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
                case '/izm':
                    $izm = "Indra Azimi, ST., MT.\n14870060\nindraazimi@tass.telkomuniversity.ac.id\n0813-4567-9546";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($izm);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/npr':
                    $npr = "Fat’hah Noor Prawita, ST., MT.\n14840024\nfathah@tass.telkomuniversity.ac.id\n0812-2493-458";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($npr);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/htt':
                    $htt = "Hetti Hidayati, S.Kom., MT.\n06750056\nhettihd@tass.telkomuniversity.ac.id\n0812-2172-2311";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($htt);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/mch':
                    # code...
                    $htt = "Maria Christina\n-\nmariachristinautel@gmail.com\n-";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($htt);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/auy':
                    # code...
                    $auy = "Agus Suryana, S.S., M.Pd.\n-\nagus.nasrul.suryana@gmail.com\n0857-2110-2189";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($auy);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/ast':
                    # code...
                    $ast = "Ahman Sutardi, M.M.-\n195808056\nahman.sutardi@gmail.com\n0813-2271-2266";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($ast);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/jadwalsenin':
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($senin);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/jadwalselasa':
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($selasa);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/jadwalrabu':
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($rabu);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/jadwalkamis':
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($kamis);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/jadwaljumat':
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($jumat);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                case '/seluruhjadwal':
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($seluruh_jadwal);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                //Untuk Cek Nama Pengirim Dan Cek Kode Grup
                case '/cekgrup':
                    $cek = $cekGrup." ".$namaPengirim;
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($cek);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;

                case '/pebi':
                    $cek = "Hai Pebi";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($cek);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;

                case '/artika':
                    $cek = "Hai Tika dirimu sudah masuk bot lo";
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($cek);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                // cek user id
                case '/cekid':
                    $cek_id = $idPengirim." ".$namaPengirim;
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($cek_id);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                // cek multi return
                case '/cekmulti':
                    $textMessageBuilder1 = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("mention");
                    $textMessageBuilder2 = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("mention");
                    $textMessageBuilder3 = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("mention");
                    $textMessageBuilder4 = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("mention");
                    $textMessageBuilder5 = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("mention");
                    $textMessageBuilder6 = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("mention");

                    $multiMessaegBuiler = new MultiMessageBuilder();
                    $multiMessaegBuiler->add($textMessageBuilder1);
                    $multiMessaegBuiler->add($textMessageBuilder2);
                    $multiMessaegBuiler->add($textMessageBuilder3);
                    $result = $bot->replyMessage($event['replyToken'], $multiMessaegBuiler);
                    return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                    break;
                // leave group
                case '/leavegrup':
                    if($event['source']['type'] == 'group') {
                        $response = $bot->leaveGroup($idGroup);
                        return $response->getHTTPStatus() . ' ' . $response->getRawBody();
                        break;
                    } elseif ($event['source']['type'] == 'room') {
                        $response = $bot->leaveRoom($idRoom);
                        return $response->getHTTPStatus() . ' ' . $response->getRawBody();
                        break;
                    } else {
                        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($errorText);
                        $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                        return $result->getHTTPStatus() . ' ' . $result->getRawBody();
                        break;
                    }
                case '/reminder':
                    break;
                case '/sholat':
                    $linkSholat = "http://muslimsalat.com/bojongsoang/daily.json";
                    $openContent = file_get_contents($linkSholat);
                    $dataSholatJSON = json_decode($openContent, true);

                    $subuh = "Subuh : ".$dataSholatJSON['items']['0']['fajr'];
                    $zuhur = "Zuhur : ".$dataSholatJSON['items']['0']['dhuhr'];
                    $asar = "Asar : ".$dataSholatJSON['items']['0']['asr'];
                    $magrib = "Maghrib : ".$dataSholatJSON['items']['0']['maghrib'];
                    $isya = "Isya : ".$dataSholatJSON['items']['0']['isha'];

                    $textGabunganSholat = "Jadwal Sholat \nBojongsoang Bandung \n\n".$subuh."\n".$zuhur."\n".$asar."\n".$magrib."\n".$isya."\n";

                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($textGabunganSholat);
                    $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
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
    for ($i=0; $i < 5 ; $i++) {
        # code...
        $result = $bot->multicast($userList, $textMessageBuilder);
    }


    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});
$app->run();

?>