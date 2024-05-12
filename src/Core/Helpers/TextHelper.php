<?php
namespace Core\Helpers;

class TextHelper{
	public static function Cut($text,$maxchar){	
		$result = $text;	
		$ntext = strlen($text);
		if($ntext>$maxchar){
			$last = $maxchar;
			for($j=$maxchar;$j>0;$j--){
				if($text[$j]==' '){
					$last = $j;
					break;
				}
			}
			$result = substr($text,0,$last);
			$result .= " ...";
		}	
		return $result;
	}
	//from http://stackoverflow.com/a/2668953
	public static function sanitize($string, $force_lowercase = true, $anal = false) {
		$strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
					   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
					   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
		$clean = trim(str_replace($strip, "", strip_tags($string)));
		$clean = preg_replace('/\s+/', "-", $clean);
		$clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
		return ($force_lowercase) ?
			(function_exists('mb_strtolower')) ?
				mb_strtolower($clean, 'UTF-8') :
				strtolower($clean) :
			$clean;
	}
	
	
	public static function clean($str){
		return htmlentities($str, ENT_QUOTES, 'UTF-8');
	}
	
	public static function encrypt($string_to_encrypt){		
		$password="r\3@W':226:c=ZyH";
		return openssl_encrypt($string_to_encrypt,"AES-128-ECB",$password);		
	}
	public static function decrypt($encrypted_string){		
		$password="r\3@W':226:c=ZyH";		
		return openssl_decrypt($encrypted_string,"AES-128-ECB",$password);
	}
	public static function convertLatin1ToUtf8Recursively($dat){
		if (is_string($dat)) {
			return utf8_encode($dat);
		} elseif (is_array($dat)) {
			$ret = [];
			foreach ($dat as $i => $d) $ret[ $i ] = self::convertLatin1ToUtf8Recursively($d);

			return $ret;
		} elseif (is_object($dat)) {
			foreach ($dat as $i => $d) $dat->$i = self::convertLatin1ToUtf8Recursively($d);

			return $dat;
		} else {
			return $dat;
		}
	}
	
	public static function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[random_int(0, $charactersLength - 1)];
		}
		return $randomString;
	}

}

