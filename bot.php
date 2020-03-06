<?php

//connection
require 'connection.php';

//token
$botToken = "1099773239:AAEDlg9VuIykF7c3sOQsYU7gPTadzTLre2Y";

//base URL API telegram bot
$website = "https://api.telegram.org/bot".$botToken;

//Mengambil informasi dari webhook, informasi diparse dalam message
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

//Mengambil text & id tiap user
$message = $update["message"]["text"];
$chatId = $update["message"]["chat"]["id"];
$userId = $update["message"]["from"]["id"];

//Coding pengaksesan database sesuai kebutuhan
$out = '';
$step = '';

//Check userId dalam database
//check();

//Pengambilan state dari userId
$query = mysqli_query($conn,"SELECT state FROM data WHERE userId = '".$userId."'");
$row = $query->fetch_assoc();
$state = $row['state'];

//#DEBUG# check message
sendMessage($botToken,$chatId,$state);
sendMessage($botToken,$chatId,$userId);
echo "Debug#36";
sendMessage($botToken,$chatId,"Debug#36");

switch($message) {
    
    case '/start':
        $out = "Halo :D Terima kasih telah menggunakan kami sebagai bot reminder kalian. Berikut beberapa list command yang bisa kalian gunakan:\n1. /addData\n2. /addKegiatan\n3. /aboutUs";
        break;
        
    case '/addData':
        $out = "Silahkan masukkan nama kamu";
        break;
    
    default:
            
            switch($state) {
                
                case 'nama':
                    $state = 'nrp';
                    $out = "Halo {$message}! Silahkan masukkan NRP kamu";
                    
                    break;
                    
                case 'nrp':
                    $state = 'konfirmasi';
                    $out = "Apakah sudah benar NRP kamu {$message} ? (ya/tidak)";
                    break;
                    
                case 'konfirmasi':
                    if($message == 'ya' || $message == 'Ya' || $message == 'YA' || $message == 'yA') {
                        $out = "Terima kasih datamu sudah kami masukkan!";
                    }
                    else {
                        $out = "Silahkan gunakan command /addData kembali ya";
                    }
                    break;
                    
                default:
                    $out = "Halo :D Silahkan mendaftarkan jadwal kamu dengan command /addJadwal. Jika belum mendaftarkan diri silahkan gunakan command /addData";
            }
        break;
}

mysqli_query($conn,"UPDATE data SET '".$state."'='".$message."' WHERE userId='".$userId."'");
mysqli_query($conn,"UPDATE data SET state='".$state."' WHERE userId ='".$userId."'");
sendMessage($botToken,$chatId,$out);

//function sendMessage
function sendMessage($botToken, $chatId, $message) {
    
    //url
    $message = urlencode(utf8_encode($message));
    $url = "https://api.telegram.org/bot".$botToken."/sendMessage?chat_id=".$chatId."&text=".$message;
    file_get_contents($url);
    
}

//function check userId
function check() {
    GLOBAL $conn;
    GLOBAL $userId;
    $query = mysqli_query($conn,"SELECT userId FROM data");
    if(mysqli_num_rows($query) == 0) {
        echo "Error: Database kosong";
        return die;
    }
    else {
        $query = mysqli_query($conn,"SELECT userId FROM data WHERE userId = '.$userId.')");
        if($query == NULL) {
            $query = mysqli_query($conn,"INSERT INTO data (userId,state) VALUES ('.$userId.','nama')");
        }
    }
}

?>
