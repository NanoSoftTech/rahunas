<?php
session_start();
ob_start();
require_once 'rahu_radius.class.php';
require_once 'rahu_xmlrpc.class.php';
require_once 'getmacaddr.php';
require_once 'config.php';
require_once 'header.php';
require_once 'locale.php';
require_once 'messages.php';

$current_url = $_SERVER['REQUEST_URI'];
$interval = 60;
$auto_refresh = false;

if ($auto_refresh) {
  header("Refresh: $interval; url=$current_url");
}

$forward_uri  = $config['NAS_LOGIN_PROTO'] . "://" . $config['NAS_LOGIN_HOST'];
$forward_uri .= !empty($config['NAS_LOGIN_PORT']) ? ":" . $config['NAS_LOGIN_PORT'] : "";
$forward_uri .= "/login.php?sss=" . time();

$request_url = $_SESSION['request_url'];
$request_url_text = strlen($request_url) < 20 ? $request_url : substr($request_url, 0, 20) . " ...";

$ip = $_SERVER['REMOTE_ADDR'];
$xmlrpc = new rahu_xmlrpc_client();
$xmlrpc->host = $config["RAHUNAS_HOST"];
$xmlrpc->port = $config["RAHUNAS_PORT"];
$valid = false;
$isinfo = false;
$isstopacct = false;
$info = array();
$retinfo = $xmlrpc->do_getsessioninfo($ip);
if (is_array($retinfo)) {
  // Send stop accounting to Radius
  $ip =& $retinfo["ip"];
  $username =& $retinfo["username"];
  $session_id =& $retinfo["session_id"];
  $session_start =& $retinfo["session_start"];
  $mac_address =& $retinfo["mac_address"];
  $isinfo = true;
} else {
  $valid = false;
}

if (!empty($_POST['do_logout'])) {
  if ($isinfo) {
    $result = $xmlrpc->do_stopsession($ip, returnMacAddress());
    if ($result === true) {
      $valid = false;
      $message = get_message('OK_USER_LOGOUT');
      $isstopacct = true;
    } else {
      $valid = false;
      $message = get_message('ERR_LOGOUT_FAILED');
      $show_info = true;
    }
  }

  if ($isstopacct) {
    // Send account stop to radius
    $racct = new rahu_radius_acct ($username);
    $racct->host = $config["RADIUS_HOST"];
    $racct->port = $config["RADIUS_ACCT_PORT"];
    $racct->secret = $config["RADIUS_SECRET"];
    $racct->nas_identifier = $config["NAS_IDENTIFIER"];
    $racct->nas_ip_address = $config["NAS_IP_ADDRESS"];
    $racct->nas_port = $config["NAS_PORT"];
    $racct->framed_ip_address  = $ip;
    $racct->calling_station_id = $mac_address;
    $racct->terminate_cause = RADIUS_TERM_USER_REQUEST;
    $racct->session_id    = $session_id;
    $racct->session_start = $session_start;
    $racct->acctStop();
  }
} else {
  $show_info = true;
}

if ($show_info) {
  $result = $xmlrpc->do_getsessioninfo($ip);
  if (is_array($result)) {
    if (!empty($result['session_id'])) {
      $valid = true;
      $info = $result;
    } else {
      $message = get_message('ERR_PLEASE_LOGIN');
    }
  }
}
?>

<?php
// Login box
$valid_text = !$valid ? "" : "" . 
  "  <table id='rh_bg'>" .
  "  <tr>" .
  "   <td align='right'><b>" . _("Username") . ":</b></td>" .
  "   <td>". $info['username']."</td>" .
  " </tr>" .
  "  <tr>" .
  "   <td align='right'><b>" . _("Session Start") . ":</b></td>" .
  "   <td>". strftime('%e %B %Y %H:%M:%S', $info['session_start']) . "</td>" .
  " </tr>" .
  "  <tr>" .
  "   <td align='right'><b>" . _("Session Time") . ":</b></td>" .
  "   <td>" . (time() - $info['session_start']) . " ". _("seconds") . "</td>" .
  " </tr>" .
  "" .
  "  <tr>" .
  "   <td align='right'><b>" . _("Expired") . ":</b></td>" .
  "   <td>". ($info['session_timeout'] == 0 ? _("Never") : strftime('%e %B %Y %H:%M:%S', $info['session_timeout']) . "</td>") .
  " </tr>" .
  "" .
  "  <tr>" .
  "   <td align='right'><b>" . _("Request URL") . ":</b></td>" .
  "   <td><a href='$request_url' target='_new'>$request_url_text</a></td>" .
  " </tr>" .
  "</table>".
  "<table>".
  " <tr>" .
  "   <td>&nbsp;<input type='hidden' name='do_logout' value='yes'></td>" .
  "   <td><input type='button' value='" . _("Go! Go! Go!") . "' id='rh_goto_button' onClick='window.open(\"".$request_url."\");'></td>" .
  "   <td><input type='submit' value='" . _("Logout") . "' id='rh_logout_button'></td>" .
  " </tr>" .
  "</table>";
$request_uri = $_SERVER['REQUEST_URI'];
$loginbox = "<form name='login' action='$request_uri' method='post'>" .
            "  $valid_text ". 
            "</form>";

$forward_script  = $valid == false ? "self.location.replace('$forward_uri');" : "";

$waiting_show  = $forward ? "visible_hide(wt, 'show');" 
                          : "visible_hide(wt, 'hide');";
$message_show  = !empty($message) ? "visible_hide(msg, 'show');" 
                                  : "visible_hide(msg, 'hide');";
$hide_wait = !empty($message) ? "setTimeout('hide_wait();', 2000);\n" : "";

$loginscript = "<script>" .
               "var msg=(document.all);\n" .  
               "var ns4=document.layers;\n" .
               "var ns6=document.getElementById&&!document.all;\n" .
               "var ie4=document.all;\n" .
               "if (ns4)" .
               "  msg=document.rh_message;\n" .
               "else if (ns6)" .
               "  msg=document.getElementById('rh_message').style;\n" .
               "else if (ie4)" .
               "  msg=document.all.rh_message.style;\n\n" .
               "var wt=(document.all);\n" .  
               "if (ns4)" .
               "  wt=document.rh_waiting;\n" .
               "else if (ns6)" .
               "  wt=document.getElementById('rh_waiting').style;\n" .
               "else if (ie4)" .
               "  wt=document.all.rh_waiting.style;\n\n" .
               "function visible_hide(obj, type)\n" .
               "{\n" .
               "  if(type == 'show') {\n" .
               "    if(ns4){obj.visibility='visible';} \n" .
               "    else if (ns6||ie4) obj.display='block';\n".
               "  } else {\n".
               "    if(ns4){obj.visibility='hidden';} \n" .
               "    else if (ns6||ie4) obj.display='none';\n".
               "  }\n".
               "}\n".
               "function hide_wait() {\n".
               "  visible_hide(msg, 'hide');\n".
               "  visible_hide(wt, 'hide');\n".
               "  $forward_script \n" .
               "}\n".
               "  $message_show \n".
               "  $waiting_show \n".
               "  $hide_wait \n".
               "</script>";
$watting_script="";

$waiting  = "<div id='rh_waiting'><img src='loading.gif'></div>";
$loginmsg = "<div id='rh_message'>$message</div>";
$loginbox .= $waiting;
$loginbox .= $loginmsg;
?>

<?php
// Template loading
$tpl_path = "templates/" . $config['UAM_TEMPLATE'] . "/";
$tpl_file = $tpl_path . $config['UAM_TEMPLATE'] . ".html";
$handle = @fopen($tpl_file, "r");
$html_buffer = "";
if ($handle) {  
  $css = "<link rel='stylesheet' type='text/css' href='" . $tpl_path . "rahunas.css'>";
  $loginbox = $css . $loginbox;

  while (!feof($handle)) {
    $html_buffer .= fgets($handle, 4096);
  }
  fclose($handle);

  $html_buffer = str_replace("images/", $tpl_path."images/", $html_buffer);
  $html_buffer = str_replace("<!-- Title -->", $config["NAS_LOGIN_TITLE"], 
                             $html_buffer);
  $html_buffer = str_replace("<!-- Login -->", $loginbox, $html_buffer);
  $html_buffer = str_replace("<!-- JavaScript -->", $loginscript, $html_buffer);
  print $html_buffer;
}


ob_end_flush();
?>
