<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Kullanicilar;
use App\Sifreler;
use App\Dogrulamakodlari;
use App\Mail;
use App\Oturumlar;
use App\Cihazlar;
use App\Girisler;
use App\Geribildirim;
use DateTime;
use DateTimeZone;
use App\Helpers\Helpers as Helpers;

class KullanicilarController extends Controller
{
    public function kontrol()
    {
		
		$mailadresi = Helpers::aes_decrypt(Input::get('zn5'));		
		if (filter_var($mailadresi, FILTER_VALIDATE_EMAIL)) {
		//$mailadresi = aes_decrypt($mailadresi);
		$kullanicilar = Kullanicilar::select('id')->where('mail', '=', $mailadresi)->first();
		
		if($kullanicilar == null)
		{
		$kullanici = new Kullanicilar;
		$kullanici->mail = $mailadresi;		
		$kullanici->save();
		$id = $kullanici->id;
		}else{
		$id = $kullanicilar->id;
		}
		
		$sifreler = Sifreler::select('id')->where('kullanici_id', '=', $id)->first();
		
		if($sifreler == null)
		{
		$sifre = new Sifreler;
		$sifre->kullanici_id = $id;	
		$sifre->sifre = Helpers::gen_uuid();		
		$sifre->save();
		}
		
		
		$tarih = time();		
		$dogrulamakodlari = Dogrulamakodlari::select('dogrulamakodu')->where('kullanici_id', '=', $id)->where('sonkullanimtarihi', '>', $tarih)->where('durum', '=', false)->first();		
		$tarih = time()+60*60;
		if($dogrulamakodlari == null){
		$dogrulama = new Dogrulamakodlari;
		$dogrulama->kullanici_id = $id;	
		$dogrulama->dogrulamakodu = Helpers::DogrulamaKodu();	
		$dogrulama->durum = false;	
		$dogrulama->sonkullanimtarihi = $tarih;
		$dogrulama->save();
		$kod = $dogrulama->dogrulamakodu;
		}else{
		$kod = $dogrulamakodlari->dogrulamakodu;	
		}
		
		
		$mail = new Mail;
		$mail->kullanici_id = $id;	
		$mail->mailadresi = $mailadresi;
		$mail->baslik = "Pass ID Code";		
		$mail->mesaj = $kod;
		$mail->sablon = "mail.postmail";
		$mail->durum = false;		
		$mail->save();
		
		
		$sonuc = array(
			'durum' => Helpers::aes_encrypt("true"),							
			);	
		
       return $sonuc;
		}else{
			$sonuc = array(
			'durum' => Helpers::aes_encrypt("false"),							
			);
			return $sonuc;
		}
    }
	
	
	
	public function dogrulama()
	{
		$mailadresi = Helpers::aes_decrypt(Input::get('zn5'));
		$kod = (int) Helpers::aes_decrypt(Input::get('zn8'));
		
		$kullanicilar = Kullanicilar::where('mail', '=', $mailadresi)->first();
		
		if($kullanicilar == null)
		{
		
		}else{
		$id = $kullanicilar->id;
		$tarih = time();
		$dogrulamakodlari = Dogrulamakodlari::where('kullanici_id', '=', $id)->where('sonkullanimtarihi', '>', $tarih)->where('durum', '=', false)->where('dogrulamakodu', '=', $kod)->first();
		if($dogrulamakodlari != null)
		{
			if($dogrulamakodlari->kullanici_id == $id){
			$sifreler = Sifreler::where('kullanici_id', '=', $id)->first();
			$sifre = $sifreler->sifre;
			
			$oturumkontrol = Oturumlar::where('kullanici_id', '=', $id)->where('sonkullanimtarihi', '>', $tarih)->first();
			if($oturumkontrol == null)
			{
			$tarih = time()+86400*180;
			$oturum = new Oturumlar;
			$oturum->kullanici_id = $id;	
			$oturum->api_anahtar = Helpers::gen_uuid();
			$oturum->sonkullanimtarihi = $tarih;			
			$oturum->save();
			$oturum_api = $oturum->api_anahtar;
			}else{
			$oturum_api = $oturumkontrol->api_anahtar;
			}
			
			
			$sonuc = array(
			'anahtar' => Helpers::aes_encrypt(md5($sifre)),	
			'oturum' => Helpers::aes_encrypt($oturum_api),			
			);	

				$dogrulamaupdate = Dogrulamakodlari::find($dogrulamakodlari->id);
				$dogrulamaupdate->durum = true;
				$dogrulamaupdate->save();
					
			return $sonuc;
			}
		}else{
			$sonuc = array(
			'anahtar' => Helpers::aes_encrypt("null"),
			'oturum' => Helpers::aes_encrypt("null"),			
			);
			
			return $sonuc;
		}
		
		}
		
	}
	
	public function oturum()
	{
		$tarih = time();
		$anahtar = Helpers::aes_decrypt(Input::get('zn10'));
		$oturumkontrol = Oturumlar::where('api_anahtar', '=', $anahtar)->where('sonkullanimtarihi', '>', $tarih)->first();
		if($oturumkontrol != null)
		{
			$sonuc = array(
			'oturumkontrol' => Helpers::aes_encrypt("true"),			
			);
			return $sonuc;
		}else{
			$sonuc = array(
			'oturumkontrol' => Helpers::aes_encrypt("false"),			
			);
			return $sonuc;
		}
		
		
	}
	
	public function cihazlardancikisyap()
	{
		$tarih = time();
		$anahtar = Helpers::aes_decrypt(Input::get('zn12'));
		$oturumkontrol = Oturumlar::where('api_anahtar', '=', $anahtar)->where('sonkullanimtarihi', '>', $tarih)->get();
		if($oturumkontrol != null)
		{
			foreach($oturumkontrol as $oturum) {
				$oturumupdate = Oturumlar::find($oturum->id);
				$oturumupdate->sonkullanimtarihi = $tarih;
				$oturumupdate->save();   
			}
			
			$sonuc = array(
			'oturumdurumu' => Helpers::aes_encrypt("true"),			
			);
			return $sonuc;
		}else{
			$sonuc = array(
			'oturumdurumu' => Helpers::aes_encrypt("false"),			
			);
			return $sonuc;
		}
		
		
	}
	
	
	public function cihaz(Request $request)
	{	   
	    $anahtar = Helpers::aes_decrypt($request->input('oturum'));
	    $package_name = Helpers::aes_decrypt($request->input('package_name'));
	    $app_name = Helpers::aes_decrypt($request->input('app_name'));
	    $app_version = Helpers::aes_decrypt($request->input('app_version'));
	    $app_version_code = Helpers::aes_decrypt($request->input('app_version_code'));
	    
	    
	    $manufacturer = Helpers::aes_decrypt($request->input('manufacturer'));
	    $model = Helpers::aes_decrypt($request->input('model'));
	    $os_version = Helpers::aes_decrypt($request->input('os_version'));
	    $product = Helpers::aes_decrypt($request->input('product'));
	    $device = Helpers::aes_decrypt($request->input('device'));
	    $board = Helpers::aes_decrypt($request->input('board'));
	    $hardware = Helpers::aes_decrypt($request->input('hardware'));	    
	    $is_device_rooted = Helpers::aes_decrypt($request->input('is_device_rooted'));
	    
	    $sim_country = Helpers::aes_decrypt($request->input('sim_country'));
	    $sim_carrier = Helpers::aes_decrypt($request->input('sim_carrier'));
	    
	    $is_nfc_present = Helpers::aes_decrypt($request->input('is_nfc_present'));
	    $is_nfc_enabled = Helpers::aes_decrypt($request->input('is_nfc_enabled'));
	    
	    $display_resolution = Helpers::aes_decrypt($request->input('display_resolution'));
	    
	    $PseudoID = Helpers::aes_decrypt($request->input('PseudoID'));
	    
	    
	    $oturumkontrol = Oturumlar::where('api_anahtar', '=', $anahtar)->first();
	    if($oturumkontrol != null)
	    {
	        $cihazkontrol = Cihazlar::where('PseudoID', '=', $PseudoID)->where('kullanici_id', '=', $oturumkontrol->kullanici_id)->first();
	        if($cihazkontrol == null)
	        {
	        $cihaz = new Cihazlar;
	        $cihaz->kullanici_id = $oturumkontrol->kullanici_id;
	        $cihaz->package_name = $package_name;
	        $cihaz->app_name = $app_name;
	        $cihaz->app_version = $app_version;
	        $cihaz->app_version_code = $app_version_code;
	        
	        $cihaz->manufacturer = $manufacturer;
	        $cihaz->model = $model;
	        $cihaz->os_version = $os_version;
	        $cihaz->product = $product;
	        $cihaz->device = $device;
	        $cihaz->board = $board;
	        $cihaz->hardware = $hardware;	        
	        $cihaz->is_device_rooted = $is_device_rooted;
	        
	        $cihaz->sim_country = $sim_country;
	        $cihaz->sim_carrier = $sim_carrier;
	        
	        $cihaz->is_nfc_present = $is_nfc_present;
	        $cihaz->is_nfc_enabled = $is_nfc_enabled;
	        
	        $cihaz->display_resolution = $display_resolution;
	        
	        $cihaz->PseudoID = $PseudoID;
	        
	        $cihaz->save();
	        }else{
	            
	            $cihaz = Cihazlar::find($cihazkontrol->id);
	            $cihaz->kullanici_id = $oturumkontrol->kullanici_id;
	            $cihaz->package_name = $package_name;
	            $cihaz->app_name = $app_name;
	            $cihaz->app_version = $app_version;
	            $cihaz->app_version_code = $app_version_code;
	            
	            $cihaz->manufacturer = $manufacturer;
	            $cihaz->model = $model;
	            $cihaz->os_version = $os_version;
	            $cihaz->product = $product;
	            $cihaz->device = $device;
	            $cihaz->board = $board;
	            $cihaz->hardware = $hardware;
	            $cihaz->is_device_rooted = $is_device_rooted;
	            
	            $cihaz->sim_country = $sim_country;
	            $cihaz->sim_carrier = $sim_carrier;
	            
	            $cihaz->is_nfc_present = $is_nfc_present;
	            $cihaz->is_nfc_enabled = $is_nfc_enabled;
	            
	            $cihaz->display_resolution = $display_resolution;
	            
	            $cihaz->PseudoID = $PseudoID;
	            
	            $cihaz->save();
	            
	        }
	    }else{
	       
	    }
	    
	    
	}
	
	
	public function giris(Request $request)
	{	    
	    $anahtar = Helpers::aes_decrypt($request->input('oturum'));
	    $oturumanahtari = Helpers::aes_decrypt($request->input('oturumanahtari'));
	    $random = Helpers::aes_decrypt($request->input('random'));
	    $PseudoID = Helpers::aes_decrypt($request->input('PseudoID'));
	    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
	    $oturumkontrol = Oturumlar::where('api_anahtar', '=', $anahtar)->first();
	    if($oturumkontrol != null)
	    {	       
	        $giriskontrol = Girisler::where('PseudoID', '=', $PseudoID)->where('kullanici_id', '=', $oturumkontrol->kullanici_id)->where('oturumanahtari', '=', $oturumanahtari)->first();
	        if($giriskontrol == null)
	        {
	            $giris = new Girisler;
	            $giris->kullanici_id = $oturumkontrol->kullanici_id;
	            $giris->oturumanahtari = $oturumanahtari;
	            $giris->random = $random;
	            $giris->PseudoID = $PseudoID;
	            $giris->ip = $ip;
	            $giris->save();
	            
	        }else{
	            $giris = Girisler::find($giriskontrol->id);
	            $giris->kullanici_id = $oturumkontrol->kullanici_id;
	            $giris->oturumanahtari = $oturumanahtari;
	            $giris->random = $random;
	            $giris->PseudoID = $PseudoID;
	            $giris->ip = $ip;
	            $giris->save();
	        }
	      
	    }else{
	        
	    }
	    
	    
	}
	
	
	
	
	public function geribildirim(Request $request)
	{
	    $anahtar = Helpers::aes_decrypt($request->input('oturum'));
	    $mesaj = strip_tags(Helpers::aes_decrypt($request->input('mesaj')));
	    $PseudoID = Helpers::aes_decrypt($request->input('PseudoID'));
	    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
	    
	    $tarihx = gmdate("Y-m-d", time());
	    $tarihx2 = $tarihx." 00:00:00";	   
	    $tarih = DateTime::createFromFormat('Y-m-d H:i:s', $tarihx2);	    
	    
	    $oturumkontrol = Oturumlar::where('api_anahtar', '=', $anahtar)->first();
	    if($oturumkontrol != null)
	    {	     
	        $geribildirimkontrol = Geribildirim::where('created_at', '>', $tarih)->where('kullanici_id', '=', $oturumkontrol->kullanici_id)->count();
	        if($geribildirimkontrol < 2)
	        {
	            
	            if(strlen($mesaj) >= 8  && strlen($mesaj) <= 500)
	            {
	            
	            $geribildirim = new Geribildirim;
	            $geribildirim->kullanici_id = $oturumkontrol->kullanici_id;
	            $geribildirim->kisaid = Helpers::GeriBildirimId();
	            $geribildirim->mesaj = $mesaj;
	            $geribildirim->PseudoID = $PseudoID;
	            $geribildirim->ip = $ip;
	            $geribildirim->kategori = 1;
	            $geribildirim->okundu = false;	            
	            $geribildirim->save();
	            $sonuc = array(
	                'kod' => Helpers::aes_encrypt("1"),
	            );
	            return $sonuc;
	            }else{
	                $sonuc = array(
	                    'kod' => Helpers::aes_encrypt("x2"),
	                );
	                return $sonuc;
	            }
	        }else{
	            
	            $sonuc = array(
	                'kod' => Helpers::aes_encrypt("x0"),
	            );
	            return $sonuc;
	            
	        }
	            
	    }else{
	        
	    }
	    
	    
	}
	
	public function GoogleKontrol(Request $request)
	{
	    $mailadresi = Helpers::aes_decrypt(Input::get('zn5'));
	    if (filter_var($mailadresi, FILTER_VALIDATE_EMAIL)) {
	        //$mailadresi = aes_decrypt($mailadresi);
	        $kullanicilar = Kullanicilar::select('id')->where('mail', '=', $mailadresi)->first();
	        
	        if($kullanicilar == null)
	        {
	            $kullanici = new Kullanicilar;
	            $kullanici->mail = $mailadresi;
	            $kullanici->save();
	            $id = $kullanici->id;
	            
	            $sifreler = Sifreler::select('id')->where('kullanici_id', '=', $id)->first();
	            
	            if($sifreler == null)
	            {
	                $sifre = new Sifreler;
	                $sifre->kullanici_id = $id;
	                $sifre->sifre = Helpers::gen_uuid();
	                $sifre->save();
	                $sifre = $sifre->sifre;
	            }else{
	                $sifre = $sifreler->sifre;
	            }
	            
	            $tarih = time();
	            $oturumkontrol = Oturumlar::where('kullanici_id', '=', $id)->where('sonkullanimtarihi', '>', $tarih)->first();
	            if($oturumkontrol == null)
	            {
	                $tarih = time()+86400*180;
	                $oturum = new Oturumlar;
	                $oturum->kullanici_id = $id;
	                $oturum->api_anahtar = Helpers::gen_uuid();
	                $oturum->sonkullanimtarihi = $tarih;
	                $oturum->save();
	                $oturum_api = $oturum->api_anahtar;
	            }else{
	                $oturum_api = $oturumkontrol->api_anahtar;
	            }
	            
	            
	            $sonuc = array(
	                'durum' => Helpers::aes_encrypt("true"),
	                'anahtar' => Helpers::aes_encrypt(md5($sifre)),
	                'oturum' => Helpers::aes_encrypt($oturum_api),
	            );	            
	            
	            
	            return $sonuc;
	            
	        }else{
	            $id = $kullanicilar->id;
	            
	            $sifreler = Sifreler::select('id')->where('kullanici_id', '=', $id)->first();
	            
	            if($sifreler == null)
	            {
	                $sifre = new Sifreler;
	                $sifre->kullanici_id = $id;
	                $sifre->sifre = Helpers::gen_uuid();
	                $sifre->save();
	            }
	            
	            $tarih = time();
	            $dogrulamakodlari = Dogrulamakodlari::select('dogrulamakodu')->where('kullanici_id', '=', $id)->where('sonkullanimtarihi', '>', $tarih)->where('durum', '=', false)->first();
	            $tarih = time()+60*60;
	            if($dogrulamakodlari == null){
	                $dogrulama = new Dogrulamakodlari;
	                $dogrulama->kullanici_id = $id;
	                $dogrulama->dogrulamakodu = Helpers::DogrulamaKodu();
	                $dogrulama->durum = false;
	                $dogrulama->sonkullanimtarihi = $tarih;
	                $dogrulama->save();
	                $kod = $dogrulama->dogrulamakodu;
	            }else{
	                $kod = $dogrulamakodlari->dogrulamakodu;
	            }
	            
	            
	            $mail = new Mail;
	            $mail->kullanici_id = $id;
	            $mail->mailadresi = $mailadresi;
	            $mail->baslik = "Pass ID Code";
	            $mail->mesaj = $kod;
	            $mail->sablon = "mail.postmail";
	            $mail->durum = false;
	            $mail->save();
	            
	            $sonuc = array(
	                'durum' => Helpers::aes_encrypt("false"),
	            );
	            
	            return $sonuc;
	            
	        }      
	        
	      
	    }
	    
	}
	
}
