

messages = [ ];
clearOnLoad = false;

$(document).ready(function() {
  
  if(isAPIAvailable()) {
    $('#LoadFiles').bind('change', handleFileSelect);
    $('#AddFiles').bind('change', handleFileSelect);
    
    //JAM
    // >.< javascript!!!!
    //init tooltip
   // $('#Helperimg').tooltip();
// alert the user that their message has exceeded the length
          
          $("#DEText").keypress(function() {
            //display your warinig the way you chose
            $(this).css('height','auto');
            $(this).height(this.scrollheight);
            if ($(this).val().length > 430 && $('#divWarnings').text() == "")
             {
              AddWarning( 'Characters left: ' + (456 - $(this).val().length) )
            } else {
              ClearWarnings();
            }
          
            //AddWarning("Characters left: " + (100 - $(this).val().length));
          });
  }

 // window.onbeforeunload = function (e) {return "If you leave this page, any unsent messages will be lost.";};


  $(window).bind('beforeunload', function(){
    if( UnsentMessageCount() > 0 ) {
        return "If you leave this page, any unsent messages will be lost.";
    }
  });

  EnableTooltips();

});


function UnsentMessageCount() {
  count = 0
  for (var idx = 0; idx < messages.length; idx++) {
    if (! messages[idx].sent) {
      count = count + 1;
    }
  }
  return count;

}

// Check for the various File API support.
function isAPIAvailable() {
  if (window.File && window.FileReader && window.FileList && window.Blob) {
    // All the File APIs are supported.
    return true;
  } else {
    alert ("You appear to be accessing this page using an unsupported browser. Please use IE11+ or log an ICT request for assistance.");
    return false;
  }
}


// Callback function after sending a message
function handleSendSMSComplete(message, status, statusText) {

  message.sentsuccess = (status == 202);
  message.sentinfo = statusText;

  UpdateMessageList();

}

// Called when one of the file fields changes
function handleFileSelect(evt) {
  var files = evt.target.files; // FileList object
  if (files.length > 0) {
    ClearWarnings();
    if (clearOnLoad) {
      messages = [ ];
      clearOnLoad = false;
      UpdateMessageList();
    }
    for (var idx = 0; idx < files.length; idx++){
      loadSMSFile(files[idx]);
    }
    evt.target.value = null;
  }
}

function EnableTooltips () {
  $('.ttip').unbind("mouseenter");
  $('.ttip').unbind("mouseleave");
  $('.ttip').hover(function(){
          // Hover over code
          var title = $(this).attr('title');
          $(this).data('tipText', title).removeAttr('title');
          $('<p class="tooltip"></p>')
          .text(title)
          .appendTo('body')
          .fadeIn("slow");
  }, function() {
          // Hover out code
          $(this).attr('title', $(this).data('tipText'));
          $('.tooltip').remove();
  }).mousemove(function(e) {
          var mousex = e.pageX + 20; //Get X coordinates
          var mousey = e.pageY + 10; //Get Y coordinates
          $('.tooltip')
          .css({ top: mousey, left: mousex })
  });

}
// need to write an alert

// Reads a CSV file, identifies the format and calls the relevant parser
function loadSMSFile(file) {
  var reader = new FileReader();
  reader.readAsText(file);
  reader.onload = function(event){
    var csv = event.target.result;
    var data = $.csv.toArrays(csv);
    var newmessages;
    if (data[0][0].toUpperCase() == "TEMPLATE"){
      // Excel pads with empty fields, so we can't check for ==2
      if (data[0].length < 2) {
        AddWarning("Template header must have two fields.");
      } else {
        ParseTemplateData(file.name, data);
      }
    } else {
      ParseFixedData(file.name, data);
    }
  };
  reader.onloadend = function(event){
    UpdateMessageList();
  }
  reader.onerror = function(){ alert('Unable to read ' + file.fileName); };
}

function ParseFixedData(filename, data) {
  
  for(var row in data) {

    // Ignore header lines
    if (data[row][0].substr(0,2) == "&&") continue;


    if (data[row].length != 2) {
      AddWarning("Could not add row " + (row + 1)+ ". Non-template messages must have exactly two fields.");
      continue;
    }

    // Silently discard blank lines
    if (data[row].filter(function(n){ return n != undefined }) == null) {
      continue;
    }

    var rcpt = data[row][0];
    var text = data[row][1];

    AddMessage(rcpt, text, filename + " row " + (row + 1));

  }

};

function ParseTemplateData(filename, data) {

  var Template = data[0][1];
  var placeholder = /~/;

  for(var idx = 1; idx < data.length; idx++) {
    var fields = data[idx];

    // Ignore header lines
    if (fields[0].substr(0,2) == "&&") continue;

    // Silently discard blank lines
    if (fields.filter(function(el){ return el != "" }).length == 0) {
      continue;
    }

    var rcpt = fields.shift();
    var text = Template;

    while (text.match(placeholder) && fields.length > 0) {
      text = text.replace(placeholder, fields.shift());
    }

    AddMessage(rcpt, text, filename + " row " + (idx + 1));
  }

};

function AddMessage(rcpt, text, reference) {
  
  rcpt = ValidatePhoneNo(rcpt);
  if (rcpt == null) {
    AddWarning("Could not add " + reference + ". Invalid phone number.");
    return 1;
  }

  safetext = SanitizeMessage(text);

  if ( safetext.length == 0 ) {
    AddWarning("Could not add " + reference + ". Message must contain at least 1 valid character.");
    return 1;
  }

  var newmessage = {recipient: rcpt,  text: safetext, sent: false, origtext: text};
  messages.push(newmessage);
}

// Remove a message from the array and update the display
function delMsg(idx) {
  messages.splice(idx,1);
  $('.tooltip').remove()
  UpdateMessageList();
}


// Clear the sent status of a message so that it will be resent
function clearSentStatusMsg(idx) {
  messages[idx].sentsuccess = undefined;
  messages[idx].sentinfo = undefined;
  messages[idx].sent = false;
  $('.tooltip').remove()
  UpdateMessageList();

}

// Display an error at the top of the page jsms3

function AddWarning(message) {
$("div#Warnings").show();
if ($("div#Warnings").text() != "")
        {
         $("div#Warnings").attr('class', 'alert alert-danger');
         
        } else {
         $("div#Warnings").attr('class', 'alert alert-danger');
         $("div#Warnings").append(message);
         $("div#Warnings").fadeOut(3000); 
        
               } 
              
/*function AddWarning(message) {
   if ($("div#Warnings").html() != "")
        {
         $("div#Warnings").append("<br />");
        }
    /* else	{
         $("div#Warnings").remove(); 
        } */ 
//-------------- remove the warning  */ // OLD BACKUP

}

function ClearWarnings() {
  $("div#Warnings").html("");
}

function SanitizeMessage(message) {
  var result = message.replace(/[^A-Za-z0-9@$_\/.,\"():;\-=+&#%!?<>' \\*\n\r]/g, "");
  result = result.substr(0,456);
  return result;
}

function ValidatePhoneNo(number) {
  number = number.replace(/[^\d+]/g, "");
  number = number.replace(/^\+/, "");
  number = number.replace(/^4|04/, "614");
  
  var RE = new RegExp(/^(?:\(?(?:\+?61|0)4\)?(?:[ -]?[0-9]){7}[0-9])$/);

  return number.match(RE);
}

function AddDEMsg() {
  ClearWarnings();

  var rcpt = $("textarea#DEDest").val();
  var text = $("textarea#DEText").val();

  AddMessage(rcpt, text, "direct entry message");
  UpdateMessageList();
}

function ConfirmClear() {
  if ( messages.length > 0 ) {
    var answer = confirm ("This will clear the current message list. Are you sure?");
    if (answer) {
      clearOnLoad = true;
    }
    return answer;
  }
  return true;
}

function ClearAll() {
  ConfirmClear();
  if (clearOnLoad) {
    ClearWarnings();
    messages = [ ];
    clearOnLoad = false;
    UpdateMessageList();
  }
}

function HtmlEncode(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

}

function UpdateMessageList() {
  line = "<tr><th width=300>Recipient</th><th width=800>Message</th>";
  line += "<th width=130>Result</th><th>Actions</th></tr>";
  $("table#messageList").html(line);
  for (var idx = 0; idx < messages.length; idx++) {
    line = "<tr id='row" + idx + "'><td>" + messages[idx].recipient + "</td>";
    line += "<td";
    if (messages[idx].text != messages[idx].origtext) {
      line += ' class="ModifiedMsg ttip" title="';
      line += 'Message has been modified. Original Content: ';
      line += HtmlEncode(messages[idx].origtext) + '"' ;
    }
    line += ">" + HtmlEncode(messages[idx].text) + "</td>";
    line += '<td class="status">Unsent</td>';
    line += '<td class="actions">';
    line += '<img src="img/del.png" onclick="delMsg(' + idx + ')" class="ttip" title="Delete message"/>';
    if (messages[idx].sentsuccess !== undefined) {
      line += '<img src="img/resend.png" onclick="clearSentStatusMsg(' + idx + ')" class="ttip" title="Resend message"/>';
    }
    line += '</td></tr>';
    $("table#messageList").append(line);
    SetStatusCell(idx);
  }
  if (messages.length > 0) {
    $("span#MessagesReady").show();
  } else {
    $("span#MessagesReady").hide();
    line = "<tr><td colspan='4'>No messages loaded.</td></tr>";
    $("table#messageList").append(line);
  }
  EnableTooltips();
}

function SendAll() {
  for (var idx = 0; idx < messages.length; idx++) {
    if (! messages[idx].sent) {
      //TODO: TKTK Do we need to lock the UI so that people can't delete messages while they are being sent?
      //           As long as the callback function retains a handle on the message, updating the status should work
      //           and if the user doesn't want to see the message any more, does it matter? Just don't reference messages by index
      //SendSMS(messages[idx].recipient, messages[idx].text);
      SendSMS(messages[idx]);
      messages[idx].sent = true;
      var now = new Date();
      messages[idx].sentinfo = "Queued for delivery at: " + now.toLocaleTimeString() + " " + now.toDateString();
    }
    SetStatusCell(idx);
  }
}


// TODO: TKTK This should be deprecated, it's not safe to refer to the table by index when the send requests are async
//            We need to either store a reference to the cell with the message when the table is redrawn, or completely
//            redraw the table when data changes.
function SetStatusCell(idx) {
  if (! messages[idx].sent) {
    return 0;
  }
  if (messages[idx].sentsuccess === undefined) {
    output = "<img src='img/sending.gif'>";
  } else if (messages[idx].sentsuccess === true) {
    output = "<img src='img/tick.png'>";
  } else {
    output = "<img src='img/cross.png'>";
  }

  $("table#messageList  tr#row" + idx + " td.status").html(output);
  $("table#messageList  tr#row" + idx + " td.status").addClass("ttip");
  $("table#messageList  tr#row" + idx + " td.status").prop("title", messages[idx].sentinfo);
  EnableTooltips();
}

    
    