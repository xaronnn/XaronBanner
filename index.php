<?php
/*
 Author: XARON
 File: TeamSpeak3DynamicBanner
 Last Update: 07.05.2017 12:43

 GET IN OVER HERE! :)
*/
header('Content-type: image-png; charset=utf-8');
error_reporting(E_ALL);
require_once('ts3admin.class.php');
require_once('TeamSpeak3/TeamSpeak3.php');

$ayarlar = array();
$resimler = array();
$ayarlar['host'] = '127.0.0.1'; //Server IP
$ayarlar['queryAdi'] = 'serveradmin'; //Query Name
$ayarlar['querySifre'] = 'HELLO'; //Query Password
$ayarlar['queryPort'] = 10011; //Query Port
$ayarlar['sunucuPort'] = 9987; //Server Port
$ayarlar['botAdi'] = 'GET IN OVER HERE!'; //Bot Name
$ayarlar['apiKey'] = 'THERE'; // "apixu.com" Api Key
$resimler = ['arkaplan.png','arkaplan2.png']; // Background's

$ts3Baglan = TeamSpeak3::factory('serverquery://'.$ayarlar['queryAdi'].':'.$ayarlar['querySifre'].'@'.$ayarlar['host'].':'.$ayarlar['queryPort'].'/?server_port='.$ayarlar['sunucuPort'].'&blocking=0&nickname='.urlencode($ayarlar['botAdi']));
$clientListe = $ts3Baglan->clientList(array('connection_client_ip' => $_SERVER["REMOTE_ADDR"]));

foreach($clientListe as $clientListeYaz)
{
  $clientAdYaz = $clientListeYaz['client_nickname'];
}


function rastgeleSec($x) {
 return $x[rand(0, sizeof($x)-1)];
}
function ipAdresindenSehir($ip = null){

    if($ip == null){
        if(getenv('HTTP_CLIENT_IP')){
            $ip = getenv('HTTP_CLIENT_IP');
        }elseif(getenv('HTTP_X_FORWARDED_FOR')){
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            if(strstr($ip, ',')){
                $gec = explode (',', $ip);
                $ip = trim($gec[0]);
            }
        }else{
            $ip = getenv('REMOTE_ADDR');
        }
    }

    $json = file_get_contents('http://ipinfo.io/'.$ip);
    $detaylar = json_decode($json);
    return $detaylar;
}

$resim = imagecreatefrompng(rastgeleSec($resimler));
$gri = imagecolorallocate($resim, 82, 82, 82);
$beyaz = imagecolorallocate($resim, 255, 255, 255);
$query = new ts3admin($ayarlar['host'], $ayarlar['queryPort']);
$ip = ipAdresindenSehir(null);

$url = file_get_contents('http://api.apixu.com/v1/current.json?key='.$ayarlar['apiKey'].'&q='.$ip->city);
$json = json_decode($url, true);
date_default_timezone_set($json['location']['tz_id']);

$havaDurumuResimUrl = $json['current']['condition']['icon'];
$havaDurumuResimUrl = str_replace('//', 'http://', $havaDurumuResimUrl);
$fligram = imagecreatefrompng($havaDurumuResimUrl);

$konum_x = imagesx($fligram);
$konum_y = imagesy($fligram);

if($query->getElement('success', $query->connect()))
{
	$query->login($ayarlar['queryAdi'],$ayarlar['querySifre']);
    $query->selectServer($ayarlar['sunucuPort']);
	$query->setName($ayarlar['botAdi']);

    $sunucuBilgi = $query->getElement('data', $query->serverInfo());
    $queryClient = $sunucuBilgi['virtualserver_queryclientsonline'];
	$normalClient = $sunucuBilgi['virtualserver_clientsonline'];
	$sunucuKapasite = $sunucuBilgi['virtualserver_maxclients'];
	$sunucuAdi = $sunucuBilgi['virtualserver_name'];
	$sehirAdi = $json['location']['name'];
	$sehirKacDerece = $json['current']['temp_c'];
	$aktifKullanici = ($normalClient - $queryClient);
	
	//imagettftext($resim, 25, 0, 285, 55, $beyaz, 'CaviarDreams.ttf', $sunucuAdi);
	imagettftext($resim, 17, 0, 430, 150, $beyaz, 'CaviarDreams.ttf', 'Welcome '.$clientAdYaz.'!');
	imagettftext($resim, 14, 0, 23, 115, $gri, 'CaviarDreams_Bold.ttf', strftime('%e %B %Y'));
	imagettftext($resim, 33, 0, 25, 165, $gri, 'CaviarDreams_Bold.ttf', date('H:i'));
	imagettftext($resim, 29, 0, 60, 237, $gri, 'CaviarDreams_Bold.ttf', ''.$aktifKullanici.'/'.$sunucuKapasite);
	imagettftext($resim, 15, 0, 690, 240, $beyaz, 'CaviarDreams.ttf', $sehirAdi);
	imagettftext($resim, 10, 0, 765, 220, $beyaz, 'CaviarDreams.ttf', $sehirKacDerece.'°');
	imagecopy($resim, $fligram, imagesx($resim) - $konum_x - 35, imagesy($resim) - $konum_y - 25, 0, 0, imagesx($fligram), imagesy($fligram));
	
}
imagepng($resim);
imagedestroy($resim);
?>
