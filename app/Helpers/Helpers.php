<?php


namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class Helpers {

    // something here
    static function aes_encrypt($text){
	$plaintext = $text;
	$password = '7b1350ec5905e7055971c7e7c5639129';
	$method = 'aes-256-cbc';

	// Must be exact 32 chars (256 bit)
	$password = substr(hash('sha256', $password, true), 0, 32);
	//echo "Password:" . $password . "\n";

	// IV must be exact 16 chars (128 bit)
	$iv = "4r5Wa26fYoZBMNEC";

// av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
	$encrypted = base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
		
        return $encrypted;
    }
	
	static function aes_decrypt($text){
	$plaintext = $text;
	$password = '7b1350ec5905e7055971c7e7c5639129';
	$method = 'aes-256-cbc';
	

	// Must be exact 32 chars (256 bit)
	$password = substr(hash('sha256', $password, true), 0, 32);
	//echo "Password:" . $password . "\n";

	// IV must be exact 16 chars (128 bit)
	$iv = "4r5Wa26fYoZBMNEC";

	// av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
	$decrypted = openssl_decrypt(base64_decode($plaintext), $method, $password, OPENSSL_RAW_DATA, $iv);
	
        return $decrypted;
    }
	
	static function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

	static function DogrulamaKodu() {
			
			$kod = rand(10000000,99999999);
		
		return $kod;
}


    static function mailgonder($mail,$baslik,$mesaj,$sablon){
        $data = ['baslik' =>$baslik,'mesaj'=>$mesaj];
	Mail::send($sablon, $data, function ($message) use ($mail,$baslik) {
		$mailadresi = $mail;
        $message->from('noreply@passid.web.tr', 'Pass ID');

        $message->to($mailadresi)->subject($baslik);

    });
	
	//dd('Mail Send Successfully');
	}
	
	
	static function GeriBildirimId() {
	    $rnd = rand(0,25);
	    $randomsayi = rand(100000,999999);	    
	    $parametre = array("QW", "ER", "TY", "UI","OP","AS","DF","GH","JK","LZ","XC","VB","NM","MN","BV","CX","ZL","KJ","HG","FD","SA","PO","IU","YT","RE","WQ");
	    $kod = $parametre[$rnd]."-".$randomsayi;
	    return $kod;
	}
	
}
?>