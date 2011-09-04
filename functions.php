<?php

//Functions for Twitter Photo Scraping

//Require Main Config File
require_once('config.php')

//Bitly URL Creation
function bitlyurl($valuessource) {
	//Lets get a Bit.ly URL for this…it's smaller!
			
	// full url to send to bit.ly
	$url = 'http://api.bitly.com/v3/shorten?login='.$bitlyuser.'&apiKey='.$bitlykey.'&longUrl='.urlencode($valuessource).'&format=json';

	// make the cURL request to TwitPic URL
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $url);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);
		
	$response = curl_exec($curl2);
	
	$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
			
	// if the HTTPCode is not 200 - you got issues
	if ($HttpCode != 200) {
		//IT FAILED!
	}
	else {
		$json_a = json_decode($response,true);
		$valuesbitlyurl = $json_a['data']['url'];
		return $valuesbitlyurl;
	}
}

//Photo URL
function photourl($valuestitle) {
	$url = explode('http://', $valuestitle);
	$photoservice = explode(' ', $url[1]);
	$photourl = 'http://'.$photoservice[0];
	return $photourl;
}

//TCO Long URL
function tcolocation($valuestitle) {
	$photourl = photourl($valuestitle);

	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $photourl);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_NOBODY, true); 
	curl_setopt($curl2, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl2, CURLOPT_HEADER, true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);		

	$response = curl_exec($curl2);
	curl_close ($curl2); 
	$retVal = array();

	$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $response));
	foreach( $fields as $field ) {
		if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
			$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
			if( isset($retVal[$match[1]]) ) {
				$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
			} else {
				$retVal[$match[1]] = trim($match[2]);
			}
		}			
	}		

	$location = $retVal['Location'];
	return $location;
}

//Twitpic Photo Service
function twitpic($valuestitle) {
	$photourl = photourl($valuestitle);
			
	// full url of the TwitPic photo
	$url = $photourl;

	// make the cURL request to TwitPic URL
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $url);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);

	$html = curl_exec($curl2);
		
	$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);

	// if the HTTPCode is not 200 - you got issues
	if ($HttpCode != 200) {
		//IT FAILED!
	}
	else {
		// if you are not getting any HTML returned, you got another issue.
		if ($html == "") {
			//Twitpic must be broken…
		}
		else {
			$dom = new DOMDocument();
			@$dom->loadHTML($html);
			// grab all the on the page
			$xpath = new DOMXPath($dom);
			$hrefs = $xpath->evaluate("/html/body//img");
			foreach( $hrefs as $href ) {
				$url = $href->getAttribute('id'); 
				// for all the images on the page find the one with the ID of photo-display
				if ($url == "photo-display") {
					// get the SRC attribute of the element with the ID of photo-display
					$valuesphotourl = $href->getAttribute('src');
					return $valuesphotourl;
				}
			}
		}
	}
}

//YFrog Photo Service
function yfrog($valuestitle) {
	//http://www.yfrog.com/api/oembed?url=http%3A%2F%2Fyfrog.com%2F2pswonj
	$photourl = photourl($valuestitle);
		
	// full url of the YFrog photo
	$url = 'http://www.yfrog.com/api/oembed?url='.urlencode($photourl);

	// make the cURL request to YFrog URL
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $url);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);

	$response = curl_exec($curl2);
	$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
			
	// if the HTTPCode is not 200 - you got issues
	if ($HttpCode != 200) {
		//IT FAILED!
	}
	else {
		$json_a = json_decode($response,true);
		//print $json_a['url'];
		$valuesphotourl = $json_a['url'];
		return $valuesphotourl;
	}
}

//Instagram Photo Service
function instagram($valuestitle) {
	//http://api.instagram.com/oembed?url=http://instagr.am/p/BUG/
	$photourl = photourl($valuestitle);
		
	// full url of the Instagram photo
	$url = 'http://api.instagram.com/oembed?url='.urlencode($photourl);

	// make the cURL request to Instagram URL
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $url);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);

	$response = curl_exec($curl2);
	$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
			
	// if the HTTPCode is not 200 - you got issues
	if ($HttpCode != 200) {
		//IT FAILED!
	}
	else {
		$json_a = json_decode($response,true);
		//print $json_a['url'];
		$valuesphotourl = $json_a['url'];
		return $valuesphotourl;
	}
}

//Plixi Photo Service
function plixi($valuestitle) {
	//http://api.plixi.com/api/tpapi.svc/imagefromurl?size=big&url=http://tweetphoto.com/5527850
	$photourl = photourl($valuestitle);
	
	$valuesphotourl = 'http://api.plixi.com/api/tpapi.svc/imagefromurl?size=big&url='.$photourl;
	return $valuesphotourl;
}

//Flickr Photo Service
function flickr($valuestitle) {
	//http://flickr.com/services/oembed?url=http%3A//flickr.com/photos/bees/2362225867/
	$photourl = photourl($valuestitle);

	//Curl to Get Long Location URL
	//http://stackoverflow.com/questions/4062819/curl-get-redirect-url-to-a-variable
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $photourl);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_NOBODY, true); 
	curl_setopt($curl2, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl2, CURLOPT_HEADER, true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);			

	$response = curl_exec($curl2);
	curl_close ($curl2); 
	$retVal = array();

	$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $response));
		foreach( $fields as $field ) {
			if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
				$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
				if( isset($retVal[$match[1]]) ) {
					$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
				} else {
					$retVal[$match[1]] = trim($match[2]);
				}
			}			
		}		

	$photourl = 'http://flickr.com'.$retVal['Location'][1];
			
	// full url of the Flickr photo
	$url = 'http://flickr.com/services/oembed?url='.$photourl;

	// make the cURL request to Flickr Oembed URL
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $url);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);

	$response = curl_exec($curl2);
	$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
			
	// if the HTTPCode is not 200 - you got issues
	if ($HttpCode != 200) {
		//IT FAILED!
	}
	else {
		$flickr = new SimpleXMLElement($response);
		$flickrurl = ( (string) $flickr->url);
		$valuesphotourl = $flickrurl;
		return $valuesphotourl;
	}
}

//t.co URL Service
function tco($valuestitle) {
	$location = tcolocation($valuestitle);
	
	if(strstr($location,'twitpic.com')) {			
		$valuesphotourl = twitpic($location);
		return $valuesphotourl;
	}				
	else if(strstr($location,'yfrog.com')) {			
		$valuesphotourl = yfrog($location);
		return $valuesphotourl;
	}
	else if(strstr($location,'instagr.am')) {
		$valuesphotourl = instagram($location);
		return $valuesphotourl;
	}
	/*else if(strstr($location,'plixi.com')) {
		$valuesphotourl = plixi($location);
		return $valuesphotourl;
	}*/
	else if(strstr($location,'flic.kr')) {
		$valuesphotourl = flickr($location);
		return $valuesphotourl;
	}
	else {
		//Do Nothing
	}	
}

//FourSquare URLs
function foursquare($valuestitle) {
	$photourl = photourl($valuestitle);

	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $photourl);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_NOBODY, true); 
	curl_setopt($curl2, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl2, CURLOPT_HEADER, true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);		

	$response = curl_exec($curl2);
	curl_close ($curl2); 
	$retVal = array();

	$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $response));
	foreach( $fields as $field ) {
		if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
			$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
			if( isset($retVal[$match[1]]) ) {
				$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
			} else {
				$retVal[$match[1]] = trim($match[2]);
			}
		}			
	}		

	$url = $retVal['Location'];

	// make the cURL request to TwitPic URL
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $url);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);

	$html = curl_exec($curl2);
		
	$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);

	// if the HTTPCode is not 200 - you got issues
	if ($HttpCode != 200) {
		//IT FAILED!
	}
	else {
		// if you are not getting any HTML returned, you got another issue.
		if ($html == "") {
			//Twitpic must be broken…
		}
		else {
			$dom = new DOMDocument();
			@$dom->loadHTML($html);
			// grab all the on the page
			$xpath = new DOMXPath($dom);
			$hrefs = $xpath->evaluate("/html/body//img");
			foreach( $hrefs as $href ) {
				$url = $href->getAttribute('class'); 
				// for all the images on the page find the one with the ID of photo-display
				if ($url == "mainPhoto") {
					// get the SRC attribute of the element with the ID of photo-display
					$valuesphotourl = $href->getAttribute('src');
					return $valuesphotourl;
				}
			}
		}
	}
}

//Posterous URLs
function posterous($valuestitle) {
	$photourl = photourl($valuestitle);
	
	$posterousid = explode('/', $photourl);
	$postlyid = $posterousid[3];
	
	$url = 'http://posterous.com/api/getpost?id='.$postlyid;
	
	// make the cURL request to Postly URL
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $url);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);

	$response = curl_exec($curl2);
	$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
			
	// if the HTTPCode is not 200 - you got issues
	if ($HttpCode != 200) {
		//IT FAILED!
	}
	else {
		$xmlstring = simplexml_load_string($response);
		$mediatype = $xmlstring->post->media->type;
		
		if ($mediatype = 'image') {
			$photourl = $xmlstring->post->media->medium->url;
			
			$curl2 = curl_init();
			curl_setopt($curl2, CURLOPT_URL, $photourl);
			curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
			curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl2, CURLOPT_NOBODY, true); 
			curl_setopt($curl2, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl2, CURLOPT_HEADER, true);
			curl_setopt($curl2, CURLOPT_TIMEOUT, 10);		

			$response = curl_exec($curl2);
			curl_close ($curl2); 
			$retVal = array();

			$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $response));
			foreach( $fields as $field ) {
				if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
					$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
					if( isset($retVal[$match[1]]) ) {
						$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
					} else {
						$retVal[$match[1]] = trim($match[2]);
					}
				}			
			}		

			$location = $retVal['Location'];
			return $location;
		}
		else {
			//Do Nothing
		}
	}
}

//Picplz URLs
function picplz($valuestitle) {
	$photourl = photourl($valuestitle);
	
	$photo = explode('/', $photourl);
	$photoid = $photo[3];
			
	// full url to send to picplz.com
	$url = 'http://api.picplz.com/api/v2/pic.json?shorturl_id='.$photoid;

	// make the cURL request to TwitPic URL
	$curl2 = curl_init();
	curl_setopt($curl2, CURLOPT_URL, $url);
	curl_setopt($curl2, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl2, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl2, CURLOPT_TIMEOUT, 10);
		
	$response = curl_exec($curl2);
	
	$HttpCode = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
			
	// if the HTTPCode is not 200 - you got issues
	if ($HttpCode != 200) {
		//IT FAILED!
	}
	else {
		$json_a = json_decode($response,true);
		$valuesphotourl = $json_a['value']['pics'][0]['pic_files']['640r']['img_url'];
		return $valuesphotourl;
	}
}

function imgResize($width, $height, $target) {
	if ($width > $height) {
		$percentage = ($target / $width);
	} 
	else {
		$percentage = ($target / $height);
	}
	$width = round($width * $percentage);
	$height = round($height * $percentage);
	
	return "width=\"$width\" height=\"$height\"";
}
