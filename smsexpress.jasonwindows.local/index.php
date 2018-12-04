<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- /bootstrap config -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="./img/jwlogo.png">
    <!-- /old scripts -->
    <body onload="UpdateMessageList()">
  <script src="./lib/SendSMS.js"></script>
  <script src="./lib/jquery-2.1.3.js"></script>
  <script src="./lib/jquery.csv.js"></script>
  <script src="./lib/JWSMS3.js"></script>

    <title>Jason SMS Express</title>

    <!-- Bootstrap core CSS -->
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./bootstrap/css/narrow-jumbotron.css" rel="stylesheet">
  </head>
<!-- /main portion -->
  <body>

  <!--navbar 
  -->

  <img src="./img/testbanner.png" alt="..." class="img-responsive" width="100%" style="box-shadow: 0px 3px 5px grey;">  
    
  <!-- end of navbar 
  -->
    <div class="container">
      <header class="header clearfix">
      
        <nav>
          <ul class="nav nav-pills float-right">
            <li class="nav-item">
              <a class="nav-link active" href=>Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="./Log/viewUserLog.php">User log</a>
            </li>
          </ul>
        </nav>
        <h3 class="h3">SMS Express</h3>
      </header>
      <main role="main">
<!-- /old check if the user can access the SMS list
 -->
 <div id="Warnings"class="alert alert-danger" role="alert"></div>
 
<?PHP
  require "conf/LDAPFunctions.php";
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
<!-- /end of PHP -->

 <!-- /buttons and file attachments area
 -->
        <div class="jumbotron" id="Mainband">
         <!-- <h1 class="display-6">My messages</h1> -->
<!-- /buttons area
 --><p class="text-md-left">
      <img src="img/globe-3x.png"> 
    &nbsp;&nbsp;Hello <?php print($_SERVER['REMOTE_USER'])?>
    </p>
 <!-- /username
 -->
          <p class="text-md-left">Enter a message and number into the box below. Hit ''Add'' to add to the Send list, then 'Send all'</p>
          
        
          
<!--  /user enter phone number area
--> 
          <textarea class="form-control rounded-2" 
          id="DEDest"
           rows=1 
           maxlength=17 
           placeholder="Enter phone number"
          ></textarea><br />
          
<!--  /user enter message area
 -->

 <p class="text-md-left"> <a 
                          id="Helperimg"
                          data-toggle="tooltip"
            title="place reference no. after 'Ref:' for it to be in response!">
            <img src="img/envelope-closed-3x.png">
            </a>&nbsp; &nbsp; Enter your message below (Maximum 240 characters) </p>
          <textarea 
          class="form-control rounded-2" 
          id="DEText" 
          rows=8
          maxlength=456 
          placeholder="Enter message"
          style="margin-top: 0px;
                 margin-bottom: 0px; 
                height:80px"> A notification from Jason Windows: Ref:
          </textarea>
        </div>
<!--  /buttons and file attachments 
-->
<p class="text-sm-left"><button class="btn btn-primary btn-md" id="AddDEMsg" onclick="AddDEMsg();" role="button">Add</button></p>
        <div class="container"> 
       <!-- <label class="InputLabel" for="EnterMsg">
            <button type="button" id="EnterMsg" onclick='$("div#DirectEntryFields").toggle();'></button>
            <span>Enter Message</span>
          </label> -->
          </main>
        
          <br>
<table id="messageList" class="table responsive">
</table>
<br>
      <div id="ListActions">
      <span id="MessagesReady">
      <label class="InputLabel" id="SendAllLabel" for="SendAll">
        <button type="button" class="btn btn-sm btn-success" id="SendAll" onclick="SendAll();"><b><img src="img/media-play-2x.png"></b></button>
        <span>Send All</span>
      </label>
      <br />
      </span>
      <br />
      <!-- message area
          </label> -->
          <label class="InputLabel" for="ClearAll">
            <button type="button" class="btn btn-sm btn-danger" id="ClearAll" onclick="ClearAll();"><img src="img/trash-2x.png"></button>
            <span>Clear All</span>
        </label>
        <!-- help button before messages
        -->
  <hr> <!-- help button before messages
        -->
        <h6>Add CSV file for bulk message</h6>
        <a
        id="Helperimg"
        data-toggle="tooltip"
            title="File help! the format of your csv is [Number], [message] 
            ie. 0410xxxxx,my message 
                0420xxxxx,my second message
                this can be done in excel using the [save as CSV] option"
        ><img 
            src="img/question-mark-2x.png" 
            class="img-thumbnail"
            style="margin-bottom:10px;"
        ></a>
          <!-- message area
          </label> -->
        <br>
        
          <label class="InputLabel" for="LoadFiles">
            <input type=file id="LoadFiles" name=files[] multiple onclick="return ConfirmClear()"/>
            <span>Load File(s) &nbsp;<img src="img/folder.png"></span> 
          </label>
          <!-- message area
          </label 2 with its own help -->

  <hr> <!-- help button before messages
        -->
          <h6>Add picture for MMS</h6>
        <a
        id="Helperimg"
        data-toggle="tooltip"
            title="File help! Add a picture for MMS "
        ><img 
            src="img/question-mark-2x.png" 
            class="img-thumbnail"
            style="margin-bottom:10px;"         
        ></a>
          <!-- message area
          </label> -->
        <br>
          <label class="InputLabel" for="AddFiles">
            <input type=file id="AddFiles" name=files[] multiple />
            <!--image -->
            <span>Add File(s) &nbsp;&nbsp;<img src="img/image-2x.png"></span>
          </label>
      </div> <!-- /end listactions -->
    </div> <!-- /container -->
  <!-- /buttons and file attachments -->
<!-- /old
  <div id="DirectEntryFields">
      ><br />
      <label class="InputLabel" for="AddDEMsg">
        <button type="button" id="AddDEMsg" onclick="AddDEMsg();"></button>
        <span>Add Message</span>
      </label>
    </div>
    -->
    <!-- /main portion -->
    <footer class="footer">
        <p>&nbsp;&nbsp;&nbsp;&nbsp;© Jason Windows 2018</p> 
    </footer>
    <script>
   $("div#Warnings").hide();
</script>
<!-- /main portion js popper etc
[DATA SECTION]  ------------------------------
[DOCUMENTATION]
  [REQUIRED FILES]

  [BANNER]

  [FIELD ENTRY AREAS]

  [TABLE]

  [FOLDER SELECT]

  [FOOTER]

  
[NAME]  [LASTNAME]  [VERSION]  [DATE DDMMMYY]  [NOTES]
|-------|-----------|----------|---------------|------
JOEL    MARTINEZ    1.00        21OCT18         //INIT REL CREATED FORMS AND FIELDS 
                                                

-->
</body></html>