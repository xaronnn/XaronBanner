# XARON TeamSpeak3DynamicBanner



function GetWeather($a, $b, $c){
    @preg_match_all('/'.preg_quote($a, '/').'(.*?)'.preg_quote($b,'/').'/i', $c, $s);
    return @$s[1];
}
function GetCity($ip = null){
    if($ip == null){
        if(getenv('HTTP_CLIENT_IP')){
            $ip = getenv('HTTP_CLIENT_IP');
        }elseif(getenv('HTTP_X_FORWARDED_FOR')){
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            if(strstr($ip, ',')){
                $g = explode (',', $ip);
                $ip = trim($g[0]);
            }
        }else{
            $ip = getenv('REMOTE_ADDR');
        }
    }
    $apiUrl = 'http://ip-api.com/json/'.$ip;
    $json = file_get_contents($apiUrl);
    $d = json_decode($json);
    return $d;
}

$url = 'https://www.timeanddate.com/weather/'.GetCity(null)->country.'/'.GetCity(null)->city;
$content = file_get_contents($url);
$weather = GetWeather('width=80 height=80><div class=h2>','</div><p>', $content);

$degree = $weather[0];
echo GetCity(null)->city.' now '.$degree;

you can use this code for weather. no need api :)
