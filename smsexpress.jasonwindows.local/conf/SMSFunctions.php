<?PHP

function initDB () {

  $sqlhost = "localhost";
  $sqluser = "jwsms";
  $sqlpw = "WUDH53dhGmE6gETMiJLHwgTTUEKiyMjTvHt77CZM";
  $sqldb = "jwsms";
//password in plain text?!
  $sqlcon = mysqli_connect($sqlhost, $sqluser, $sqlpw, $sqldb);
  if (mysqli_connect_errno()) {
    error_log("Failed to connect to MySQL: " . mysqli_connect_error());
    return null;
  }

  return $sqlcon;

}

function storeSentMessage ($to, $message, $from, $result, $link) {

  $stmt = mysqli_prepare($link,"INSERT INTO sent_messages VALUES ('', ?, ?, ?, ?, ?);");

  ini_set('date.timezone', 'Australia/Perth');

  $time = date("Y-m-d H:i:s");

  mysqli_stmt_bind_param($stmt, "sssss", $to, $from, $message, $time, $result);

  mysqli_stmt_execute($stmt);

  $result = ( mysqli_stmt_affected_rows($stmt) == 1 );

  mysqli_stmt_close($stmt);
  return $result;

}

function sendSMS($to, $message, $from) {

  $user = "ictnotify@jasonwindows.com.au";
  $pw = 'Jw1nd0ws';
  //$baseurl = "https://www.streetdata.com.au/admin/msg.php?u=".$user."&p=".$pw;
  //$url = $baseurl."&d=".$to."&m=".urlencode($message)."&mf=5";
  //vch[> Begin
  $pos = stripos($message,"Ref:");       //get the position of Reference
  if ($pos !== false)
  {
    $ref = substr($message,$pos,15);        //Get the referece number (job no)
  }
  else
  {
    $ref = substr($message,1,100);
  }
  //
  //vch<] End
  $baseurl = "https://tim.telstra.com/cgphttp/servlet/sendmsg?username=".$user."&password=".$pw;
  //vch $url = $baseurl."&destination=".$to."&text=".urlencode($message)."&replytoTON=8&replyTo=".$from;
  $url = $baseurl."&destination=".$to."&text=".urlencode($message)."&replytoTON=8&replyTo=".$from."&clientMessageId=".urlencode($ref); //vch
  $dblink = initDB();
  if ($dblink == null) {
    return "LCLF Could not connect to DB.";
  }

  $curl = curl_init();

  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

  $result = curl_exec($curl);

  curl_close($curl);

  $storeResult = storeSentMessage($to, $message, $from, $result, $dblink);

  if (!$storeResult) {
    error_log("Unable to insert message to " . $to . ". Result: " . $result);
    $result = "LCLF Unable to insert into DB, message may have been send but replies will not be received. " . $result;
  }

  mysqli_close($dblink);

  return $result;
}

function getAPIAcct ($key, $ip) {

  $dblink = initDB();
  if ($dblink == null) {
    error_log("Unable to connect to DB to check API key.");
    return null;
  }

  $sql = "SELECT from_address, can_override_from FROM api_keys WHERE api_key='" . preg_replace('/[^a-zA-Z0-9]/', '', $key) . "' AND ( allowed_ip=INET_ATON('" . preg_replace('/[^0-9\.]/', '', $ip) . "') OR allowed_ip=0 ) LIMIT 1;";

  $result = $dblink->query($sql);
  mysqli_close($dblink);

  $resdata = $result->fetch_assoc();
  return $resdata;

}

?>
