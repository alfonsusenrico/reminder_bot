<?php

//connection
require 'connection.php';

//token
$botToken = "";

//base URL API telegram bot
$website = "https://api.telegram.org/bot".$botToken;

//Mengambil informasi dari webhook, informasi diparse dalam message
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

//Mengambil text & id tiap user
$message = $update["message"]["text"];
$chatId = $update["message"]["chat"]["id"];
$userId = $update["message"]["from"]["id"];

//inisialisasi variabel tanggal
$tanggal = date("d");
$today = strtolower(date("l"));

if($today == 'monday') {
    $today = 'senin';
}
else if ($today == 'tuesday') {
    $today = 'selasa';
}
else if ($today == 'wednesday') {
    $today = 'rabu';
}
else if ($today == 'thursday') {
    $today = 'kamis';
}
else if ($today == 'friday') {
    $today = 'jumat';
}
else if ($today == 'saturday') {
    $today = 'sabtu';
}
else if ($today == 'sunday') {
    $today = 'minggu';
}

//inisialisasi variabel
$out = '';
$step = '';

//Check userId & set state berdasarkan data
checkData();
$id = '';
$id = getId();

//Pengambilan data penting dari data user
$query = mysqli_query($conn,"SELECT state,nama FROM data WHERE userId = '".$userId."'");
$row = $query->fetch_assoc();
$state = $row['state'];
$nama = $row['nama'];

//#DEBUG# check message
echo "Debug#73_beta_release";
    //sendMessage($botToken,$chatId,"\nDebug#73_beta\nState: {$state}");
    sendMessage($botToken,$chatId,"Debug check id: {$id}");
    
switch($message) {
    
    case '/cekJadwal':
        sendMessage($botToken,$chatId,"Halo {$nama}! Berikut seluruh kegiatan yang telah kamu jadwalkan di hari {$today} ini:");
        $query = mysqli_query($conn,"SELECT * FROM jadwal WHERE userId = '".$userId."' AND hari = '".$today."' ORDER BY mulai");
        while($row = $query->fetch_assoc()) {
            sendMessage($botToken,$chatId,"Kegiatan : {$row['jenis']}\nNama      : {$row['nama']}\nMulai       : {$row['mulai']}\nSelesai    : {$row['selesai']}\n");
        }
        sendMessage($botToken,$chatId,"Selamat beraktivitas!");
        break;
        
    case '/start':
        $state = setState();
        $out = "Halo :D Terima kasih telah menggunakan kami sebagai bot reminder kalian. Berikut beberapa list command yang bisa kalian gunakan:\n1. /addData\n2. /addJadwal\n3. /aboutUs";
        break;
        
    case '/addData':
        $state = setState();
        if($state != idle) {
            $out = "Silahkan masukkan nama kamu";
        }
        else {
            $query = mysqli_query($conn,"SELECT nama,nrp FROM data WHERE userId = '".$userId."'");
            $row = $query->fetch_assoc();
            $nama = $row['nama'];
            $nrp = $row['nrp'];
            $out = "Kamu tidak perlu capek-capek mendaftarkan diri lagi karena kamu sudah terdaftar :)\nNama kamu       : {$nama}\nNRP kamu          : {$nrp}";
        }
        break;
    
    case '/addJadwal':
        checkJadwal();
        $out = "Kegiatan rutin atau sekali";
        $state = 'tipekegiatan';
        break;
    
    case '/aboutUs':
        sendMessage($botToken,$chatId,"Terima kasih sudah mau menggunakan bot kami. Bot ini dibuat oleh:\nAlfonsus Enrico @enrico06\nMichael Wida P. @michaelpramas\nVito Varian L. @vito_laman\nMahasiswa biasa saja di Informatika Petra.\n");
        break;
        
    default:
            
            switch($state) {
                
                case 'tipekegiatan':
                    mysqli_query($conn,"UPDATE jadwal set tipe='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'jeniskegiatan';
                    $out = "Jenis kegiatan yang ingin dijadwalkan (kelas / lain)";
                    break;
                
                case 'jeniskegiatan':
                    mysqli_query($conn,"UPDATE jadwal set jenis='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'namakegiatan';
                    $out = "Silahkan masukkan nama kegiatan";
                    break;
                
                case 'namakegiatan':
                    mysqli_query($conn,"UPDATE jadwal set nama='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'mulai';
                    $out = "Pukul berapa kegiatanmu dimulai (HH:MM)";
                    break;
                
                case 'mulai':
                    mysqli_query($conn,"UPDATE jadwal set mulai='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'selesai';
                    $out = "Pukul berapa kegiatanmu selesai (HH:MM) (jika tidak tahu bisa diisi 00:00)";
                    break;
                    
                case 'selesai':
                    mysqli_query($conn,"UPDATE jadwal set selesai='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'hari';
                    $out = "Silahkan masukkan hari";
                    break;
                
                case 'hari':
                    mysqli_query($conn,"UPDATE jadwal set hari='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    
                    $query = mysqli_query($conn,"SELECT tipe FROM jadwal WHERE userId = '".$userId."' AND id='".$id."'");
                    $row = $query->fetch_assoc();
                    $tipe = $row['tipe'];

                    if($tipe == 'sekali' || $tipe == 'Sekali' || $tipe == 'SEKALI') {
                        $state = 'konfirmasi2';
                        $out = "Silahkan masukkan tanggal pelaksanaan kegiatan (YYYY:MM:DD)";
                    }
                    else {
                        $query = mysqli_query($conn,"SELECT * FROM jadwal WHERE userId='".$userId."' AND id='".$id."'");
                        $row = $query->fetch_assoc();
                    
                        //semua data jadwal
                        $tipe = $row['tipe'];
                        $jenis = $row['jenis'];
                        $nama = $row['nama'];
                        $hari = $row['hari'];
                        $tanggal = $row['tanggal'];
                        $mulai = $row['mulai'];
                        $selesai = $row['selesai'];
                        $state = 'terdaftar';        
                        $out = "Nama kegiatan   : {$nama}\nJenis kegiatan    : {$jenis}\nHari                      : {$hari}\nJam mulai           : {$mulai}\nJam selesai         : {$selesai}\nApakah sudah benar? (ya/tidak)";
                    }
                    break;
                    
                case 'konfirmasi2':
                    mysqli_query($conn,"UPDATE jadwal set tanggal='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $query = mysqli_query($conn,"SELECT * FROM jadwal WHERE userId='".$userId."' AND id='".$id."'");
                    $row = $query->fetch_assoc();
                    
                    //semua data jadwal
                    $tipe = $row['tipe'];
                    $jenis = $row['jenis'];
                    $nama = $row['nama'];
                    $hari = $row['hari'];
                    $tanggal = $row['tanggal'];
                    $mulai = $row['mulai'];
                    $selesai = $row['selesai'];
                    
                    $out = "Nama kegiatan   : {$nama}\nJenis kegiatan    : {$jenis}\nHari                       : {$hari}\nTanggal                : {$tanggal}\nJam mulai            : {$mulai}\nJam selesai         : {$selesai}\nApakah sudah benar? (ya/tidak)";
                    $state = 'terdaftar';
                    break;
                    
                case 'terdaftar':
                    if($message == 'ya' || $message == 'Ya' || $message == 'YA' || $message == 'yA') {
                        $out = "Terima kasih jadwalmu sudah terdaftar!";
                    }
                    else {
                        mysqli_query($conn,"DELETE FROM jadwal WHERE id='".$id."'");
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
                        mysqli_query($conn,"DELETE FROM data WHERE userId='".$userId."'");
                        $out = "Silahkan gunakan command /addData atau /start kembali ya";
                        $state = 'nama';
                    }
                    break;
                    
                default:
                    $state = setState();
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
function checkData() {
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

//function checkJadwal
function checkJadwal() {
    GLOBAL $conn;
    GLOBAL $userId;
    $query = mysqli_query($conn,"SELECT userId FROM jadwal");
    if(mysqli_num_rows($query) == 0) {
        echo "Error: Database kosong";
        return die;
    }
    else {
        $query = mysqli_query($conn,"SELECT userId FROM jadwal WHERE userId = '.$userId.')");
        if($query == NULL) {
            $query = mysqli_query($conn,"INSERT INTO jadwal (userId) VALUES ('.$userId.')");
        }
    }
}

//function get Id
function getId() {
    GLOBAL $conn;
    GLOBAL $userId;
    $query = mysqli_query($conn,"SELECT id FROM jadwal WHERE userId = '".$userId."' ORDER BY id DESC");
    $row = $query->fetch_assoc();
    $id = $row['id'];
    return $id;
}
    
//function set state
function setState() {
    GLOBAL $conn;
    GLOBAL $userId;
    
    $query = mysqli_query($conn,"SELECT nrp FROM data WHERE userId = '".$userId."'");
    $row = $query->fetch_assoc();
    $data = $row['nrp'];
    if($data != NULL) {
        $state = 'idle';
    }
    else {
        $state = 'nama';
    }
    return $state;
}

?>
