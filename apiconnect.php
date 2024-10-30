<?php 
session_start();
$client_id = $_SESSION['cleint_id'];
$client_secret = $_SESSION['secert_id'];
$redirect_uri = $_SESSION['redirect_url'];
$max_results = 1000;

$auth_code = $_GET["code"];
function curl_file_get_contents($url)
{
 $curl = curl_init();
 $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
 
 curl_setopt($curl,CURLOPT_URL,$url);	//The URL to fetch. This can also be set when initializing a session with curl_init().
 curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);	//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
 curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);	//The number of seconds to wait while trying to connect.	
 
 curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);	//The contents of the "User-Agent: " header to be used in a HTTP request.
 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);	//To follow any "Location: " header that the server sends as part of the HTTP header.
 curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);	//To automatically set the Referer: field in requests where it follows a Location: redirect.
 curl_setopt($curl, CURLOPT_TIMEOUT, 10);	//The maximum number of seconds to allow cURL functions to execute.
 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);	//To stop cURL from verifying the peer's certificate.
 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
 
 $contents = curl_exec($curl);
 curl_close($curl);
 return $contents;
}

	$fields=array(
		'code'=>  urlencode($auth_code),
		'client_id'=>  urlencode($client_id),
		'client_secret'=>  urlencode($client_secret),
		'redirect_uri'=>  urlencode($redirect_uri),
		'grant_type'=>  urlencode('authorization_code')
	);
$post = '';
foreach($fields as $key=>$value) { $post .= $key.'='.$value.'&'; }
$post = rtrim($post,'&');

$curl = curl_init();
curl_setopt($curl,CURLOPT_URL,'https://accounts.google.com/o/oauth2/token');
curl_setopt($curl,CURLOPT_POST,5);
curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
$result = curl_exec($curl);
curl_close($curl);

$response =  json_decode($result);
$accesstoken = $response->access_token;

$url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results='.$max_results.'&oauth_token='.$accesstoken;
$xmlresponse =  curl_file_get_contents($url);
if((strlen(stristr($xmlresponse,'Authorization required'))>0) && (strlen(stristr($xmlresponse,'Error '))>0))
{
	echo "<h2>OOPS !! Something went wrong. Please try reloading the page.</h2>";
	exit();
}
 

  $sStep = 'fetch_contacts'; // fetch contacts step

		$xml =  new SimpleXMLElement($xmlresponse);
		$xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');
		$resultss = $xml->xpath('//gd:email');
		$email_exits_check = array();
		foreach ($resultss as $title) {
		  //echo $title->attributes()->address . "<br>";
		  $email_exits_check[] = $title->attributes()->address;
		}
		// Reading csv file 
		$file ='emtg_file.csv';
		$file_handle = fopen($file, "r") or die("Permission Denied :(");
	
		while (!feof($file_handle) ) {
		 $line_of_text = fgetcsv($file_handle, 102400);
		 $mail = $line_of_text[0];
		if (!in_array($mail, $email_exits_check))
		{
  
         $url = 'https://www.google.com/m8/feeds/contacts/default/full?oauth_token='.$accesstoken;
         $doc = new DOMDocument();
        $doc->formatOutput = true;

        $entry = $doc->createElement('atom:entry');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
        $doc->appendChild($entry);

        $cat = $doc->createElement('atom:category');
        $cat->setAttribute('scheme', 'http://schemas.google.com/g/2005#kind');
        $cat->setAttribute('term', 'http://schemas.google.com/contact/2008#contact');
        $entry->appendChild($cat);
        
        // add email element
         
        $email = $doc->createElement('gd:email');
        $entry->appendChild($email);
        $email->setAttribute('address',$mail);
        //$email->setAttribute('displayName', $_POST['fname']);
        //$email->setAttribute('primary', 'true');
        $email->setAttribute('rel', 'http://schemas.google.com/g/2005#work');


        //insert Address
        
        $contact_detail = $doc->saveXML();
      
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 5);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $contact_detail);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $curlheader[0] = "Content-Type: application/atom+xml";
        $curlheader[2] = "Content-length:" . strlen($contact_detail);
        $curlheader[1] = "Content-Transfer-Encoding: binary";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheader);
      
        $xmlresponse = curl_exec($curl);
      
  }
   
   
  }
//echo htmlentities($xmlresponse);

$email_arrya = array();
$email_arrya['id'] = $xmlresponse;
//echo trim($email_arrya['id']).'sfsdfsdf';


$xml=simplexml_load_string($xmlresponse) or die("Error: Cannot create object");
$value_id = (string) $xml->id;
$tokens = explode('/', $value_id);
$email_path = $tokens[sizeof($tokens)-3];

    //global $wpdb;
     //$del_emails =  "DELETE FROM ".$wpdb->prefix."import_mail_gmail_contacts ";
              //$wpdb->query($del_emails);
             // echo $wpdb->num_rows;
             // if($wpdb->num_rows == 0)
              //{
                 header('Location: '.$_SESSION['sucess_url'].'&email_id='.urldecode($email_path));  
             // }
  
?>