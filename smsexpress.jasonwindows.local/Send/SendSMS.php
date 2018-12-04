<?PHP

  include "../conf/LDAPFunctions.php";
  include "../conf/SMSFunctions.php";

  set_time_limit(30);

  // If the request includes an API key, authenticate based on that and the IP.
  if (isset($_REQUEST['K'])) {
    $key = urlencode($_REQUEST['K']);

    $APIAcct = getAPIAcct ($key, $_SERVER['REMOTE_ADDR']);

    if ( is_null($APIAcct) ) {
      header("HTTP/1.0 403 Could not validate API key " . $key);
      error_log("Could not validate API key: " . $key);
      exit(1);
    }

    if ($APIAcct['can_override_from'] == 1 && ! is_null($_REQUEST['from'])) {
      $reply = filter_var($_REQUEST['from'], FILTER_SANITIZE_EMAIL);
    } else {
      $reply = $APIAcct['from_address'];
    }

  } else {
    // otherwise require the user to be a member of the SMS Users group
    $user = GetUserIfInGroup($_SERVER['REMOTE_USER'], "CN=SMS Users,OU=Security,OU=Groups,OU=Accounts,DC=jasonwindows,DC=local");

    if (!$user) {
      header("HTTP/1.0 403 Could not validate user: " . $_SERVER['REMOTE_USER']);
      error_log("Could not validate user: " . $_SERVER['REMOTE_USER']);
      exit(1);
    }

    $reply = $user['mail'];

  }

  // Authentication complete, sanitize the input and send the message

  $dst = preg_replace('/[^0-9]/', '', $_REQUEST['dst']);
  $dst = preg_replace('/^04(?=[0-9]{8}$)/', '614', $dst);
  $dst = preg_replace('/^4(?=[0-9]{8}$)/', '614', $dst);

  $txt = preg_replace('/[^A-Za-z0-9@$_\/.,"():;\-=+&%#!?<>\' \n]/', '', $_REQUEST['txt']);

  if (! preg_match('/^614[0-9]{8}$/', $dst)) {

    header("HTTP/1.0 503 Invalid destination: " . $dst);

  } elseif ($txt == "") {

    header("HTTP/1.0 503 No valid message text.");

  } else {

    $result = sendSMS($dst, $txt, $reply);

    if (strpos($result,'OK') > 0) {
      header("HTTP/1.0 202 Accepted by carrier.");
    } elseif (substr($result,0,4) == "LCLF") {
      header("HTTP/1.0 503 Local Failure: " . substr($result,4));
    } else {
      header("HTTP/1.0 503 Refused by carrier: " . $result);
    }

  }

  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

?>
