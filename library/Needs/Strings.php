<?php
class Needs_Strings {
	/**
	 * Functie pentru comments
	 * @param unix timestamp $ptime
	 * @return string
	 */	
	
	public static function time_elapsed_string($ptime) {
		$secs = time() - $ptime;		
		$second		= 1;
		$minute		= 60;
		$hour		= 60*60;
		$day		= 60*60*24;
		$week		= 60*60*24*7;
 
		
		if ($secs <= 0) { $output = "now";
		}elseif ($secs > $second && $secs < $minute) {	$output = round($secs/$second)." second";
		}elseif ($secs >= $minute && $secs < $hour) {	$output = round($secs/$minute)." minute";
		}elseif ($secs >= $hour && $secs < $day) {		$output = round($secs/$hour)." hour";
		}elseif ($secs >= $day && $secs < $week) {		$output = round($secs/$day)." day";		
		}else{ //if more than a week, return '24 January,2014' format
			return date('d F, Y',$ptime); 			
		}
 
		if ($output <> "now"){
			$output = (substr($output,0,2)<>"1 ") ? $output."s" : $output;
			$output = $output.' ago';
		}
		return $output;
	}
 

	/**
  * Returneaza data primita la primul parametru in formatul specificat in al doilea parametru
  * 
  * @param string $inputDate Data in formatul YYYY-mm-dd
  * @param string $returnFormat Formatul in care se doreste sa fie returnata
  * 
  * @return string Data formatata dupa modul specificat in al doilea parametru
  */
	public static function convertDate($nr, $type='month') {
		$numar = (int) $nr;
		$dayRo = array(
			1 => 'Luni',
			2 => 'Marti',
			3 => 'Miercuri',
			4 => 'Joi',
			5 => 'Vineri',
			6 => 'Sambata',
			7 => 'Duminica',);
		$monthRo = array(
			1 => 'Ianuarie',
			2 => 'Februarie',
			3 => 'Martie',
			4 => 'Aprilie',
			5 => 'Mai',
			6 => 'Iunie',
			7 => 'Iulie',
			8 => 'August',
			9 => 'Septembrie',
			10 => 'Octombrie',
			11 => 'Noiembrie',
			12 => 'Decembrie',);
		if ($type == 'day') {
			if (isset($dayRo[$numar])) {
				return $dayRo[$numar];
			}
		} elseif ($type == 'month') {
			if (isset($monthRo[$numar])) {
				return $monthRo[$numar];
			}
		}
		return null;
	}

	public static function stripRomanianChars($string)
	{
		$string = mb_convert_encoding($string, "HTML-ENTITIES", "auto");
		$string = str_replace(array('&#355;', '&#354;', '&#259;', '&#258;', '&#226;', '&#194;', '&#238;', '&#206;', '&#351;', '&#350;', '&#x219;', '&#x218;', '&#x103;', '&#x102;', '&acirc;', '&Acirc;', '&icirc;', '&Icirc;', '&#x15F;', '&#x15E;', '&#x21B;', '&#x21A;', '&#x163;', '&#x162;','&#536;','&#537;'),
		array('t', 'T', 'a', 'A', 'a', 'A', 'i', 'I', 's', 'S', 's', 'S', 'a', 'A', 'a', 'A', 'i', 'I', 's', 'S', 't', 'T', 't', 'T','S','s'), $string);
		return $string;
	}

	public static function buildFullUrl($input){
		$input = self::removeAccents($input);
		$input = trim($input);
		$input = strtolower($input);
		$input = preg_replace('/[^a-z0-9]/', '-', $input);
		$input = preg_replace('/[-]+/', '-', $input);
		$input = trim($input, '-');
		return $input;
	}

	public static function removeAccents($input){
		$accented = array(
				  'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ă', 'Ą',
				  'Ç', 'Ć', 'Č', 'Œ',
				  'Ď', 'Đ',
				  'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ă', 'ą',
				  'ç', 'ć', 'č', 'œ',
				  'ď', 'đ',
				  'È', 'É', 'Ê', 'Ë', 'Ę', 'Ě',
				  'Ğ',
				  'Ì', 'Í', 'Î', 'Ï', 'İ',
				  'Ĺ', 'Ľ', 'Ł',
				  'è', 'é', 'ê', 'ë', 'ę', 'ě',
				  'ğ',
				  'ì', 'í', 'î', 'ï', 'ı',
				  'ĺ', 'ľ', 'ł',
				  'Ñ', 'Ń', 'Ň',
				  'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ő',
				  'Ŕ', 'Ř',
				  'Ś', 'Ş', 'Š',
				  'ñ', 'ń', 'ň',
				  'ò', 'ó', 'ô', 'ö', 'ø', 'ő',
				  'ŕ', 'ř',
				  'ś', 'ş', 'š',
				  'Ţ', 'Ť', 'ț', 'Ț',
				  'Ù', 'Ú', 'Û', 'Ų', 'Ü', 'Ů', 'Ű',
				  'Ý', 'ß',
				  'Ź', 'Ż', 'Ž',
				  'ţ', 'ť',
				  'ù', 'ú', 'û', 'ų', 'ü', 'ů', 'ű',
				  'ý', 'ÿ',
				  'ź', 'ż', 'ž',
				  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р',
				  'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'р',
				  'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
				  'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
				  );
		$replace = array(
				 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'A', 'A',
				 'C', 'C', 'C', 'CE',
				 'D', 'D',
				 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'a', 'a',
				 'c', 'c', 'c', 'ce',
				 'd', 'd',
				 'E', 'E', 'E', 'E', 'E', 'E',
				 'G',
				 'I', 'I', 'I', 'I', 'I',
				 'L', 'L', 'L',
				 'e', 'e', 'e', 'e', 'e', 'e',
				 'g',
				 'i', 'i', 'i', 'i', 'i',
				 'l', 'l', 'l',
				 'N', 'N', 'N',
				 'O', 'O', 'O', 'O', 'O', 'O', 'O',
				 'R', 'R',
				 'S', 'S', 'S',
				 'n', 'n', 'n',
				 'o', 'o', 'o', 'o', 'o', 'o',
				 'r', 'r',
				 's', 's', 's',
				 'T', 'T', 't', 'T',
				 'U', 'U', 'U', 'U', 'U', 'U', 'U',
				 'Y', 'Y',
				 'Z', 'Z', 'Z',
				 't', 't',
				 'u', 'u', 'u', 'u', 'u', 'u', 'u',
				 'y', 'y',
				 'z', 'z', 'z',
				 'A', 'B', 'B', 'r', 'A', 'E', 'E', 'X', '3', 'N', 'N', 'K', 'N', 'M', 'H', 'O', 'N', 'P',
				 'a', 'b', 'b', 'r', 'a', 'e', 'e', 'x', '3', 'n', 'n', 'k', 'n', 'm', 'h', 'o', 'p',
				 'C', 'T', 'Y', 'O', 'X', 'U', 'u', 'W', 'W', 'b', 'b', 'b', 'E', 'O', 'R',
				 'c', 't', 'y', 'o', 'x', 'u', 'u', 'w', 'w', 'b', 'b', 'b', 'e', 'o', 'r'
				 );
		return str_replace($accented, $replace, $input);
	}

	public static function buildKeywords($string)
	{
		$string = self::removeAccents($string);
		$string = strtolower(trim($string));
		$string = preg_replace('([, ]+)', ', ', $string);
		return $string;

	}
	
	public static function limitString($string,$length)
	{		
		// Return early if the string is already shorter than the limit
		if(strlen($string) < $length) {return $string;}

		$regex = "/(.{1,$length})\b/";
		preg_match($regex, $string, $matches);
		return $matches[1].' ...';
	}
	
	public static function buildAbsoluteUrl()
	{
		$url ='http://'.$_SERVER['SERVER_NAME'].WEBROOT.'/';
		return $url;
	}
	
	 public static function cleanYoutubeUrl($youtubeUrl)
    {       
        preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $youtubeUrl, $matches);
        $result = (!empty($matches) && !empty($matches[0]))?$matches[0]:NULL;
        return  $result; 
    }
	
	public static function youtubeEmbed($youtubeUrl, $width, $height)
	{
		$return = '';
		$cleanYoutubeUrl = self::cleanYoutubeUrl($youtubeUrl);
		if($cleanYoutubeUrl)
		{
			$return .= "<iframe width='{$width}' height='{$height}' src='//www.youtube.com/embed/{$cleanYoutubeUrl}?rel=0&showinfo=0&vq=hd720&hd=1' frameborder='0' allowfullscreen ></iframe>";
		}
		echo $return;
	}
}