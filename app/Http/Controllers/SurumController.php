<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Surum;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Input;
use App\Helpers\Helpers as Helpers;

class SurumController extends Controller
{
   
   
  public function surum()
    {
		/*
		$user = new Surum;
		$user->SurumKodu = '1.1';
		$user->SurumDurumu = false;
		$user->save();
		*/
		$id = Input::get('id');
		$id = Helpers::aes_decrypt($id);
		$surum = Surum::select('SurumDurumu')->where('SurumKodu', '=', $id)->first();
		$surum = Helpers::aes_encrypt($surum);
       return $surum;
    }
	
	
   public function aes(){
    $plaintext = '1.1';
$password = '123456789A';
$method = 'aes-256-cbc';

// Must be exact 32 chars (256 bit)
$password = substr(hash('sha256', $password, true), 0, 32);
echo "Password:" . $password . "\n";

// IV must be exact 16 chars (128 bit)
$iv = "4e5Wa71fYoT7MNEC";

// av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
$encrypted = base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));

// My secret message 1234
$decrypted = openssl_decrypt(base64_decode($encrypted), $method, $password, OPENSSL_RAW_DATA, $iv);

echo 'plaintext=' . $plaintext . "\n";
echo 'cipher=' . $method . "\n";
echo 'encrypted to: ' . $encrypted . "\n";
echo 'decrypted to: ' . $decrypted . "\n\n";

   }
   
}
