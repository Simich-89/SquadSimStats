<?php 
if (isset($_POST['id']) && isset($_POST['pass'])) {
	$ret='Hello';
	$mysqli = new mysqli("localhost", "uac", "20KeepUkraine23", "uac");
	$result = $mysqli->query("SELECT * FROM players WHERE id='".$_POST['id']."'");
	if ($result->num_rows != 1) { $ret='no such id'; 
	} else {
		$row = $result->fetch_assoc();
		if (($row['pass'] == '') || ($row['pass'] != $_POST['pass'])) { $ret='wrong password'; 
		} else {
			session_start(); 
			$_SESSION['id']=$row['id'];
			$_SESSION['perms']=$row['perms'];
			$_SESSION['nick']=$row['nick'];
			file_put_contents('acc.log',date("m.d H:i:s").' -- '.$_SESSION['id'].' logged in'."\n",FILE_APPEND);
			$ret='Ok';
		}
	}
	$mysqli->close();
	echo $ret; header("Refresh:1"); exit;
}
?>

<?php 
session_start();
if (!isset($_SESSION['id'])) { ?>
<!DOCTYPE html>
<html lang="uk">
<head><title>4.5.0. server</title><meta charset="utf-8"></head>
<body><form style="display: inline-block;margin:5%; padding:15px; background: cadetblue; color: #fff; font-size: 20px; border-radius: 20px; box-shadow: 1px 1px 5px rgba(0,0,0,0.8); " name="logn" method="POST" ACTION="main.php" id="logn"><table><tr><td>Discord ID</td><td><input type="text" name="id" maxlength="50" placeholder="цифри вашого логіну діскорду" /></td></tr><tr><td>Пароль</td><td><input type="password" name="pass" maxlength="50" /></td></tr><tr><td colspan="2"><input style="width:100%;" type="submit" value="login" /></td></tr></table></form> </body> 
</html>
<?php exit; } ?>

<?php 
if (isset ($_POST['exec'])) {
	
} else { ?>
<!DOCTYPE html>
<html lang="uk">
<head>
<title>4.5.0. server</title>
<meta charset="utf-8">
<style>
a { display: inline-block; padding: 15px; margin:5px; background: cadetblue; color: #fff; font-size: 20px; border-radius: 20px; box-shadow: 1px 1px 5px rgba(0,0,0,0.8); }
a:hover { background: #29d5db; }
</style>
</head>
<body>
<a href="teams.php" />Склад на гру</a>
<a href="server.php" />Керування сервером</a>
<a href="import.php" />Import гравців</a>
</script>
</body>
</html>

<?php } ?>