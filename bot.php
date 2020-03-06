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
check();

//Pengambilan state dari userId
$query = mysqli_query($conn,"SELECT state FROM data WHERE userId = '".$userId."'");
$row = $query->fetch_assoc();
$state = $row['state'];

//#DEBUG# check message
echo "Debug#43";
sendMessage($botToken,$chatId,"\nDebug#43\nState: {$state}");

switch($message) {
    
    case '/start':
        $out = "Halo :D Terima kasih telah menggunakan kami sebagai bot reminder kalian. Berikut beberapa list command yang bisa kalian gunakan:\n1. /addData\n2. /addJadwal\n3. /aboutUs";
        break;
        
    case '/addData':
        if($state != idle) {
            $out = "Silahkan masukkan nama kamu";
        }
        else {
            $out = "Kamu tidak perlu capek-capek mendaftarkan diri lagi karena kamu sudah terdaftar :)";
        }
        break;
    
    case '/addJadwal':
        $out = "Kegiatan rutin atau sekali jalan";
        $state = 'tipekegiatan';
        break;
    
    case '/aboutUs':
        sendMessage($botToken,$chatId,"Terima kasih sudah mau menggunakan bot kami. Bot ini dibuat oleh:\nAlfonsus Enrico @enrico06\nMichael Wida P. @michaelpramas\nVito Varian L. @vito_laman\nMahasiswa biasa saja di Informatika Petra.\n");
        break;
        
    default:
            
            switch($state) {
                
                case 'tipekegiatan':
                    mysqli_query($conn,"UPDATE jadwal set tipe='".$message."' WHERE userId='".$userId."'");
                    $state = 'jeniskegiatan';
                    $out = "Jenis kegiatan yang ingin dijadwalkan (kelas / lain)";
                
                case 'jeniskegiatan':
                    mysqli_query($conn,"UPDATE jadwal set jenis='".$message."' WHERE userId='".$userId."'");
                    $state = 'namakegiatan';
                    $out = "Silahkan masukkan nama kegiatan";
                    break;
                
                case 'namakegiatan':
                    mysqli_query($conn,"UPDATE jadwal set nama='".$message."' WHERE userId='".$userId."'");
                    $state = 'mulai';
                    $out = "Pukul berapa kegiatanmu dimulai (HH:MM)";
                    break;
                
                case 'mulai':
                    mysqli_query($conn,"UPDATE jadwal set mulai='".$message."' WHERE userId='".$userId."'");
                    $state = 'selesai';
                    $out = "Pukul berapa kegiatanmu selesai (HH:MM) (jika tidak tahu bisa diisi 00:00)";
                    break;
                    
                case 'selesai':
                    mysqli_query($conn,"UPDATE jadwal set selesai='".$message."' WHERE userId='".$userId."'");
                    $state = 'hari';
                    $out = "Silahkan masukkan hari";
                    break;
                
                case 'hari':
                    mysqli_query($conn,"UPDATE jadwal set hari='".$message."' WHERE userId='".$userId."'");
                    
                    $query = mysqli_query($conn,"SELECT tipe FROM jadwal WHERE userId = '".$userId."'");
                    $row = $query->fetch_assoc();
                    $tipe = $row['tipe'];

                    if($tipe == 'sekali') {
                        $state = 'tanggal';
                        $out = "Silahkan masukkan tanggal pelaksanaan kegiatan (YYYY:MM:DD)";
                    }
                    else {
                        $state = 'konfirmasi2';
                        $out = "Silahkan cek ulang data jadwal kamu";
                    }
                    break;
                    
                case 'tanggal':
                    mysqli_query($conn,"UPDATE jadwal set tanggal='".$message."' WHERE userId='".$userId."'");
                    $state = 'konfirmasi2';
                    $out = "Silahkan cek ulang data jadwal kamu";
                    break;
                    
                case 'konfirmasi2':
                    $query = mysqli_query($conn,"SELECT * FROM jadwal WHERE userId='".$userId."'");
                    $row = $query->fetch_assoc();
                    
                    //semua data jadwal
                    $tipe = $row['tipe'];
                    $jenis = $row['jenis'];
                    $nama = $row['nama'];
                    $hari = $row['hari'];
                    $tanggal = $row['tanggal'];
                    $mulai = $row['mulai'];
                    $selesai = $row['selesai'];
                    
                    if($tipe == 'rutin') {
                        $out = "Nama kegiatan   : {$nama}\nJenis kegiatan   : {$jenis}\nHari        : {$hari}\nJam mulai    : {$mulai}\nJam selesai : {$selesai}\nApakah sudah benar? (ya/tidak)";
                    }
                    else {
                        $out = "Nama kegiatan   : {$nama}\nJenis kegiatan   : {$jenis}\nHari        : {$hari}\nTanggal      : {$tanggal}\nJam mulai    : {$mulai}\nJam selesai : {$selesai}\nApakah sudah benar? (ya/tidak)";
                    }
                    $state = 'selesai';
                    break;
                    
                case 'selesai':
                    if($message == 'ya' || $message == 'Ya' || $message == 'YA' || $message == 'yA') {
                        $out = "Terima kasih jadwalmu sudah terdaftar!";
                    }
                    else {
                        $out = "Silahkan gunakan command /addJadwal kembali ya";
                    }
                    $state = 'idle';
                    break;
                    
                case 'nama':
                    mysqli_query($conn,"UPDATE data SET nama='".$message."' WHERE userId='".$userId."'");
                    $state = 'nrp';
                    $out = "Halo {$message}! Silahkan masukkan NRP kamu";
                    break;
                    
                case 'nrp':
                    mysqli_query($conn,"UPDATE data SET nrp='".$message."' WHERE userId='".$userId."'");
                    $state = 'konfirmasi';
                    $out = "Apakah sudah benar NRP kamu {$message} ? (ya/tidak)";
                    break;
                    
                case 'konfirmasi':
                    if($message == 'ya' || $message == 'Ya' || $message == 'YA' || $message == 'yA') {
                        $out = "Terima kasih datamu sudah kami masukkan!";
                        $state = 'idle';
                    }
                    else {
                        $out = "Silahkan gunakan command /addData atau /start kembali ya";
                        $state = 'nama';
                    }
                    break;
                    
                default:
                    $out = "Halo :D Silahkan daftarkan jadwal kamu dengan command /addJadwal. Jika belum mendaftarkan diri silahkan gunakan command /addData";
                    break;
            }
        break;
}


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
    $query1 = mysqli_query($conn,"SELECT userId FROM jadwal");
    if(mysqli_num_rows($query) == 0) {
        echo "Error: Database kosong";
        return die;
    }
    if(mysqli_num_rows($query1) == 0) {
        echo "Error: Database kosong";
        return die;
    }
    else {
        $query = mysqli_query($conn,"SELECT userId FROM data WHERE userId = '.$userId.')");
        if($query == NULL) {
            $query = mysqli_query($conn,"INSERT INTO data (userId,state) VALUES ('.$userId.','nama')");
        }
        
        $query = mysqli_query($conn,"SELECT userId FROM jadwal WHERE userId = '.$userId.')");
        if($query == NULL) {
            $query = mysqli_query($conn,"INSERT INTO jadwal (userId) VALUES ('.$userId.')");
        }
    }
}

?>
