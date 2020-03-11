<?php

//connection
require 'connection.php';
require 'function.php';

//token
$botToken = '1099773239:AAEDlg9VuIykF7c3sOQsYU7gPTadzTLre2Y';

//base URL API telegram bot
$website = "https://api.telegram.org/bot".$botToken;

//Mengambil informasi dari webhook, informasi diparse dalam message
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

//inisialisasi variabel
$message = 'cek';
$out = '';

//Mengambil text & id tiap user
$message = $update["message"]["text"];
$chatId = $update["message"]["chat"]["id"];
$userId = $update["message"]["from"]["id"];

//ambil variabel date
date_default_timezone_set("Asia/Jakarta");
$today = getDay();
$date = date("Y-m-d");
$time = getTime();

//Check userId
$id = getId();

//Pengambilan data penting dari user
$query = mysqli_query($conn,"SELECT state,nama FROM data WHERE userId = '".$userId."'");
$row = $query->fetch_assoc();
$state = $row['state'];
$nama = $row['nama'];

//DEBUG
echo " Debug#113_testing_beta_release ";
echo $today;
echo date(" H:i");
//sendMessage($botToken,$chatId,"Waktu remind: {$time}");
//sendMessage($botToken,$chatId,$chatId);
//sendMessage($botToken,$chatId,"Waktu sekarang: {$now}");
//sendMessage($botToken,$chatId,"\nDebug#111.6_beta\nState: {$state}");
//sendMessage($botToken,$chatId,"Debug check id: {$id}");
//sendMessage($botToken,$chatId,"Last message: {$message}");

//initialize keyboard
$keyboard = array(
                array(
                    array(
                        'text' => ""
                    )
                ),
            );

switch($message) {
        
    case '/start':
        checkData();
        $state = setState();
        if($state == 'idle') {
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/addJadwal"
                                ),
                                array(
                                    'text' => "/cekJadwal"
                                )
                            ),
                            array(
                                array(
                                    'text' => "/editData"
                                ),
                                array(
                                    'text' => "/waktu"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        else if($state == 'first') {
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        $out = "Halo :D Terima kasih telah menggunakan kami sebagai bot reminder anda. Silahkan pilih list command yang tersedia";
        break;
    
    case '/adddata':    
    case '/addData':
        $state = setState();
        if($state != 'idle') {
            $out = "Silahkan masukkan nama anda";
            $state = 'nama';
        }
        else {
            $query = mysqli_query($conn,"SELECT nama,nrp FROM data WHERE userId = '".$userId."'");
            $row = $query->fetch_assoc();
            $nama = $row['nama'];
            $nrp = $row['nrp'];
            $out = "Tidak perlu capek-capek mendaftarkan diri lagi karena anda sudah terdaftar :)\nNama anda       : {$nama}\nNRP kamu          : {$nrp}";
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/addJadwal"
                                ),
                                array(
                                    'text' => "/cekJadwal"
                                )
                            ),
                            array(
                                array(
                                    'text' => "/editData"
                                ),
                                array(
                                    'text' => "/waktu"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        ); 
        }
        break;
    
    case '/editdata':
    case '/editData':
        $out = "Silahkan masukkan nama";
        $state = 'nama';
        break;
    
    case '/addjadwal':
    case '/addJadwal':
        checkJadwal();
        if($state == 'first' || $state == 'nama' || $state == 'nrp') {
            $out = "Maaf data diri anda tidak ditemukan. Silahkan daftarkan data diri dulu ya";
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        else {
            $out = "Kegiatan rutin atau event tertentu (Rutin / Event)";
            $state = 'tipekegiatan';
            //inline keyboard
            $keyboard = array(array("Rutin","Event"));
        }
        break;
    
    case '/cekjadwal':
    case '/cekJadwal':
        $state = setState();
        //checkEvent();
        if($state == 'first' || $state == 'nama' || $state == 'nrp') {
            $out = "Maaf data diri anda tidak ditemukan. Silahkan daftarkan data diri dulu ya";
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        else {
        $query = mysqli_query($conn,"SELECT * FROM jadwal WHERE userId = '".$userId."' AND hari = '".$today."' ORDER BY tanggal, mulai");
        if(mysqli_num_rows($query) == 0) {
            sendMessage($botToken,$chatId,"Halo {$nama}! Sepertinya tidak ada kegiatan yang dijadwalkan untuk hari ini. Selamat beristirahat :D");
            $out = ("Jika ingin menambahkan jadwal silahkan gunakan command /addJadwal ya");
        }
        else {
            sendMessage($botToken,$chatId,"Halo {$nama}! Berikut seluruh kegiatan yang telah dijadwalkan untuk hari {$today} ini:");
            while($row = $query->fetch_assoc()) {
                if(strtolower($row['tipe']) == 'rutin') {
                    sendMessage($botToken,$chatId,"Kegiatan  : {$row['jenis']}\nNama      : {$row['nama']}\nMulai       : {$row['mulai']}\nSelesai    : {$row['selesai']}\n");
                }
                else {
                    if($row['tanggal'] == $date) {
                        sendMessage($botToken,$chatId,"Kegiatan  : {$row['jenis']}\nNama      : {$row['nama']}\nMulai       : {$row['mulai']}\nSelesai    : {$row['selesai']}\n");
                    }
                }
            }
            $out = ("Selamat beraktivitas!");
        }
        $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/addJadwal"
                                ),
                                array(
                                    'text' => "/cekJadwal"
                                )
                            ),
                            array(
                                array(
                                    'text' => "/editData"
                                ),
                                array(
                                    'text' => "/waktu"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        break;
    
    case '/waktu':
        if($state == 'first' || $state == 'nama' || $state == 'nrp') {
            $out = "Maaf data diri anda tidak ditemukan. Silahkan daftarkan data diri dulu ya";
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        else {
            $state = 'waktu';
            $out = "Silahkan input waktu untuk reminder (HH:MM)";
        }
        break;
    
    case '/aboutus':
    case '/aboutUs':
        $out = "Terima kasih sudah mau menggunakan bot kami. Bot ini dibuat oleh:\nAlfonsus Enrico @enrico06\nMichael Wida P. @michaelpramast\nVito Varian L. @vito_laman\nMahasiswa Teknik Informatika Petra.\n";
        if($state == 'first' || $state == 'nama' || $state == 'nrp') {
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        else {
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/addJadwal"
                                ),
                                array(
                                    'text' => "/cekJadwal"
                                )
                            ),
                            array(
                                array(
                                    'text' => "/editData"
                                ),
                                array(
                                    'text' => "/waktu"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        break;
        
    case '/remind':
        if($state == 'first' || $state == 'nama' || $state == 'nrp') {
            $out = "Maaf data diri anda tidak ditemukan. Silahkan daftarkan data diri dulu ya";
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        else {
            $out = reminder($time,$today);
            $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/addJadwal"
                                ),
                                array(
                                    'text' => "/cekJadwal"
                                )
                            ),
                            array(
                                array(
                                    'text' => "/editData"
                                ),
                                array(
                                    'text' => "/waktu"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
        }
        break;
        
    default:
            switch($state) {
                
                case 'waktu':
                    mysqli_query($conn,"UPDATE data set time='".$message."' WHERE userId='".$userId."'");
                    $out = "Untuk mengaktifkan fitur remind, silahkan ikuti step dibawah ini: \n1. Ketikkan /remind dan JANGAN KIRIM DULU\n2. HOLD / TAHAN tombol send message hingga keluar menu kecil\n3. Pilih Schedule message\n4. Pilih Send at specific date...\n5. Atur sesuai waktu remind yang telah di set sebelumnya ({$message})\nSteps diatas HARUS dilakukan setiap hari.\nFitur ini masih dalam tahap pengembangan dan akan terus diperbaiki untuk kedepannya.\n Terima kasih.";
                    sendMessage($botToken,$chatId,"Waktu reminder telah di set ke jam {$message}.");
                    sendMessage($botToken,$chatId,"!! IMPORTANT NOTE !!");
                    $state = 'idle';
                    $keyboard = array(
                            array(
                                array(
                                    'text' => "/addData"
                                ),
                                array(
                                    'text' => "/addJadwal"
                                ),
                                array(
                                    'text' => "/cekJadwal"
                                )
                            ),
                            array(
                                array(
                                    'text' => "/editData"
                                ),
                                array(
                                    'text' => "/waktu"
                                ),
                                array(
                                    'text' => "/aboutUs"
                                )
                            ),
                        );
                    break;
                
                case 'tipekegiatan':
                    mysqli_query($conn,"UPDATE jadwal set tipe='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'jeniskegiatan';
                    $out = "Jenis kegiatan yang ingin dijadwalkan (kelas / event lain (ex. panitia, ibadah, dll) )";
                    break;
                
                case 'jeniskegiatan':
                    mysqli_query($conn,"UPDATE jadwal set jenis='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'namakegiatan';
                    $out = "Silahkan masukkan nama kegiatan ({$message})";
                    break;
                
                case 'namakegiatan':
                    mysqli_query($conn,"UPDATE jadwal set nama='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'mulai';
                    $out = "Pukul berapa kegiatan dimulai (HH:MM)";
                    break;
                
                case 'mulai':
                    mysqli_query($conn,"UPDATE jadwal set mulai='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'selesai';
                    $out = "Pukul berapa kegiatan selesai (HH:MM) (jika tidak tahu bisa diisi 00:00)";
                    break;
                    
                case 'selesai':
                    mysqli_query($conn,"UPDATE jadwal set selesai='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    $state = 'hari';
                    $out = "Silahkan pilih hari pelaksanaan kegiatan";
                    $keyboard = array(
                                            array(
                                                array(
                                                    'text' => "Senin"
                                                ),
                                                array(
                                                'text' => "Selasa"
                                                ),
                                            ),
                                            array(
                                                array(
                                                    'text' => "Rabu"
                                                ),
                                                array(
                                                    'text' => "Kamis"
                                                ),
                                            ),
                                            array(
                                                array(
                                                'text' => "Jumat"
                                                ),
                                                array(
                                                'text' => "Sabtu"
                                                ),
                                                array(
                                                'text' => "Minggu"
                                                )
                                            )
                                        );
                    break;
                
                case 'hari':
                    $message = strtolower($message);
                    mysqli_query($conn,"UPDATE jadwal set hari='".$message."' WHERE userId='".$userId."' AND id='".$id."'");
                    
                    $query = mysqli_query($conn,"SELECT tipe FROM jadwal WHERE userId = '".$userId."' AND id='".$id."'");
                    $row = $query->fetch_assoc();
                    $tipe = strtolower($row['tipe']);

                    if($tipe == 'event') {
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
                        $out = "Nama kegiatan   : {$nama}\nJenis kegiatan    : {$jenis}\nHari                      : {$hari}\nJam mulai           : {$mulai}\nJam selesai         : {$selesai}\nApakah sudah benar?";
                        $keyboard = array(array("Ya","Tidak"));
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
                    
                    $out = "Nama kegiatan   : {$nama}\nJenis kegiatan    : {$jenis}\nHari                       : {$hari}\nTanggal                : {$tanggal}\nJam mulai            : {$mulai}\nJam selesai         : {$selesai}\nApakah sudah benar?";
                    $state = 'terdaftar';
                    $keyboard = array(array("Ya","Tidak"));
                    break;
                    
                case 'terdaftar':
                    if(strtolower($message) == 'ya') {
                        $out = "Terima kasih jadwal anda sudah terdaftar!";
                    }
                    else {
                        mysqli_query($conn,"DELETE FROM jadwal WHERE id='".$id."'");
                        $out = "Silahkan gunakan command /addJadwal kembali ya";
                    }
                    $keyboard = array(
                                            array(
                                                array(
                                                    'text' => "/addData"
                                                ),
                                                array(
                                                'text' => "/addJadwal"
                                                ),
                                                array(
                                                'text' => "/cekJadwal"
                                                )
                                            ),
                                            array(
                                                array(
                                                    'text' => "/editData"
                                                ),
                                                array(
                                                    'text' => "/waktu"
                                                ),
                                                array(
                                                    'text' => "/aboutUs"
                                                 )
                                            ),
                                        );
                    $state = 'idle';
                    break;
                    
                case 'nama':
                    mysqli_query($conn,"UPDATE data SET nama='".$message."' WHERE userId='".$userId."'");
                    $state = 'nrp';
                    $out = "Halo {$message}! Silahkan masukkan NRP anda";
                    break;
                    
                case 'nrp':
                    mysqli_query($conn,"UPDATE data SET nrp='".$message."' WHERE userId='".$userId."'");
                    $state = 'konfirmasi';
                    $out = "Apakah sudah benar NRP anda {$message} ? (Ya/Tidak)";
                    $keyboard = array(array("Ya","Tidak"));
                    break;
                    
                case 'konfirmasi':
                    if(strtolower($message == 'ya')) {
                        $out = "Terima kasih data anda sudah saya daftarkan!";
                        $state = 'idle';
                        $keyboard = array(
                    array(
                        array(
                            'text' => "/addData"
                        ),
                        array(
                            'text' => "/addJadwal"
                        ),
                        array(
                            'text' => "/cekJadwal"
                        )
                    ),
                    array(
                        array(
                            'text' => "/editData"
                        ),
                        array(
                            'text' => "/waktu"
                        ),
                        array(
                            'text' => "/aboutUs"
                        )
                    ),
                );
                    }
                    else {
                        mysqli_query($conn,"DELETE FROM data WHERE userId='".$userId."'");
                        $out = "Silahkan gunakan command /addData atau /start kembali ya";
                        $state = 'nama';
                        $keyboard = array(
                    array(
                        array(
                            'text' => "/addData"
                        ),
                        array(
                            'text' => "/aboutUs"
                        )
                    ),
                );
                    }
                    break;
                    default:
                        echo "Default";
                        break;
                }
                break;
            }
            
//sendMessage($botToken,$chatId,$out);
mysqli_query($conn,"UPDATE data SET state='".$state."' WHERE userId ='".$userId."'");
$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
$reply = json_encode($resp);
inlineMessage($botToken,$chatId,$out,$reply);

?>