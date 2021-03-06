<?php
/*
  Copyright (c) 2008-2010, Neutron Soutmun <neo.neutron@gmail.com>
  All rights reserved.

  Redistribution and use in source and binary forms, with or without 
  modification, are permitted provided that the following conditions 
  are met:

  1. Redistributions of source code must retain the above copyright 
     notice, this list of conditions and the following disclaimer.
  2. Redistributions in binary form must reproduce the above copyright 
     notice, this list of conditions and the following disclaimer in the 
     documentation and/or other materials provided with the distribution.
  3. The names of the authors may not be used to endorse or promote products 
     derived from this software without specific prior written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
  AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
  THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR 
  PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
  BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, 
  OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
  INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
  CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
  POSSIBILITY OF SUCH DAMAGE.

  This code cannot simply be copied and put under the GNU Public License or 
    any other GPL-like (LGPL, GPL2) License.
*/

session_start();
ob_start();
require_once 'rahu_radius.class.php';
require_once 'rahu_xmlrpc.class.php';
require_once 'getmacaddr.php';
require_once 'config.php';
require_once 'header.php';
require_once 'locale.php';
require_once 'messages.php';
require_once 'networkchk.php';

function secs_to_human($secs) {
  $units = array (
    "day"     => array ("unit" => _("day"),
                        "plural_unit" => _("days"),
                        "div" => 24*3600),
    "hour"    => array ("unit" => _("hour"),
                        "plural_unit" => _("hours"),
                        "div" =>    3600),
    "minute"  => array ("unit" => _("minute"),
                        "plural_unit" => _("minutes"),
                        "div" =>      60),
    "second"  => array ("unit" => _("second"),
                        "plural_unit" => _("seconds"),
                        "div" =>       1),
  );

  if ($secs == 0)
    return "$secs " . $units["second"]["plural_unit"];

  $s = "";

  foreach ($units as $unit) {
    if ($n = intval($secs / $unit["div"])) {
      $s .= "$n " . (abs($n) > 1 ? $unit["plural_unit"] : $unit["unit"]) . ", ";
      $secs -= $n * $unit["div"];
    }
  }

  return substr ($s, 0, -2);
}

$current_url = $_SERVER['REQUEST_URI'];
$interval = 60;
$auto_refresh = false;

if ($auto_refresh) {
  header("Refresh: $interval; url=$current_url");
}

$ip = $_SERVER['REMOTE_ADDR'];
$config = get_config_by_network($ip, $config_list);
$vserver_id = $config["VSERVER_ID"];

$forward_uri  = $config['NAS_LOGIN_PROTO'] . "://" . $config['NAS_LOGIN_HOST'];
$forward_uri .= !empty($config['NAS_LOGIN_PORT']) ? ":" . $config['NAS_LOGIN_PORT'] : "";
$forward_uri .= "/login.php?sss=" . time();

$request_url = $_SESSION['request_url'];
$request_url_text = strlen($request_url) < 20 ? $request_url : substr($request_url, 0, 20) . " ...";



$xmlrpc = new rahu_xmlrpc_client();
$xmlrpc->host = $config["RAHUNAS_HOST"];
$xmlrpc->port = $config["RAHUNAS_PORT"];
$valid = false;
$isinfo = false;
$isstopacct = false;
$info = array();
$retinfo = $xmlrpc->do_getsessioninfo($vserver_id, $ip);
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
    $result = $xmlrpc->do_stopsession($vserver_id, $ip, returnMacAddress(), 
                                      RADIUS_TERM_USER_REQUEST);
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
  $result = $xmlrpc->do_getsessioninfo($vserver_id, $ip);
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
  "   <td align='right'><b>" . _("Session End") . ":</b></td>" .
  "   <td>". ($info['session_timeout'] == 0 ? _("Never") : strftime('%e %B %Y %H:%M:%S', $info['session_timeout']) . "</td>") .
  " </tr>" .
  "" .
  "  <tr>" .
  "   <td align='right'><b>" . _("Session Time") . ":</b></td>" .
  "   <td>" . secs_to_human(time() - $info['session_start']) . "</td>" .
  " </tr>" .
  "" .
  (strcmp ($info['serviceclass_description'], "(null)") == 0 ? "" :
  "  <tr>" .
  "   <td align='right'><b>" . _("Class of service") . ":</b></td>" .
  "   <td>" . $info['serviceclass_description'] . "</td>" .
  " </tr>") .
  "" .
  "  <tr>" .
  "   <td align='right'><b>" . _("Request URL") . ":</b></td>" .
  "   <td><a href='$request_url' target='_new'>$request_url_text</a></td>" .
  " </tr>" .
  "</table>".
  "<table>".
  " <tr>" .
  "   <td>&nbsp;<input type='hidden' name='do_logout' value='yes'></td>" .
  "   <td><input type='button' value='" . _("OK") . "' id='rh_goto_button' onClick='window.open(\"".$request_url."\");'></td>" .
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
