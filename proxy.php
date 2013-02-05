<?php
// DESCRIPTION: 
// 		A session aware php proxy. 
// AUTHOR: Jonathan Cheung
// 
// Prerequisite:
// 		- A writable directory
// 		- CURL and PHP-CURL
// 		- PHP session enabled
// 
// Usage:
// 	Use GET, POST, DELETE, PUT method to pass in the 2 parameters:
// 		- url : (String) - URI encoded URL of the server address.
// 				* Note:	When using GET, please URI encode the parameters before appending to the url
// 						E.G.	If this is the actual call "http://www.example.com/api/?name=xxx&email=yyy.com",
// 								your string should be "http%3A%2F%2Fwww.example.com%2Fapi%2F%3Fname%3Dxxx%26email%3Dyyy.com"
// 								so that your proxy URL would be proxy.php?url=http%3A%2F%2Fwww.example.com%2Fapi%2F%3Fname%3Dxxx%26email%3Dyyy.com
// 		- clearCookie : (String) true | false - tells the proxy to start a new local session.
//  
// 
// 

//	CONFIGURATION:
//
// filePrefix is the prefix of the local folder for the cookie files
// fileSuffix is the suffix of the local folder for the cookie files
// 	e.g. 
// 		IF
// 			$filePrefix = "/tmp/CURLCOOKIE_";
// 			$fileSuffix = "_file.cookie";
// 			XXXXXX is your session ID
// 		THEN your files will be stored in:
// 			"/tmp/CURLCOOKIE_XXXXXX_file.cookie"
// 
$filePrefix = "/tmp/CURLCOOKIE_";
$fileSuffix = "";

session_start();
$url = $_REQUEST['url'];

//Start the Curl session
$session = curl_init($url);


if (!$_SESSION['cookiefile'] || $_REQUEST['clearCookie'] == 'true')
{	
	session_destroy();
	session_start();
	session_regenerate_id ();
	$_SESSION['cookiefile'] = $filePrefix.session_id().$fileSuffix;
	curl_setopt($session, CURLOPT_COOKIESESSION, 1);

	// uncomment to log
	//error_log(session_id()."\n\n", 3, "/var/tmp/my-sessions.log");
	
}else{

	curl_setopt($session, CURLOPT_COOKIEFILE, $_SESSION['cookiefile']);
}	
//echo "cookie" .$_SESSION['cookiefile'];

// If it's a POST, put the POST data in the body
if ($_POST ) {
 $requestvars = "";
 foreach ($_POST as $key => $val){
 	if ($requestvars !== "") $requestvars .= "&";
 	$requestvars .=$key."=".$val;
 }
 	// uncomment to log
	//error_log($postvars."\n\n", 3, "/var/tmp/my-errors.log");
}

switch($_SERVER['REQUEST_METHOD']){

	case "GET":
	break;
	case "POST":
		curl_setopt ($session, CURLOPT_POST, true);
		curl_setopt ($session, CURLOPT_POSTFIELDS, $requestvars);
	break;
	case "DELETE":
		curl_setopt($session, CURLOPT_CUSTOMREQUEST, "DELETE"); 
	break;
	case "PUT":
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); 
	break;
}


//Save CURL COOKIES upon curl_close()
curl_setopt($session, CURLOPT_COOKIEJAR, $_SESSION['cookiefile']);
curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_HEADER,true);

// Make the call
$response = curl_exec($session) or die("CURL Error");

list($headers, $body) = explode("\r\n\r\n",(string)$response,2);

$count =0;
foreach (explode("\r\n", $headers) as $hdr){
	if($count <1) 	{
/* 		echo $hdr; */
		header($hdr); 
	}
	$count++;
} 

echo $body;

// uncomment to log
// error_log($response."\n\n", 3, "/var/tmp/my-response.log");

curl_close($session); 

?>

