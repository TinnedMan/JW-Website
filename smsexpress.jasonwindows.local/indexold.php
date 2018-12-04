<!DOCTYPE html>

<html>
<head>
  <link href="JWSMS2.css" type="text/css" rel="stylesheet" />
</head>
<body onload="UpdateMessageList()">

 
  <script src="lib/SendSMS.js"></script>
  <script src="lib/jquery.js"></script>
  <script src="lib/jquery.csv.js"></script>
  <script src="lib/JWSMS2.js"></script>

  <div id="title">
    <img src="img/jwlogo.png" />
    TESTJWSMS v2.1
  </div>
  <br />
 
  <div id="MainPanel">

<?PHP
  include "conf/LDAPFunctions.php";
  set_time_limit(5);

  $user = GetUserIfInGroup($_SERVER['REMOTE_USER'], "CN=SMS Users,OU=Security,OU=Groups,OU=Accounts,DC=jasonwindows,DC=local");

  if (!$user) { 
    print("<div id='PermWarn'>");
    print("User " . $_SERVER['REMOTE_USER'] . " was not found in the SMS Users group.");
    print("<h2>You will not be able to send messages.</h2>");
    print("Please log an ICT request if you require this functionality.<br />");
    print("</div>");
  }

?>

    <div id="Warnings"></div>

    <table id="messageList">
    </table>

    <div id="ListActions">
      <span id="MessagesReady">
      <label class="InputLabel" id="SendAllLabel" for="SendAll">
        <button type="button" id="SendAll" onclick="SendAll();"></button>
        <span>Send All</span>
      </label>
      <br />
      </span>
      <br />

      <label class="InputLabel" for="EnterMsg">
        <button type="button" id="EnterMsg" onclick='$("div#DirectEntryFields").toggle();'></button>
        <span>Enter Message</span>
      </label>
      <label class="InputLabel" for="LoadFiles">
        <input type=file id="LoadFiles" name=files[] multiple onclick="return ConfirmClear()"/>
        <span>Load File(s)</span>
      </label>
      <label class="InputLabel" for="AddFiles">
        <input type=file id="AddFiles" name=files[] multiple />
        <span>Add File(s)</span>
      </label>
      <label class="InputLabel" for="ClearAll">
        <button type="button" id="ClearAll" onclick="ClearAll();"></button>
        <span>Clear All</span>
      </label>

    <br /><br />
    </div>

    <div id="DirectEntryFields">
      <textarea id="DEDest" rows=1 maxlength=17 placeholder="Enter phone number"></textarea><br />
      <textarea id="DEText" rows=8 maxlength=456 placeholder="Enter message">A notification from Jason Windows:
</textarea><br />
      <label class="InputLabel" for="AddDEMsg">
        <button type="button" id="AddDEMsg" onclick="AddDEMsg();"></button>
        <span>Add Message</span>
      </label>
    
    </div>

    <div id="links">
      <a href="/Log/viewUserLog.php">User Log</a>
    </div>

  </div>

</body>
</html>
