messages = [ ];
clearOnLoad = false;

$(document).ready(function() {
  if(isAPIAvailable()) {
    $('#LoadFiles').bind('change', handleFileSelect);
    $('#AddFiles').bind('change', handleFileSelect);
  }

  EnableTooltips();

});

function isAPIAvailable() {
  // Check for the various File API support.
  if (window.File && window.FileReader && window.FileList && window.Blob) {
    // Great success! All the File APIs are supported.
    return true;
  } else {
    alert ("You appear to be accessing this page using an unsupported browser. Please use IE11+ or log an ICT request for assistance.");
    return false;
  }
}

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
          .fadeIn('slow');
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
    if (data[row].length != 2) {
      AddWarning("Could not add row " + (row + 1)+ ". Non-template messages must have exactly two fields.");
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
    var rcpt = fields.shift();

    var text = Template;

    while (text.match(placeholder) && fields.length > 0) {
      text = text.replace(placeholder, fields.shift());
    }

    AddMessage(rcpt, text, filename + " row " + idx);
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

function RemoveMessage(idx) {
  messages.splice(idx,1);
}

function delMsg(idx) {
  RemoveMessage(idx);
  UpdateMessageList();
}

function AddWarning(message) {
  if ($("div#Warnings").html() != "") {
    $("div#Warnings").append("<br />");
  }
  $("div#Warnings").append(message);
}

function ClearWarnings() {
  $("div#Warnings").html("");
}

function SanitizeMessage(message) {
  var result = message.replace(/[^A-Za-z0-9@$_\/.,\"():;\-=+&%#!?<>' \n]/g, "");
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

function UpdateMessageList() {
  line = "<tr><th width=300>Recipient</th><th width=800>Message</th>";
  line += "<th width=100>Result</th><th>Actions</th></tr>";
  $("table#messageList").html(line);
  for (var idx = 0; idx < messages.length; idx++) {
    line = "<tr id='row" + idx + "'><td>" + messages[idx].recipient + "</td>";
    line += "<td";
    if (messages[idx].text != messages[idx].origtext) {
      line += ' class="ModifiedMsg ttip" title="';
      line += 'Message has been modified. Original Content: ';
      line += messages[idx].origtext + '"' ;
    }
    line += ">" + messages[idx].text + "</td>";
    line += '<td class="status">Unsent</td>';
    line += '<td class="actions"><img src="img/del.png" onclick="delMsg(' + idx;
    line += ')" /></td></tr>';
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
      result = SendSMS(messages[idx].recipient, messages[idx].text);
      messages[idx].sent = true;
      messages[idx].sentsuccess = result.success;
      messages[idx].sentinfo = result.info;
    }
    SetStatusCell(idx);
  }
}

function SetStatusCell(idx) {
  if (! messages[idx].sent) {
    return 0;
  }
  if (messages[idx].sentsuccess) {
    output = "<img src='img/tick.png'>";
  } else {
    output = "<img src='img/cross.png'>";
  }

  $("table#messageList  tr#row" + idx + " td.status").html(output);
  $("table#messageList  tr#row" + idx + " td.status").addClass("ttip");
  $("table#messageList  tr#row" + idx + " td.status").prop("title", messages[idx].sentinfo);
  EnableTooltips();
}
