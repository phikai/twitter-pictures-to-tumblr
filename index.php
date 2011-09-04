<?php

/*************************************/
/*									 */
/*									 */
/* NO NEED TO CHANGE BELOW THIS AREA */
/*									 */
/*									 */
/*************************************/

//Set XML Errors Off
//libxml_use_internal_errors(true);
libxml_use_internal_errors(false);

//Require Main Config File
require_once('config.php')

//MySQL Connection
mysql_connect($server,$username,$password);
@mysql_select_db($database) or die("Unable to select database");

//Set URL for Twitter Search
//$basetwitsearch = 'http://search.twitter.com/search.atom?lang=en&q=';
$basetwitsearch = 'http://search.twitter.com/search.atom?q=';
$limit = 100;

//Since ID Text File
$sinceidfile = 'sinceid.txt';

//Get the Since ID to get new Tweets from Last Time
$sincefile = fopen($sinceidfile, 'r');
$sinceid = fgets($sincefile);
fclose($sincefile);

//curl twitter for data
$ch= curl_init($basetwitsearch.urlencode($twitquery).'&rpp='.$limit.'&since_id='.$sinceid);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
$response = curl_exec($ch);

$twitfeed = simplexml_load_string($response);

//Debugging Code
//print_r($twitfeed);

foreach($twitfeed->link as $link) {
	$linkprops = $link->attributes();
	$rel = $linkprops['rel'];
	if($rel == 'refresh') {
		$newsearchurl = $linkprops['href'];
		$since = explode('since_id=', $newsearchurl);
		$sinceid = $since[1];
		
		//Write ID to Text File
		$sincefile = fopen($sinceidfile, 'w');
		fwrite($sincefile, $sinceid."\n");
		fclose($sincefile);
		
	}
	else {
		//Do Nothing
	}
}

foreach($twitfeed->entry as $entry) {
	foreach($entry->link as $sourcelink) {
		$sourceprops = $sourcelink->attributes();
		$rel = $sourceprops['rel'];
		if ($rel == 'alternate') {
			$values['source'] = trim($sourceprops['href']);
			$values['bitlyurl'] = bitlyurl($values['source']);		
		}
		else if ($rel == 'image') {
			$values['authimg'] = trim($sourceprops['href']);
		}
		else {
			//Do Nothing
		}
	}
	unset($sourcelink);
	foreach($entry->title as $tweettitle) {
		$values['title'] = trim($tweettitle);
		
		//Picture Services…We Support Them
		//Instagram, Twitpic, yfrog, Plixi, Flickr…Plixi might be broken…
		
		if(strstr($values['title'],'twitpic.com')) {
			$values['shorturl'] = photourl($values['title']);
			$values['photourl'] = twitpic($values['title']);
		}				
		else if(strstr($values['title'],'yfrog.com')) {
			$values['shorturl'] = photourl($values['title']);
			$values['photourl'] = yfrog($values['title']);			
		}
		else if(strstr($values['title'],'instagr.am')) {
			$values['shorturl'] = photourl($values['title']);
			$values['photourl'] = instagram($values['title']);
		}
		/*else if(strstr($values['title'],'plixi.com')) {
			$values['shorturl'] = photourl($values['title']);
			$values['photourl'] = plixi($values['title']);
		}*/
		else if(strstr($values['title'],'flic.kr')) {
			$values['shorturl'] = photourl($values['title']);
			$values['photourl'] = flickr($values['title']);
		}
		else if(strstr($values['title'],'t.co')) {
			$values['shorturl'] = tcolocation($values['title']);
			$values['photourl'] = tco($values['title']);
		}
		else if(strstr($values['title'],'4sq.com')) {
			$values['shorturl'] = photourl($values['title']);
			$values['photourl'] = foursquare($values['title']);
		}
		else if(strstr($values['title'],'post.ly')) {
			$values['shorturl'] = photourl($values['title']);
			$values['photourl'] = posterous($values['title']);
		}
		else if(strstr($values['title'],'picplz.com')) {
			$values['photourl'] = picplz($values['title']);
		}			
		else {
			//Do Nothing
		}		
		
	}
	unset($tweetttile);
	foreach($entry->author as $authinfo) {
		$values['authname'] = trim($authinfo->name);
		$values['authurl'] = trim($authinfo->uri);
	}
	unset($authinfo);
	//print_r($values);

	if(strlen($values['photourl']) > 1) {
	
		//Sanitize the Variables before MySQL Query
		foreach($values as $key => $val){
			$safe[$key] = mysql_real_escape_string($val);
		}
		
		if(mysql_num_rows(mysql_query("SELECT shorturl FROM ".$database_table." WHERE shorturl = '{$safe['shorturl']}'"))){
			// Code inside if block if userid is already there
		}
		else {
			//Tumblr Setup
	
			// Options
			$post_state = 'published';

			// Data for new record
			$post_type  = 'photo';
			$post_title = $vaules['title'];
			$post_photo = $values['photourl'];
			if(strlen($values['bitlyurl']) > 1) {
				$post_caption  = $values['title'].'<br /><br />Source: <a href="'.$values['bitlyurl'].'">'.$values['bitlyurl'].'</a>';
			}
			else {
				$post_caption  = $values['title'].'<br /><br />Source: <a href="'.$values['source'].'">'.$values['source'].'</a>';
			}
	
			// Prepare POST request
			$request_data = http_build_query(
				array(
					'email'     => $tumblr_email,
					'password'  => $tumblr_password,
					'type'      => $post_type,
					'state'		=> $post_state,
					'title'     => $post_title,
					'caption'   => $post_caption,
					'source'	=> $post_photo,
					'generator' => 'ThinkOneZero Picture Tweet Catcher'
				)
			);
		
			//Debug Code
			//print_r($request_data);

			// Send the POST request (with cURL)
			$c = curl_init('http://www.tumblr.com/api/write');
			curl_setopt($c, CURLOPT_POST, true);
			curl_setopt($c, CURLOPT_POSTFIELDS, $request_data);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($c);
			$status = curl_getinfo($c, CURLINFO_HTTP_CODE);
			curl_close($c);

			// Check for success
			if ($status == 201) {
				//echo "Success! The new post ID is $result.\n";
				
				//MySQL Query for Each Item
				$query = "INSERT INTO ".$database_table."(shorturl, source, bitlyurl, title, photourl, authname, authurl, authimg) VALUES('{$safe['shorturl']}', '{$safe['source']}', '{$safe['bitlyurl']}', '{$safe['title']}', '{$safe['photourl']}', '{$safe['authname']}', '{$safe['authurl']}', '{$safe['authimg']}')";
				mysql_query($query);
			} 
			else if ($status == 403) {
				echo 'Bad email or password';
			} 
			else {
				echo "Error: $result\n";
				//print_r($values);
			}
		
			//Debugging Print
			//print_r($values);		
	
			//Reset Values Array
			unset($values);
		}
	}
	else {
		//Debugging Print
		//print_r($values);
	
		unset($values);
	}	
}

//MySQL Connection Close
mysql_close();

?>