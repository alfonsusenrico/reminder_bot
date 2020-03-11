<?php
    
//function check data user dalam database
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
            $query = mysqli_query($conn,"INSERT INTO data (userId,state) VALUES ('.$userId.','first')");
        }
    }
}

//function check data jadwal user dalam database
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
        $state = 'first';
    }
    return $state;
}

//function sendMessage
function sendMessage($botToken, $chatId, $message) {
    
    $message = urlencode(utf8_encode($message));
    $url = "https://api.telegram.org/bot".$botToken."/sendMessage?chat_id=".$chatId."&text=".$message;
    file_get_contents($url);
    
}

//function inlineMessage
function inlineMessage($botToken,$chatId,$message,$reply) {

    $message = urlencode(utf8_encode($message));
    $url = "https://api.telegram.org/bot".$botToken."/sendmessage?chat_id=".$chatId."&text=".$message."&reply_markup=".$reply;
    file_get_contents($url);
}

//function getDay
function getDay() {
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
    else { 
        $today = 'minggu';
    }
    return $today;
}

//function getTime
function getTime() {
    GLOBAL $conn;
    GLOBAL $userId;
    $query = mysqli_query($conn,"SELECT time FROM data WHERE userId = '".$userId."'");
    $row = $query->fetch_assoc();
    $data = $row['time'];
    return $data;
}

//function checkFinished event
function checkEvent() {
    GLOBAL $botToken;
    GLOBAL $chatId;
    GLOBAL $conn;
    GLOBAL $today;
    GLOBAL $userId;
    
    //variabel yang diperlukan
    $tanggal = date("Y-m-d");
    $waktu = date("H:i:s");
    
    $query = mysqli_query($conn,"SELECT id,tanggal,selesai FROM jadwal WHERE userId = '".$userId."' AND tipe = 'event' ORDER BY tanggal, mulai");
    $row = $query->fetch_assoc();
    $data = $row['id'];
    if($tanggal == $row['tanggal']) {
        if($waktu > $row['selesai']) {
            
            mysqli_query($conn,"DELETE FROM jadwal WHERE id = '".$data."'");
        }
    }
    else if($tanggal > $row['tanggal']) {
        mysqli_query($conn,"DELETE FROM jadwal WHERE id = '".$data."'");
    }
}

//function getState
function getState() {
    GLOBAL $conn;
    GLOBAL $userId;
    $query = mysqli_query($conn,"SELECT state,nama FROM data WHERE userId = '".$userId."'");
    $row = $query->fetch_assoc();
    $state = $row['state'];
    return $state;
}

//REMINDER
function reminder($time,$today) {
    GLOBAL $botToken;
    GLOBAL $chatId;
    GLOBAL $userId;
    GLOBAL $conn;
    GLOBAL $nama;
    
    if(date("H:i:00") == $time) {
        $query = mysqli_query($conn,"SELECT * FROM jadwal WHERE userId = '".$userId."' AND hari = '".$today."' ORDER BY tanggal, mulai");
        if(mysqli_num_rows($query) == 0) {
            sendMessage($botToken,$chatId,"Selamat pagi {$nama}! Selamat hari {$today}! Sepertinya kamu tidak ada kegiatan yang dijadwalkan untuk hari ini. Selamat berlibur/beristirahat :D");
            $out = "Jika ingin menambahkan jadwal silahkan gunakan command /addJadwal ya";
        }
        else {
            sendMessage($botToken,$chatId,"Selamat pagi {$nama}! Selamat hari {$today}! Di pagi hari ini kami akan menampilkan jadwal yang sudah kamu buat untuk hari ini:");
            while($row = $query->fetch_assoc()) {
            if(strtolower($row['tipe']) == 'rutin') {
                    sendMessage($botToken,$chatId,"Kegiatan  : {$row['jenis']}\nNama      : {$row['nama']}\nMulai       : {$row['mulai']}\nSelesai    : {$row['selesai']}\n");
                }
                else {
                    if($row['tanggal'] == date("Y-m-d")) {
                        sendMessage($botToken,$chatId,"Kegiatan  : {$row['jenis']}\nNama      : {$row['nama']}\nMulai       : {$row['mulai']}\nSelesai    : {$row['selesai']}\n");
                    }
                }
            }
            $out = "Selamat beraktivitas!";
        }  
    }
    return $out;
}
    
?>