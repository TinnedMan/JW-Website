function SendSMS (Message) {
  recipient = Message.recipient;
  text = Message.text;
  var xhr = new XMLHttpRequest();

  URL = "/Send/SendSMS.php";

  text = encodeURIComponent(text);
  recipient = encodeURIComponent(recipient);

  querystring = "?dst=" + recipient + "&txt=" + text;

  // We need to use an anonymous function here rather than calling handleSend... directly so that we create
  // a closure with Message in scope, it's a little bit kludgy but it works.
  xhr.onload = function () {handleSendSMSComplete(Message, this.status, this.statusText);};
  xhr.ontimeout = function () {handleSendSMSComplete(Message, 504, "Timeout waiting for local SendSMS service.");};

  xhr.open("GET", URL + querystring, true);
  xhr.timeout = 120000;

  xhr.send();
}
