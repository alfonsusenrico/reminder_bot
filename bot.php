<?
//connection
require 'connection.php';

//token
$botToken = "1099773239:AAEDlg9VuIykF7c3sOQsYU7gPTadzTLre2Y";

//base URL API telegram bot
$website = "https://api.telegram.org/bot".$botToken;

//Mengambil informasi dari webhook, informasi diparse dalam message
$update = file_get_contents("php://input");
$updates = json_decode($update, TRUE);

//Mengambil text & id tiap user
$message = $updates['message']['text'];
$chatId = $updates['message']['chat']['id'];

//Coding pengaksesan database sesuai kebutuhan
$hasil ='';
switch($message) {
    case '/start':
        $hasil = "Selamat datang";
        sendMessage($botToken,$chatId,$hasil);
        break;
        
    case '/addData':
        $hasil = "Masukkan nama anda";
        sendMessage($botToken,$chatId,$hasil);
        $_SESSION['stat'] = 'nama';
        break;
        
    case 'Enrico':
        switch($_SESSION['stat']) {
                case 'nama':
                    $_SESSION['nama'] = $message;
                    $hasil = "Halo. {$_SESSION['nama']}. Masukkan nrp anda";
                    sendMessage($botToken,$chatId,$hasil); 
                    break;
                 
        }
        break;
}

//function sendMessage
function sendMessage($botToken, $chatId, $message) {
    //url
    $message = urlencode(utf8_encode($message));
    $url = "https://api.telegram.org/bot".$botToken."/sendMessage?chat_id=".$chatId."&text=".$message;
    file_get_contents($url);
}

//function check sql connection
function check() {
    if($conn === false) {
        die("Error tidak terkonek. ".mysqli_connect_error());
    }
}
?>