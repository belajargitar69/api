<?php
$json = file_get_contents('php://input');
$data = json_decode($json);

$datajson = file_get_contents('data.json');
$datajson = json_decode($datajson)->flow1;
// print_r($datajson);die();

$api_server = "https://botwa.whusnet.com";
//variable initialization
$instance_key = $data->instance_key;
// $pesan = $data->pesan;
$sender = $data->whatsapp_number;
$nama_sender = $data->verifiedBizName;

if($nama_sender == null){
  $nama_sender = $data->pushName;
}

if (@$data->message->conversation) {
  // format lama
  $pesan = $data->message->conversation;
} else {
  if (@$data->conversation) {
    // format lama
    $pesan = $data->conversation;
  } else {
    $pesan = $data->message->extendedTextMessage->text;
  }
}

if ($pesan == null) {
  // format lama
  $pesan = $data->message->templateButtonReplyMessage->selectedDisplayText;
  // print_r($pesan);
}

if($pesan == null){
  $pesan = $data->message->listResponseMessage->title;
}

//end
// Webhook callback
foreach($datajson as $a => $v){
  if($pesan == $v->tanya ){
    $reply = $v->jawab;
    if($v->type == "text"){
      sendMessage($api_server,$reply,$sender,$instance_key,$nama_sender);
    }else if($v->type == "listMessages"){
      $title = $v->title;
      $text = $v->text;
      $footer= $v->footer;
      $button = $v->button;
      sendList($api_server,$instance_key,$sender,$v->list,$title,$text,$footer,$button,$nama_sender);
    }else if($v->type == "templateMessages"){
      $text = $v->text;
      $footer = $v->footer;
      sendTemplate($api_server,$instance_key,$sender,$v->list,$text,$footer,$nama_sender);
    }
  }
}

// sendMessage($api_server,$reply, $sender, $instance_key);
// function sendmessage
function sendMessage($api_server,$reply, $sender, $instance_key,$nama_sender)
{
  $curl = curl_init();
  $data = [
    "message" => $reply,
    "jid" => $sender,
    "instance_key" => $instance_key,
  ];
  $payload = json_encode($data);
  $ch = curl_init($api_server."/api/sendMessageText");
  # Setup request to send json via POST.
  // echo $api_server."/api/sendMessageText";
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
  # Return response instead of printing.
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  # Send request.
  $result = curl_exec($ch);
  curl_close($ch);
  # Print response.
  print_r($result);
}

function sendTemplate($api_server,$instance_key,$sender,$data,$text,$footer,$nama_sender)
{
  $curl = curl_init();
  $templatebuttons = [];
  foreach ($data as $key => $value) {
    $templatebuttons[] = [
      "index"=> $key,
      "quickReplyButton"=>[
        "displayText"=>$value,
        "id"=>$id."_".$key
      ]
    ];
  }
  $text = str_replace("[nama_pengirim]",$nama_sender,$text);
  // echo str_replace("world","Peter","Hello world!");
  $data = [
    "jid" => $sender,
    "instance_key"=> $instance_key,
    "text"=>$text,
    "footer"=>$footer,
    "templateButtons"=>$templatebuttons
  ];
  // print_r($data);
  $payload = json_encode($data);
  $ch = curl_init("https://botwa.whusnet.com/api/sendTemplateMessages");
  # Setup request to send json via POST.
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',"apikey:$instance_key"));
  # Return response instead of printing.
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  # Send request.
  $result = curl_exec($ch);
  curl_close($ch);
  # Print response.
  print_r($result);

}

function sendList($api_server,$instance_key,$sender,$data,$title,$text,$footer,$button,$nama_sender) {
  $curl = curl_init();
  $sections = [];
  foreach ($data as $key => $value) {
    $sections["rows"][] = [
      "title"=>$value,
      "rowId"=>$id."_".$key
    ];
  }
  $data = [
    "jid" => $sender,
    "instance_key"=> $instance_key,
    "title"=>$title,
    "text"=>$text,
    "footer"=>$footer,
    "imageUrl"=> "https://www.greenscene.co.id/wp-content/uploads/2021/09/One-Piece-11-696x497.jpg",
    "buttonText"=> $button,
    "sections"=>[
      $sections
    ]
  ];
  // print_r($data);
  $payload = json_encode($data);
  $ch = curl_init($api_server."/api/sendListMessage");
  # Setup request to send json via POST.
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',"apikey:$instance_key"));
  # Return response instead of printing.
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  # Send request.
  $result = curl_exec($ch);
  curl_close($ch);
  # Print response.
  print_r($result);
}
