<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: http://62.80.182.218/squad/main.php'); exit; }
if (!isset($_SESSION['perms']) || $_SESSION['perms'][1]!='1') { header('Location: http://62.80.182.218/squad/main.php'); exit; }
?>

<?php 

function clearNick($nickList) {
	$mr='';
	$nkl='';
	$lines=explode("\n",$nickList);
	foreach($lines as $ln) {
		$nk=trim($ln);
		if ($nk!='') {
			$ss=substr($nk,0,3);
			if ($ss=='(M)'||$ss=='(C)'||$ss=='(O)') { 
				if ($ss=='(M)'||$ss=='(М)') $mr='M';
				if ($ss=='(C)'||$ss=='(С)') $mr='C';
				if ($ss=='(O)'||$ss=='(О)') $mr='O';
				$nk=substr($nk,3);
			}
			$nk=preg_replace("/[^a-zA-Z0-9а-яіїА-ЯІЇєЄ\s]+/u", '', $nk);
			$nk=trim($nk);
			$nnk=strtolower($nk);
			if (strpos($nnk,'450') ===0) $nk=substr($nk,3);
			if (strpos($nnk,'sof') ===0) $nk=substr($nk,3);
			if (strpos($nnk,'ucl') ===0) $nk=substr($nk,3);
			if (strpos($nnk,'uac') ===0) $nk=substr($nk,3);
			if (strpos($nnk,'rats')===0) $nk=substr($nk,4);
			if (strpos($nnk,'bros')===0) $nk=substr($nk,4);
			if (strpos($nnk,'ib')  ===0) $nk=substr($nk,2);
			if (strpos($nnk,'u24') ===0) $nk=substr($nk,3);
		} //else $nk=substr( $ln, $s+1, $e-($s+1));
		$nk=trim($nk);
		$nkl.=$nk."\n";
	}
	
	return array( substr($nkl,0,-1), $mr );
}

if (isset($_POST['exec'])) {
	if ($_POST['exec']=='clear') {
		$r=clearNick($_POST['list']);
		echo $r[0]; exit;
	} else if ($_POST['exec']=='import') {
		$lines=explode("\r\n",$_POST['list']);
		$nicks=array();
		$prevLn='';
		$mysqli = new mysqli("localhost", "uac", "20KeepUkraine23", "uac");
		$nk=$mr='';
		for ($ii=0;$ii<count($lines);$ii++) {
			if ($lines[$ii]=='') continue;
			$r=clearNick($lines[$ii]);
			$nk=$r[0]; $mr=$r[1];
			$ii=$ii+1;
			$did=$lines[$ii]; //echo $nk.' - '.$mr.' - '.$did."\n";
			$nicks[] = array( trim(mysqli_real_escape_string($mysqli,$did)) , trim(mysqli_real_escape_string($mysqli,$nk)) , $mr );
		}
		/*foreach($lines as $ln) {
			$nick='';
			$s = -1; $s = strpos($ln,'@');
			if ($s > -1) {
				$r=clearNick($prevLn);
				$nk=$r[0]; $mr=$r[1];
				$did=substr($ln,1);
				$e = -1; $e = strpos($ln,'#',$s); if ($e > 0) $did=substr( $ln, $e+1);
				$nicks[] = array( trim(mysqli_real_escape_string($mysqli,$did)) , trim(mysqli_real_escape_string($mysqli,$nk) , $mr) );
			}
			$prevLn=$ln;
		}*/
		$ret='';
		foreach($nicks as $n) {
			//echo $n[0].' - '.$n[1].' - '.$n[2]."\n";
			//main insert
			//echo "INSERT INTO `players` SET `id`='".$n[0]."', `nick`='".$n[1]."', `role`='".$_POST['mainRole']."';\n";
			$pas=$prm=''; if ($n[2]=='O') { $prm='100'; $pas='1qaz2wsx'; }
			echo "INSERT INTO `players` SET `pass`='$pas', `perms`='$prm', `role`='".$n[2]."', `id`='".$n[0]."', `nick`='".$n[1]."';\n";
			//update nick
			//$ret.="UPDATE `players` SET `nick`='".$n[1]."' WHERE `id`='".$n[0]."';\n";
		}
		echo $ret;
		$mysqli->close();
	}
} else { ?>
<!DOCTYPE html>
<html lang="uk">
<head><title>4.5.0. server</title><meta charset="utf-8"></head>
<body>
main role <input id="mainRole">
list <textarea id="list"></textarea>
<input type="button" onclick="importMembers()" value="import" />
<hr>
<textarea id="ret"></textarea>
<script>
function importMembers(exec) {
	var formData = new FormData();
	formData.append("exec",'import');	
    formData.append("mainRole",document.getElementById('mainRole').value);
	formData.append("list",document.getElementById('list').value);
	var xhr = new XMLHttpRequest();  
	xhr.open("POST", "http://62.80.182.218/squad/import.php");
	xhr.send(formData);
	
	xhr.onreadystatechange = () => {
		if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
			//document.getElementById('ret').innerHTML = xhr.responseText;
			document.getElementById('ret').value = xhr.responseText;
		}
	}
	
}
</script>
</body>
</html>

<?php } ?>
