<?php 
session_start();
if (!isset($_SESSION['id'])) { header('Location: http://62.80.182.218/squad/main.php'); exit; }
if (!isset($_SESSION['perms']) || $_SESSION['perms'][2]!='1') { header('Location: http://62.80.182.218/squad/main.php'); exit; }
?>

<?php 
if (isset ($_POST['exec'])) {
	file_put_contents('acc.log',date("m.d H:i:s").' -- '.$_SESSION['id'].' - '.$_POST['exec']."\n",FILE_APPEND);
	if ($_POST['exec'] == 1) {
		$o = shell_exec("pgrep SquadGameServer");
		if ($o != "") echo "Squad server is already running\n$o";
		file_put_contents('/var/www/html/squad/exec','1'); file_put_contents('/var/www/html/squad/status',date("m.d H:i:s").' WAIT! Pending start server.'."\n"); exit;
	} else if ($_POST['exec'] == 2) {
		file_put_contents('/var/www/html/squad/exec','2'); file_put_contents('/var/www/html/squad/status',date("m.d H:i:s").' WAIT! Pending stop server.'."\n"); exit;
	} else if ($_POST['exec'] == 3) {
		file_put_contents('/var/www/html/squad/exec','3'); file_put_contents('/var/www/html/squad/status',date("m.d H:i:s").' WAIT! Pending restart server.'."\n"); exit;
	} else if ($_POST['exec'] == 0) {
		$o = shell_exec("pgrep SquadGameServer");
		$o.="\n---------------------------------------------------------\n".file_get_contents('/var/www/html/squad/status');
		echo date("m.d H:i:s")."\n$o";
		exit;
	} else if ($_POST['exec'] == 4) {
		echo date("m.d H:i:s")."\n".file_get_contents('/var/www/html/squad/acc.log');
	} else if ($_POST['exec'] == 99) {
		echo date("m.d H:i:s")."\n".file_get_contents('/var/www/html/squad/acc.log');
	}
} else { ?>
<!DOCTYPE html>
<html lang="uk">
<head>
<title>4.5.0. server</title>
<meta charset="utf-8">
</head>
<body>
	<input type="button" value="Get Status" onclick="postRequest(0);"/> 
	<input type="button" value="Stop Server" onclick="if (confirm('Are you sure you want to stop server?')) { postRequest(2); postRequest(0); }"/> 
	<input type="button" value="Start Server" onclick="if (confirm('Are you sure you want to start server?')) { postRequest(1); postRequest(0); }"/>
	<input type="button" value="Restart Server" onclick="if (confirm('Are you sure you want to Restart server?')) { postRequest(3); postRequest(0); }"/>
	<input type="button" value="Get log" onclick="postRequest(99);"/> 
	<input type="button" value="wipe stats" onclick="if (confirm('Are you sure you want to wipe stats?')) { postRequest(4); }"/> 
	<div><textarea id="blkStatus" style="padding:10px; border:1px solid lightgrey; width:90%;height:600px;"></textarea></div>
<script>
function postRequest(exec) {
	var formData = new FormData();  
    formData.append("exec",exec); 
	var xhr = new XMLHttpRequest();  
	xhr.open("POST", "http://62.80.182.218/squad/server.php", true);  
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send("exec="+exec);
	
	xhr.onreadystatechange = () => { // Call a function when the state changes.
		if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
			document.getElementById('blkStatus').value = xhr.responseText;
		}
	}
	
}

</script>
</body>
</html>

<?php } ?>