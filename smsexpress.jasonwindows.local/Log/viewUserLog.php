
 <!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- /bootstrap config -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../img/jwlogo.png">
    <title>Jason sms express</title>

    <!-- Bootstrap core CSS -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../bootstrap/css/narrow-jumbotron.css" rel="stylesheet">
  </head>
    <!--data -->

    <img src="../img/testbanner.png" alt="..." class="img-responsive" width="100%" style="box-shadow: 0px 3px 5px grey;">  
 <!--main header

  -->
 <body>
    <div class="container">
      <header class="header clearfix">
        <nav>
          <ul class="nav nav-pills float-right">
            <li class="nav-item">
              <a class="nav-link active" href="/index.php">Home <span class="sr-only"></span></a>
            </li>
            
          </ul>
        </nav>
        <h3>SMS Express</h3>
      </header>
<?PHP


  include "../conf/LDAPFunctions.php";
  include "../conf/SMSFunctions.php";



  set_time_limit(30);

  $user = GetUserIfInGroup($_SERVER['REMOTE_USER'], "CN=SMS Users,OU=Security,OU=Groups,OU=Accounts,DC=jasonwindows,DC=local");

  if (!$user) {
    header("HTTP/1.0 403 Could not validate user: " . $_SERVER['REMOTE_USER']);
    error_log("Could not validate user: " . $_SERVER['REMOTE_USER']);
    exit(1);
  }

  // User has been authenticated and is a member of the SMS group


  $sqlcon = initDB();

  # This is only safe because the parameters are pulled from AD. If you want to build a proper
  # search function that allows users to enter parameters, don't just copy this as it will lead
  # to SQLi, use prepared statements instead

  $sql = "SELECT * FROM sent_messages WHERE from_email = '" . $user["mail"] . "';";
  $result = $sqlcon->query($sql);

  if ($result->num_rows > 0) {A
?>

<table class="table">
  <tr>
    <th scope="col">Message ID</th>
    <th scope="col">To</th>
    <th scope="col" width="50%">Message</th>
    <th scope="col">Sent Date</th>
    <th scope="col">Status Code</th>
  </tr>
   

<?PHP
    // output data of each row
    while($row = $result->fetch_assoc()) {
      echo "<tr><td>" . $row["msg_id"] . "</td><td>" . $row["to_mobile"] . "</td><td>" . htmlentities($row["message"]) ;
      echo  "</td><td>" . $row["sent_time"]  . "</td><td>" . $row["result"] . "</td></tr>";
    }
  } else {
    echo "0 results";
  }
  $sqlcon->close();



?>
