<?php
$host = "127.0.0.1";
$port = 5984;

// Show errors
error_reporting(E_ALL);

// Preserve timeout
set_time_limit(0);

// Create socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");

// Bind socket to port
$result = socket_bind($socket, $host, $port) or die("Could not bind to socket\n");

// Headers
$h_ok = "HTTP/1.1 200 OK\n";
$h_date = "Date: ".gmdate(DATE_RFC7231)."\n";
$h_content = "Content-Type: application/json\n";

// Output MySQL errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Functions
function sendMessage($m, $i = egor, $b1 = "", $b2 = "", $b3 = "", $b4 = "", $b5 = "", $b6 = "", $b7 = "", $b8 = "", $b9 = "") {
	$ch = curl_init(url."sendMessage");
	$m = str_replace('"', '\"', $m);
	$start = '{"chat_id":"'.$i.'","text":"'.$m.'","parse_mode":"html"';
	if (!empty($b1)) {
		$ins = ',"reply_markup":{"inline_keyboard":[';
		$ine="]";
		$btns = jsonButtons($b1, $b2, $b3, $b4, $b5, $b6, $b7, $b8, $b9);
	} else {
		$ins = "";
		$btns = "";
		$ine = "";
	}
	$end = "}";
	$payload = $start.$ins.$btns.$ine.$end;
	$r = execPayload($payload, $ch);
	$u = json_decode($r, true);
	$id = $u['result']['message_id'];
	return $id;
}

function sendLog($m, $i = egor, $b1 = "", $b2 = "", $b3 = "", $b4 = "", $b5 = "", $b6 = "", $b7 = "", $b8 = "", $b9 = "") {
	$ch = curl_init(log."sendMessage");
	$m = str_replace('"', '\"', $m);
	$start = '{"chat_id":"'.$i.'","text":"'.$m.'","parse_mode":"html"';
	if (!empty($b1)) {
		$ins = ',"reply_markup":{"inline_keyboard":[';
		$ine="]";
		$btns = jsonButtons($b1, $b2, $b3, $b4, $b5, $b6, $b7, $b8, $b9);
	} else {
		$ins = "";
		$btns = "";
		$ine = "";
	}
	$end = "}";
	$payload = $start.$ins.$btns.$ine.$end;
	$r = execPayload($payload, $ch);
	$u = json_decode($r, true);
	$id = $u['result']['message_id'];
	return $id;
}

function execPayload($p, $c) {
	global $opt_debug;
	curl_setopt($c, CURLOPT_POSTFIELDS, $p);
	curl_setopt($c, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$r = curl_exec($c);
	curl_close($c);
	return $r;
}

function jsonButtons($b1 = "", $b2 = "", $b3 = "", $b4 = "", $b5 = "", $b6 = "", $b7 = "", $b8 = "", $b9 = ""){
  if (!empty($b1)) {
    $r1 = "";
  	$r2 = "";
  	$r3 = "";
  	$r4 = "";
  	$rs = "[";
  	$re = "],";
  	$num = 1;
    $not_empty = [];

    foreach ([$b1, $b2, $b3, $b4, $b5, $b6, $b7, $b8, $b9] as $btn) {
      if (!empty($btn)) {
        array_push($not_empty, $btn);
      }
    }

  	foreach ($not_empty as $arr) {
  		$vvar = "btn".$num;
  		if (!empty($arr)) {$$vvar = ',{"text":"'.$arr['text'].'", "callback_data":"'.$arr['callback_data'].'"}';} else {$$vvar = "";}
  		if ($arr['row'] == 1) {$r1 .= ${$vvar};}
  		if ($arr['row'] == 2) {$r2 .= ${$vvar};}
  		if ($arr['row'] == 3) {$r3 .= ${$vvar};}
  		if ($arr['row'] == 4) {$r4 .= ${$vvar};}
  		$num++;
  	}

  	$r1 = ltrim($r1,',');
  	$r2 = ltrim($r2,',');
  	$r3 = ltrim($r3,',');
  	$r4 = ltrim($r4,',');
  	$btns = rtrim($rs.$r1.$re.$rs.$r2.$re.$rs.$r3.$re.$rs.$r4.$re,',');
  	return $btns;
  }
}

function editMessage($m, $b1 = "", $b2 = "", $b3 = "", $b4 = "", $b5 = "", $b6 = "", $b7 = "", $b8 = "", $b9 = "") {
	global 	$chat_id,
			$msg_id;
	$ch = curl_init(url."editMessageText");
	$m = str_replace('"', '\"', $m);
	$start = '{"chat_id":"'.$chat_id.'","message_id":"'.$msg_id.'","text":"'.$m.'","parse_mode":"html","disable_web_page_preview":1';
	if (!empty($b1)) {
		$ins = ',"reply_markup":{"inline_keyboard":[';
		$ine="]";
		$btns = jsonButtons($b1, $b2, $b3, $b4, $b5, $b6, $b7, $b8, $b9);
	} else {
		$ins = "";
		$btns = "";
		$ine = "";
	}
	$end = "}";
	$payload = $start.$ins.$btns.$ine.$end;
	$r = execPayload($payload, $ch);
	return [$payload, $r];
}

function btnArray($n, $c, $r) {
	$btn = ['text' => $n, 'callback_data' => $c, 'row' => $r];
	return $btn;
}

function forwardMessage($m, $fi, $i = egor) { // $m — Message ID, $fi — From Chat ID, $i — Chat ID
	$ch = curl_init(url."forwardMessage");
	$data = [
    	'message_id' => $m,
    	'from_chat_id' => $fi,
    	'chat_id' => $i
	];
	$payload = json_encode($data);
	$r = execPayload($payload, $ch);
	$u = json_decode($r, true);
	$id = $u['result']['message_id'];
	return [$payload, $r];
}

function callbackAnswer($t, $a = 0) { // $t — Text, $a — Alert
	global $upd_id;
	$ch = curl_init(url."answerCallbackQuery");
	$data = [
    	'callback_query_id' => $upd_id,
    	'text' => $t,
    	'show_alert' => $a
	];
	$payload = json_encode($data);
	$r = execPayload($payload, $ch);
	return [$payload, $r];
}

function deleteMessage($m, $i) { // $m — Message ID, $i — Chat ID
	if ($m != "0") {$r = file_get_contents(url."deleteMessage?message_id=".$m."&chat_id=".$i);} else {$r = "Error: Zero message id";}
	return $r;
}

function safeSymbols($t) { // $t — Input Text
	$t = str_replace('\\', '\\\\', $t);
	$t = str_replace('\'', '\\\'', $t);
	$t = str_replace('"', '\"', $t);
	return $t;
}

function updHist() {
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col2,
			$hist_col3,
			$hist_col4,
			$hist_col5,
			$hist_col6,
			$hist_col7,
			$hist_col8,
			$msg1,
			$msg2,
			$msg3,
			$chat_id,
			$first_name,
			$text,
			$msg_id;

	$hnum = 3;
	$mnum = 1;
	foreach ([$msg1, $msg2, $msg3] as $arr) {
		$mmsg = "msg".$mnum;
		$hhist = "hist_col".$hnum;
		$$mmsg[0] = $conn->query("SELECT `${$hhist}` FROM `$hist_table` WHERE `$hist_col1` = '$chat_id'")->fetch_object()->${$hhist}; $hnum++;
		$$mmsg[0] = safeSymbols($$mmsg[0]);
		$hhist = "hist_col".$hnum;
		$$mmsg[1] = $conn->query("SELECT `${$hhist}` FROM `$hist_table` WHERE `$hist_col1` = '$chat_id'")->fetch_object()->${$hhist}; $hnum++; $mnum++;
	}

	$r = $conn->query("INSERT INTO `$hist_table` (`$hist_col1`, `$hist_col2`, `$hist_col3`,`$hist_col4`) VALUES('$chat_id','$first_name','$text','$msg_id') ON DUPLICATE KEY UPDATE `$hist_col1`='$chat_id', `$hist_col2`='$first_name', `$hist_col3`='$text', `$hist_col4`='$msg_id',`$hist_col5`='$msg1[0]',`$hist_col6`='$msg1[1]', `$hist_col7`='$msg2[0]', `$hist_col8`='$msg2[1]'");
}

function markTemp($m, $r = 0) { // $m — Message ID, $r — Reset
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col11,
			$hist_col12,
			$hist_col13,
			$del1,
			$del2,
			$del3,
			$chat_id;
	if ($r == 0) {
		$del1 = $m;
		$del2 = $conn->query("SELECT `$hist_col11` FROM `$hist_table` WHERE `$hist_col1` = '$chat_id'")->fetch_object()->$hist_col11;
		$del3 = $conn->query("SELECT `$hist_col12` FROM `$hist_table` WHERE `$hist_col1` = '$chat_id'")->fetch_object()->$hist_col12;

		$r = $conn->query("INSERT INTO `$hist_table` (`$hist_col1`, `$hist_col11`) VALUES('$chat_id','$del1') ON DUPLICATE KEY UPDATE `$hist_col11`='$del1', `$hist_col12`='$del2', `$hist_col13`='$del3'");
	} else {
		$r = $conn->query("INSERT INTO `$hist_table` (`$hist_col1`) VALUES('$chat_id') ON DUPLICATE KEY UPDATE `$hist_col11`='0', `$hist_col12`='0', `$hist_col13`='0'");
	}

	return $r;
}

function getTemp($s) { // $s — Slot
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col11,
			$hist_col12,
			$hist_col13,
			$chat_id;

	if ($s >= 1 && $s <= 3) {
		$hist_col = "hist_col1".$s;
		$r = $conn->query("SELECT `${$hist_col}` FROM `$hist_table` WHERE `$hist_col1` = '$chat_id'")->fetch_object()->${$hist_col};
	} else {
		$r = "Error: Wrong Input Slot";
	}

	return $r;
}

function deleteTemp() {
	global 	$chat_id,
			$del1,
			$del2,
			$del3;
	foreach ([$del1, $del2, $del3] as $m) {
		deleteMessage($m, $chat_id);
	}
	markTemp(0, 1);
}

function setMenuState($v) {
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col9,
			$chat_id;

	$r = $conn->query("INSERT INTO `$hist_table` (`$hist_col1`, `$hist_col9`) VALUES('$chat_id','$v') ON DUPLICATE KEY UPDATE `$hist_col1`='$chat_id', `$hist_col9`='$v'");
}

function getMenuState() {
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col9,
			$chat_id;

	$r = $conn->query("SELECT `$hist_col9` FROM `$hist_table` WHERE `$hist_col1` = '$chat_id'")->fetch_object()->$hist_col9;
	return $r;
}

function setConfirm($v) {
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col14,
			$chat_id;

	$r = $conn->query("INSERT INTO `$hist_table` (`$hist_col1`, `$hist_col14`) VALUES('$chat_id','$v') ON DUPLICATE KEY UPDATE `$hist_col1`='$chat_id', `$hist_col14`='$v'");
}

function getConfirm() {
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col14,
			$chat_id;

	$r = $conn->query("SELECT `$hist_col14` FROM `$hist_table` WHERE `$hist_col1` = '$chat_id'")->fetch_object()->$hist_col14;
	return $r;
}

function setMenuId($v) {
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col10,
			$chat_id;

	$r = $conn->query("INSERT INTO `$hist_table` (`$hist_col1`, `$hist_col10`) VALUES('$chat_id','$v') ON DUPLICATE KEY UPDATE `$hist_col1`='$chat_id', `$hist_col10`='$v'");
}

function getMenuId() {
	global 	$conn,
			$hist_table,
			$hist_col1,
			$hist_col10,
			$chat_id;

	$r = $conn->query("SELECT `$hist_col10` FROM `$hist_table` WHERE `$hist_col1` = '$chat_id'")->fetch_object()->$hist_col10;
	return $r;
}

function getConf($target) {
	global 	$conn,
			$stngs_table,
			$stngs_col1,
			$stngs_col2;
	$r = $conn->query("SELECT `$stngs_col2` FROM `$stngs_table` WHERE `$stngs_col1` = '$target'")->fetch_object()->$stngs_col2;
	if ($r == "") {setConf($target, 0);}
	return $r;
}

function setConf($target, $value) {
	global 	$conn,
			$stngs_table,
			$stngs_col1,
			$stngs_col2;
	$r = $conn->query("INSERT INTO `$stngs_table` (`$stngs_col1`, `$stngs_col2`) VALUES('$target','$value') ON DUPLICATE KEY UPDATE `$stngs_col1`='$target', `$stngs_col2`='$value'");
}

// All the menus
function main_menu($s = 0, $f = 0) {
	global 	$chat_id,
			$egor,
			$menu_id,
			$e_hello;
	setMenuState("main_menu");
  $h = $e_hello[mt_rand(0, count($e_hello) - 1)];
	$t = "Main Menu";
	$d = "It all starts here 🌈";
	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "$h\n\n<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = btnArray('⌨️ KM Macros', 'macro_menu', 1);
 	$b2 = btnArray('🛠 Settings', 'settings_main', 1);
 	$b3 = btnArray('👁 Last Video', 'kmrun_Last_Video', 2);
	$b4 = btnArray('❌ Exit', 'exit_menu', 3);

	if ($f) {
		if ($menu_id != 0) {
			deleteMessage($menu_id, $chat_id);
			deleteTemp();
			setConfirm(0);
		}
		$r = sendMessage($reply, $chat_id, $b1, $b2, $b3, $b4);
		setMenuId($r);
	} else {
		$r = editMessage($reply, $b1, $b2, $b3, $b4);
		if ($s == 0) {callbackAnswer("");}
	}

	global $opt_debug;
	if($opt_debug){
		$r[0] = strip_tags($r[0]);
		$r[1] = strip_tags($r[1]);
		sendMessage("<b>JSON:</b>\n\n<code>$r[0]</code>");
		sendMessage("<b>Result:</b>\n\n<code>$r[1]</code>");
	}
}

function macro_menu($s = 0) {
	setMenuState("macro_menu");
	$t = "KM Macros";
	$d = "Let's run something 🚴‍♀️";
	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = btnArray('CV Update', 'kmrun_CV_Update', 1);
	$b2 = btnArray('Test Message', 'kmrun_Test_Message', 1);
	$b3 = btnArray('Screen Photo', 'kmrun_Screen_Photo', 2);
	$b4 = btnArray('Screen File', 'kmrun_Screen_File', 2);
	$b5 = btnArray('↩️ Back', 'menu_back', 4);

	$r = editMessage($reply, $b1, $b2, $b3, $b4, $b5);
	if ($s == 0) {callbackAnswer("");}

	global $opt_debug;
	if($opt_debug){
		$r[0] = strip_tags($r[0]);
		$r[1] = strip_tags($r[1]);
		sendMessage("<b>JSON:</b>\n\n<code>$r[0]</code>");
		sendMessage("<b>Result:</b>\n\n<code>$r[1]</code>");
	}
}

function settings_main($s = 0) {
	setMenuState("settings_main");
	$t = "Settings";
	$d = "I can manage everything 🌎";
	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = btnArray('⌨️ KM', 'settings_km', 1);
	$b2 = btnArray('🤖 Bot', 'settings_bot', 1);
	$b3 = btnArray('📛 Reboot', 'confirm_reboot', 2);
	$b4 = btnArray('↩️ Back', 'menu_back', 3);

	$r = editMessage($reply, $b1, $b2, $b3, $b4);
	if ($s == 0) {callbackAnswer("");}

	global $opt_debug;
	if($opt_debug){
		$r[0] = strip_tags($r[0]);
		$r[1] = strip_tags($r[1]);
		sendMessage("<b>JSON:</b>\n\n<code>$r[0]</code>");
		sendMessage("<b>Result:</b>\n\n<code>$r[1]</code>");
	}
}

function settings_km($s = 0) {
	global	$opt_km_cafLogging;
	setMenuState("settings_km");
	$t = "Keyboard Maestro";
	$d = "Macros and automations 💾";
	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = btnArray('➕ CV Update', 'settings_cv', 1);
	$b2 = btnArray('➕ 1C Reminder', 'settings_1c', 1);
	$b3 = $opt_km_cafLogging ? btnArray('🔳 KM Logging', 'opt_km_cafLogging', 2) : btnArray('⬜️ KM Logging', 'opt_km_cafLogging', 2);
	$b4 = btnArray('💩 Soft Reset', 'confirm_reset', 3);
	$b5 = btnArray('↩️ Back', 'menu_back', 4);

	$r = editMessage($reply, $b1, $b2, $b3, $b4, $b5);
	if ($s == 0) {callbackAnswer("");}

	global $opt_debug;
	if($opt_debug){
		$r[0] = strip_tags($r[0]);
		$r[1] = strip_tags($r[1]);
		sendMessage("<b>JSON:</b>\n\n<code>$r[0]</code>");
		sendMessage("<b>Result:</b>\n\n<code>$r[1]</code>");
	}
}

function settings_cv($s = 0) {
	global	$opt_km_cafHhAuto,
			$opt_km_cafHhNotif;
	setMenuState("settings_cv");
	$t = "CV Update";
	$d = "Fuck you, headhunter 🖕";
	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = $opt_km_cafHhAuto ? btnArray('🔳 Autoupdate', 'opt_km_cafHhAuto', 1) : btnArray('⬜️ Autoupdate', 'opt_km_cafHhAuto', 1);
	$b2 = $opt_km_cafHhNotif ? btnArray('🔳 Notifications', 'opt_km_cafHhNotif', 2) : btnArray('⬜️ Notifications', 'opt_km_cafHhNotif', 2);
	$b3 = btnArray('↩️ Back', 'menu_back', 3);

	$r = editMessage($reply, $b1, $b2, $b3);
	if ($s == 0) {callbackAnswer("");}

	if($opt_debug){
		$r[0] = strip_tags($r[0]);
		$r[1] = strip_tags($r[1]);
		sendMessage("<b>JSON:</b>\n\n<code>$r[0]</code>");
		sendMessage("<b>Result:</b>\n\n<code>$r[1]</code>");
	}
}

function settings_1c($s = 0) {
	global	$opt_km_cafHSchedule,
			$opt_km_cafHoursDebug;
	setMenuState("settings_1c");
	$t = "1C Reminder";
	$d = "Let's help Nastya to deal with 1C 🤷‍♀️";
	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = $opt_km_cafHSchedule ? btnArray('🔳 Schedule', 'opt_km_cafHSchedule', 1) : btnArray('⬜️ Schedule', 'opt_km_cafHSchedule', 1);
	$b2 = $opt_km_cafHoursDebug ? btnArray('🔳 Send a copy', 'opt_km_cafHoursDebug', 2) : btnArray('⬜️ Send a copy', 'opt_km_cafHoursDebug', 2);
	$b3 = btnArray('↩️ Back', 'menu_back', 3);

	$r = editMessage($reply, $b1, $b2, $b3);
	if ($s == 0) {callbackAnswer("");}

	if($opt_debug){
		$r[0] = strip_tags($r[0]);
		$r[1] = strip_tags($r[1]);
		sendMessage("<b>JSON:</b>\n\n<code>$r[0]</code>");
		sendMessage("<b>Result:</b>\n\n<code>$r[1]</code>");
	}
}

function settings_bot($s = 0) {
	global	$opt_debug,
			$opt_json_to_log,
			$opt_autoforward;
	setMenuState("settings_bot");
	$t = "CAF Bot";
	$d = "That's me 👋";
	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = $opt_debug ? btnArray('🔳 Debug', 'opt_debug', 1) : btnArray('⬜️ Debug', 'opt_debug', 1);
	$b2 = $opt_json_to_log ? btnArray('🔳 JSON to .log', 'opt_json_to_log', 1) : btnArray('⬜️ JSON to .log', 'opt_json_to_log', 1);
	$b3 = $opt_autoforward ? btnArray('🔳 Forward all', 'opt_autoforward', 2) : btnArray('⬜️ Forward all', 'opt_autoforward', 2);
	$b4 = btnArray('↩️ Back', 'menu_back', 3);

	$r = editMessage($reply, $b1, $b2, $b3, $b4);
	if ($s == 0) {callbackAnswer("");}

	if($opt_debug){
		$r[0] = strip_tags($r[0]);
		$r[1] = strip_tags($r[1]);
		sendMessage("<b>JSON:</b>\n\n<code>$r[0]</code>");
		sendMessage("<b>Result:</b>\n\n<code>$r[1]</code>");
	}
}

function kmVar($t, $v, $s = 0) { // $t— Target, $v — Value
	if (!$s) {
		$t = str_replace('km_', '', $t);

		if ($t == "cafLogging") {
			$rt = "Logging"; // Result text
			$rs = $v ? "On" : "Off"; // Result state
		}
		if ($t == "cafHhAuto") {
			$rt = "CV Autoupdate";
			$rs = $v ? "On" : "Off";
		}
		if ($t == "cafHhNotif") {
			$rt = "CV Notifications";
			$rs = $v ? "On" : "Off";
		}
		if ($t == "cafHSchedule") {
			$rt = "1C Reminder";
			$rs = $v ? "On" : "Off";
		}
		if ($t == "cafHoursDebug") {
			$rt = "1C Send Copy";
			$rs = $v ? "On" : "Off";
		}

		exec('
			osascript -e \'
				tell application "Keyboard Maestro Engine"
					setvariable "cafNewTask" to "Change Settings"
					setvariable "cafSet" to "'."$t\n$v\n$rt\n$rs".'"
					ignoring application responses
						do script "_cafQueue"
					end ignoring
				end tell
			\'');
	} else {
		$t = str_replace('km_', '', $t);
		exec('
			osascript -e \'
				tell application "Keyboard Maestro Engine"
					setvariable "'."$t".'" to "'."$v".'"
				end tell
			\'');
	}
}

function runMacro($n) { // $n — Macro Name
	$rn = $n; // Raw Name
	$n = str_replace('kmrun_','',$n);
	if ($n == strchr($n, '_')) {
		$n = str_replace('_',' ',$n);
		$n = substr_replace($n, '_', 0, 1);
	} else {
		$n = str_replace('_',' ',$n);
	}

	if ($n != "_cafInit") {
		exec('
			osascript -e \'
			tell application "Keyboard Maestro Engine"
			setvariable "cafNewTask" to "'.$n.'"
				ignoring application responses
					do script "_cafQueue"
				end ignoring
			end tell
			\'');
	} else {
		exec('
			osascript -e \'
			tell application "Keyboard Maestro Engine"
				ignoring application responses
					do script "'.$n.'"
				end ignoring
			end tell
			\'');
	}

	callbackAnswer("$n was 🚀");
}

function toggleConf($t) { // $t — Target
	$t = str_replace('opt_','',$t);
	$tdb = getConf($t);
	if ($tdb == 1) {setConf($t, 0);$tdb = 0;} else {setConf($t, 1);$tdb = 1;}
	if (strchr($t, "km_") != false) {kmVar($t, $tdb);}

	getSettings();

	$menu = getMenuState();
	$menu(1);
	callbackAnswer("Saved 👌");
}

function exit_menu() {
	global	$msg_id,
			$chat_id;
	setMenuState("");
	setMenuId(0);
	setConfirm(0);
	deleteMessage($msg_id, $chat_id);
	deleteTemp();
	callbackAnswer("See you 👋");
}

function menu_back($s = 0) { // $s — Silent
	$menu = getMenuState();
	$confirm = getConfirm();
	if (!$confirm) {
		if ($menu == "settings_main" || $menu == "macro_menu") {main_menu($s);}
		if ($menu == "settings_bot" || $menu == "settings_km") {settings_main($s);}
		if ($menu == "settings_cv" || $menu == "settings_1c") {settings_km($s);}
		if ($menu == "") {main_menu($s);}
	} else {
		$menu($s);
		setConfirm(0);
	}

}

function confirm_dialog($itm, $s = 0) { // $itm — Item to confirm, $s — Silent
	$itm = str_replace("confirm_", "", $itm);

	if ($itm == "reboot") {
		$t = "Reboot CAF";
		$d = "Do you want to continue?";
		$yes = "kmrun__cafReboot";
	}

	if ($itm == "reset") {
		$t = "Reinitialize CAF";
		$d = "Do you want to continue?";
		$yes = "kmrun__cafInit";
	}

	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = btnArray('↩️ No', 'menu_back', 1);
	$b2 = btnArray('⚠️ Yes', $yes, 1);

	$r = editMessage($reply, $b1, $b2);
	if ($s == 0) {callbackAnswer("");}

	global $opt_debug;
	if($opt_debug){
		$r[0] = strip_tags($r[0]);
		$r[1] = strip_tags($r[1]);
		sendMessage("<b>JSON:</b>\n\n<code>$r[0]</code>");
		sendMessage("<b>Result:</b>\n\n<code>$r[1]</code>");
	}
}

// Nastya Menu
function nastya_menu($s = 0, $f = 0) {
	global	$opt_km_cafHSchedule,
			$chat_id,
			$menu_id,
			$n_hello;
	setMenuState("nastya_menu");
	$t = "1C Reminder";
	$d = "I'll help you to deal with 1C 🤷‍♀️";
	$t = "→ ".strtoupper($t);
	if (!empty($d)) {
		$reply = "<b>$t</b>\n$d";
	} else {$reply = "<b>$t</b>";}
	$b1 = $opt_km_cafHSchedule ? btnArray('🔳 Schedule', 'opt_km_cafHSchedule', 1) : btnArray('⬜️ Schedule', 'opt_km_cafHSchedule', 1);
	$b2 = btnArray('❌ Exit', 'exit_menu', 2);

	if ($f) {
		if ($menu_id != 0) {
			deleteMessage($menu_id, $chat_id);
			deleteTemp();
		}
		markTemp(sendMessage($n_hello[mt_rand(0, count($n_hello) - 1)], $chat_id));
		$r = sendMessage($reply, $chat_id, $b1, $b2);
		setMenuId($r);
	} else {
		$r = editMessage($reply, $b1, $b2);
		if ($s == 0) {callbackAnswer("");}
	}
}

// Load config
$ini = parse_ini_file('../../../caf.ini.php');
extract($ini);

// API settings
define('url', "https://api.telegram.org/bot".$caf_token."/");
define('log', "https://api.telegram.org/bot".$log_token."/");
define('egor', $egor);

// DB settings
$conn = mysqli_connect("127.0.0.1", $sql_user, $sql_pass, $sql_db);

// Get stored settings
function getSettings() {
	$GLOBALS['opt_debug'] = getConf('debug');
	$GLOBALS['opt_json_to_log'] = getConf('json_to_log');
	$GLOBALS['opt_autoforward'] = getConf('autoforward');
	$GLOBALS['opt_km_cafLogging'] = getConf('km_cafLogging');
	$GLOBALS['opt_km_cafHhAuto'] = getConf('km_cafHhAuto');
	$GLOBALS['opt_km_cafHhNotif'] = getConf('km_cafHhNotif');
	$GLOBALS['opt_km_cafHSchedule'] = getConf('km_cafHSchedule');
	$GLOBALS['opt_km_cafHoursDebug'] = getConf('km_cafHoursDebug');
}
getSettings();

// Init variables
$msg1 = ['N/A','0'];
$msg2 = ['N/A','0'];
$msg3 = ['N/A','0'];
$reply = "";
$b1 = []; $b2 = []; $b3 = [];
$b4 = []; $b5 = []; $b6 = [];
$mb7 = []; $b8 = []; $b9 = [];

// Hello
$e_hello = ["Welcome back, commander ⚡️",
      "Yeah, boooooi 😎",
      "So, work your magic 💫",
      "Let's automate the boring stuff 👾",
      "Adventure, danger and low cunning ⚔️",
      "What's up, S L A P P E R S? 🎸",
      "I solemnly swear that I am up to no good 😏"];
$n_hello = ["Hi, Pshenchik 🦊",
      "Look who's curly fun 💃",
      "Yeah, boooooi 😎",
      "So, work your magic 💫",
      "Let's automate the boring stuff 👾",
      "Adventure, danger and low cunning ⚔️",
      "I solemnly swear that I am up to no good 😏"];

while(true) {
  $raw = "";

  // Start listening for connections
  $result = socket_listen($socket, 3) or die("Could not set up socket listener\n");

  // Accept incoming connections
  // Spawn another socket to handle communication
  $spawn = socket_accept($socket) or die("Could not accept incoming connection\n");

  // Read client input
  $input = socket_read($spawn, 8192) or die("Could not read input\n");
  $input = trim($input);

  // Echo and response
  $output = $h_ok.$h_date.$h_content."\n"."It's alright, darling\n";
  socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");
  socket_close($spawn);

  list($headers, $raw) = array_pad(explode("\r\n\r\n", $input, 2), 2, "");
  // echo("\n[ HEADERS ]\n\n".$headers."\n\n");
  // echo("[ BODY ]\n\n".$raw."\n");

  // Check input
  if (empty($raw)) {continue;};
  $update = json_decode($raw, true);

  // Sync me, baby
  if ($input == $kmSyncKey) {
  	foreach ([	"km_cafLogging",
  				"km_cafHhAuto",
  				"km_cafHhNotif",
  				"km_cafHSchedule",
  				"km_cafHoursDebug"] as $k) {
  		kmVar($k, getConf($k), 1);
  	}
  	continue;
  }

  if (empty($update['update_id'])) {continue;};
  // Parse input
  if (empty($update['callback_query'])) {
  	$user_id = $update['message']['from']['id'];
  	$first_name = safeSymbols($update['message']['from']['first_name']);
  	$last_name = safeSymbols($update['message']['from']['last_name']);
  	$username = $update['message']['from']['username'];
  	$text = safeSymbols($update['message']['text']);
  	$msg_id = $update['message']['message_id'];
  	$chat_id = $update['message']['chat']['id'];
  	$msg0 = [$text, $msg_id];
  	if (!empty($chat_id)) {updHist();} else {continue;}
    $update['callback_query'] = false;
  } else {
  	$user_id = $update['callback_query']['from']['id'];
  	$first_name = safeSymbols($update['callback_query']['from']['first_name']);
  	$last_name = safeSymbols($update['callback_query']['from']['last_name']);
  	$username = $update['callback_query']['from']['username'];
  	$chat_id = $update['callback_query']['message']['chat']['id'];
  	$msg_id = $update['callback_query']['message']['message_id'];
  	$upd_id = $update['callback_query']['id'];
  	$callback = safeSymbols($update['callback_query']['data']);
  }

  // Variables from DB
  $menu_id = getMenuId();
  $del1 = getTemp(1);
  $del2 = getTemp(2);
  $del3 = getTemp(3);

  // Telegram logic
  // JSON to .log
  if ($opt_json_to_log == 1 && $user_id == $egor) {
  	sendLog("<b>JSON:</b>\n\n<code>$raw</code>");
  }

  if ($user_id == $egor && !$update['callback_query']) {
  	if ($text == "/auth") {
  		continue;
  	}
  	if ($text == "/hey" || $text == "/start") {
  		main_menu(0, 1);
  	}
  } elseif ($user_id != $egor && !$update['callback_query']) {
  	// Forward everything
  	if ($opt_autoforward) {forwardMessage($msg_id, $chat_id);}

  	// Login
  	if ($text == "/auth" || $text == "/start") {
  		$reply = "Password?";
  	} elseif ($text == "/auth ".$n_pass || $msg1[0] == "/auth" && $text == $n_pass || $msg1[0] == "/start" && $text == $n_pass) {
  		nastya_menu(0, 1);
  	} elseif ($text == "/auth ".$e_pass || $msg1[0] == "/auth" && $text == $e_pass || $msg1[0] == "/start" && $text == $e_pass) {
  		main_menu(0, 1);
  	} elseif (strpos($text, "/auth") !== false && $text != "/auth ".$n_pass || $msg1[0] == "/start" && $text != $n_pass || $msg1[0] == "/auth" && $text != $n_pass || strpos($text, "/auth") !== false && $text != "/auth ".$e_pass || $msg1[0] == "/auth" && $text != $e_pass) {
  		$reply = "Wrong. You're not welcome here 🔪";
  	}

  	if (!empty($reply)) {sendMessage($reply, $chat_id, $b1);}
  } elseif ($update['callback_query']) {
  	// Forward everything
  	if ($opt_autoforward && $user_id != $egor) {
  		$fn = $first_name;
  		if ($last_name != "") {$ln = " ".$last_name;} else {$ln = "";}
  		sendMessage("<a href=\"tg://user?id=$user_id\">$fn$ln</a>:\n$callback");
  	}

  	// Callback logic
  	if (getConfirm() == 1) {
  		if ($callback == "kmrun__cafReboot") {
  			exit_menu();
  		} else {
  			if ($callback == "menu_back") {
  				menu_back();
  				continue;
  			} else {
  				menu_back(1);
  			}
  		}
  	}

  	if ($menu_id == $msg_id) {
  		if (strchr($callback, "opt_") != false) {
  			toggleConf($callback);
  		} elseif (strchr($callback, "kmrun_") != false) {
  			runMacro($callback);
  		} elseif (strchr($callback, "confirm_") != false) {
  			setConfirm(1);
  			confirm_dialog($callback);
  		} else {
  			$callback();
  		}

  	} else {
  		deleteMessage($msg_id, $chat_id);
  		callbackAnswer("Leave it in the past 😏");
  	}
  }
}
// Close sockets
socket_close($socket);
?>
