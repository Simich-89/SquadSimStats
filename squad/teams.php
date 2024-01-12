<?php 
session_start();
if (!isset($_SESSION['id'])) { header('Location: http://62.80.182.218/squad/main.php'); exit; }
if (!isset($_SESSION['perms']) || ($_SESSION['perms'][0]!='1' && $_SESSION['perms'][0]!='2')) { header('Location: http://62.80.182.218/squad/main.php'); exit; }
?>

<?php if (isset($_POST['exec'])) {
	$ret='';
	if ($_POST['exec'] == 0) {
		$mysqli = new mysqli("localhost", "uac", "20KeepUkraine23", "uac");
		$result = $mysqli->query("SELECT * FROM players");
		$ret='';
		while( $row = $result->fetch_assoc() ) {
			$ret .= '<tr><td>'.$row['id'].'<td>'.$row['nick'].'</td><td>'.$row['role'].'</td><td>'.$row['notes'].'</td></tr>';
		}
		echo '<table>'.$ret.'</table>';	
		$mysqli->close();
		exit;
	} else if ($_POST['exec']==1) {
		if (file_put_contents('events/event',$_POST['data']) === FALSE) { echo 'Can\'t create file'; exit; }		
		echo 'Ok'; exit;
	} else if ($_POST['exec']==2) {
		$ret=file_get_contents('events/event');
		if ($ret === FALSE) { echo 'Can\'t read file'; exit; }		
		echo $ret; exit;
	} else if ($_POST['exec']==3) {
		$lines=explode("\n",$_POST['list']);
		foreach($lines as $ln) {
			$nk=trim($ln); 
			if ($nk!='') {
				$nk=preg_replace("/[^a-zA-Z0-9а-яіїА-ЯІЇ\s]+/u", '', $nk);
				$ss=substr($nk,0,2);
				if ($ss=='M '||$ss=='C '||$ss=='O ') $nk=substr($nk,2);
			}
		}
		echo $ret; exit;
	}
	
} else { ?>

<!DOCTYPE html>
<html lang="uk">
<head><title>4.5.0. server</title><meta charset="utf-8">
<script>
<?php 
$mysqli = new mysqli("localhost", "uac", "20KeepUkraine23", "uac");
$result = $mysqli->query("SELECT * FROM players");
$arr=array(); while( $row = $result->fetch_assoc() ) { $arr[]=$row; }
echo "var allList = JSON.parse('".json_encode($arr)."');";
$mysqli->close();
?>
var isEditable=false;
var forceLoad=false;
let timerId=false;

function filterList() { 
	var nm=document.getElementById('inp_filter').value.toLowerCase();
	var rows=document.getElementById('allList').getElementsByTagName('tr');
	if (nm.length<3) { for (var jj=0;jj<rows.length;jj++) { rows[jj].style.display='table-row'; } return; 
	} else {
		for (var jj=0;jj<rows.length;jj++) { 
			var nick = rows[jj].getElementsByTagName('td')[0].innerText.toLowerCase();
			if (nick.indexOf(nm) > -1) {
				rows[jj].style.display='table-row';
			} else  rows[jj].style.display='none';
		}
	}
	
}

function switchRet() {
	if (document.getElementById('ret').style.display=='none') document.getElementById('ret').style.display='block'; else document.getElementById('ret').style.display='none';
}

</script>
<style>
#allList { display:inline-block; width:220px; position: fixed; background: rgba(255,255,255,0.6); z-index:99; margin-left: -215px; transition: all 0.3s ease-out; cursor:pointer; border-right:1px dashed red; box-sizing: border-box; height: 100%; overflow-y: scroll; }
#allList table { width:200px; }
#allList:hover { margin-left: 0px; }
#allList tr:hover { background:#74f974; }
#plusList { margin-left:10px; position:relative; min-height:150px; border:1px dashed green; }
.blkPlus { position:relative; display:inline-block; margin:5px; padding:5px; border-radius:10px; background: green; cursor:pointer; color: white; }
#squadList { position:relative; } 
#squadList > div { width: 180px; position:relative; display:inline-block; margin:5px; vertical-align:top; color: white; }
#squadList > div > textarea { width:100%; height: 60px; box-sizing: border-box; }
#squadList > div > div { padding-top: 5px; border: 1px dashed #fff; }
#squadList .blkPlus { display:block; border: 1px solid #fff; margin:3px; padding:3px; border-radius:10px; }
.blkPlus img { position:relative; display:inline-block; width:25px; box-sizing: border-box; cursor:pointer; margin-bottom: -7px;  }
#tbl_tacMap td { width:50%; }
#tbl_tacMap img { width:100%; }
#blkRoles { position:absolute; display:none; background:#ffc40b; border:1px solid #fff; border-radius:10px; padding:3px; z-index:99; }
#blkRoles img { width:25px;  box-sizing: border-box; cursor:pointer; }
#blkRoles img:hover { border:1px solid #fff; }
#ret { display:none; }
</style>
</head>
<body>
<div id="allList"></div>
<div id="ret"></div>


<div id="blkRoles">
	<img src="icons/sl.webp" /><img src="icons/md.webp" /><img src="icons/lt.webp" /><img src="icons/gl.webp" /><br>	
	<img src="icons/ht.webp" /><img src="icons/rf.webp" /><img src="icons/mg.webp" /><img src="icons/ar.webp" /><br>
	<img src="icons/en.webp" /><img src="icons/mr.webp" /><img src="icons/cr.webp" /><img src="icons/pl.webp" />
</div>

<textarea id="txt_buf"></textarea>
<input type="button" id="btn_save" value="save" onclick="teamExec(1)" />
<input type="button" value="load" onclick="teamExec(2)" />
<input type="button" value="export" onclick="exportList()" />
<input type="button" value="import" onclick="importList()" />
<input type="button" value="show ret" onclick="switchRet()"/>
<span id="spn_plus"></span>

<div id="plusList" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
&nbsp;&nbsp;&nbsp;&nbsp;Color 
<select id="sel_sqColor">
	<option value="red"		style="background:red;" >red</option>
	<option value="green"	style="background:green;" >green</option>
	<option value="blue"	style="background:blue;" >blue</option>
	<option value="cyan"	style="background:cyan;" >cyan</option>
	<option value="orange"	style="background:orange;" >orange</option>
	<option value="black"	style="background:black;" >black</option>
	<option value="grey"	style="background:grey;" >grey</option>
	<option value="brown"	style="background:brown;" >brown</option>
	<option value="violet"	style="background:violet;" >violet</option>
	<option value="pink"	style="background:pink;" >pink</option>
	<option value="gold"	style="background:gold;" >gold</option>
	<option value="coral"	style="background:coral;" >coral</option>
	<option value="tan"		style="background:tan;" >tan</option>
</select> 
<input id="btn_addSquad" type="button" value="add squad" onclick="addSquad()" />
<div id="squadList"></div>
<input id="img_src_tac1" onchange="document.getElementById('img_tac1').src=this.value"><input id="img_src_tac2" onchange="document.getElementById('img_tac2').src=this.value"><br>
<table id="tbl_tacMap"><tr><td><img id="img_tac1" src=""></td><td><img id="img_tac2" src=""></td></tr></table>

<script>
var sqList=new Array();
var blk_plusList=document.getElementById('plusList');
var blk_allList=document.getElementById('allList');
var tbl_allList='';

for (var ii=0;ii<allList.length;ii++) { tbl_allList += '<tr id="alist_'+allList[ii].id+'" ondblclick="add2Plus(\''+allList[ii].id+'\',this);"><td>'+allList[ii].nick+'</td><td>'+allList[ii].role+'</td></tr>'; }
document.getElementById('allList').innerHTML = '<input oninput="filterList()" id="inp_filter" /><table>'+tbl_allList+'</table>';

function add2Plus(id,el) {
	if (!forceLoad && !isEditable) return;
	el.style.background='green';
	if (!document.getElementById('plus_'+id)) {
		var bgrl = '';
		var nick = id; for (var jj=0;jj<allList.length;jj++) { if (allList[jj].id==id) { nick=allList[jj].nick; bgrl=allList[jj].role; } if (nick.length > 17) nick=nick.substr(0,15)+'...'; }
		if (bgrl=='c') bgrl='style="background: #df7a00;"';
		else if (bgrl=='m') bgrl='style="background: seagreen;"';
		else if (bgrl=='o') bgrl='style="background: #ffeb00; color:#000;" ';		
		blk_plusList.innerHTML += '<div id="plus_'+id+'" class="blkPlus" '+bgrl+' ondblclick="this.remove(); document.getElementById(\'alist_'+id+'\').style.background=\'#fff\';" draggable="true" ondragstart="drag(event)" ondrop="return false" ondragover="return false" ><img src="icons/rf.webp"/>'+nick+'</div>';
	}
	addShowRoles();
	document.getElementById('inp_filter').value='';
	document.getElementById('inp_filter').dispatchEvent(new Event('input'));
	updCount();
}

function addSquad() {
	if (!forceLoad && !isEditable) return;
	var sel_sqColor = document.getElementById("sel_sqColor");
	if (sel_sqColor.value=='') return;
	document.getElementById('squadList').innerHTML += 
'<div style="background:'+sel_sqColor.value+';"><div  id="squad_'+sel_sqColor.value+'" class="blkSquad" ondrop="drop(event,this)" ondragover="allowDrop(event)" >&nbsp;&nbsp;&nbsp;&nbsp;'+sel_sqColor.value+'</div><textarea ondrop="return false" ondragover="return false"></textarea></div>';
	var sel_sqColor = document.getElementById("sel_sqColor");
	for (var i=0; i<sel_sqColor.length; i++) { if (sel_sqColor.options[i].value == sel_sqColor.value) sel_sqColor.remove(i); }
	updCount();
}

function addShowRoles() {
	if (!forceLoad && !isEditable) return;
	var blkPluses = document.getElementsByClassName('blkPlus');
	for (var ii=0;ii<blkPluses.length;ii++) { 
		var img_role=blkPluses[ii].getElementsByTagName('img')[0];
		img_role.addEventListener('click', function (event) {
			if (!forceLoad && !isEditable) return;
			img_this=this;
			var x = event.clientX;
			var y = event.clientY;
			var blkRoles = document.querySelector("#blkRoles");
			blkRoles.style.display = "block"; blkRoles.style.left = `${x}px`; blkRoles.style.top = `${y}px`;
			var blkRoles_img = document.querySelector("#blkRoles").getElementsByTagName('img');
			document.querySelector("#blkRoles").addEventListener('click', function (event) { blkRoles.style.display = "none";});
			for (var jj=0;jj<blkRoles_img.length;jj++) { 
				blkRoles_img[jj].addEventListener('click', function (event) {
					img_this.src=this.src;
				});
			}
		});
	}
}

function teamExec(exec) {
	var formData = new FormData();
	formData.append("exec",exec);
	if (exec==1) {
		var editor=document.getElementById('btn_save').getAttribute('name');
		if (editor) { if (editor!='<?php echo $_SESSION['id']; ?>') return; }
		
		if (document.getElementById('btn_save').value!='save') { teamExec(2); }
		if (document.getElementById('btn_save').value=='edit') {
				console.log('edit');
			var xhr = new XMLHttpRequest();  
			xhr.open("GET", "http://62.80.182.218/squad/events/event");
			xhr.send();
			xhr.onreadystatechange = () => {
				if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
					console.log(xhr.responseText);
					try { var rslt = JSON.parse(xhr.responseText);  console.log(rslt.editor); if (rslt.editor) { return; } } catch (e) { }
				}
			}
		}
		
		var svPlus = new Array(); var blkPluses = document.getElementsByClassName('blkPlus'); //document.getElementById('plusList').getElementsByClassName('blkPlus');
		for (var ii=0;ii<blkPluses.length;ii++) { svPlus.push(blkPluses[ii].id.substr(5)); }

		var svSquad = {};
		var sq_red = document.getElementById('squad_red'); if (sq_red) 			{ var svInsq = new Array(sq_red.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_red.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.red=svInsq;  }
		var sq_green = document.getElementById('squad_green'); if (sq_green) 	{ var svInsq = new Array(sq_green.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_green.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.green=svInsq; }
		var sq_blue = document.getElementById('squad_blue'); if (sq_blue) 		{ var svInsq = new Array(sq_blue.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_blue.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.blue=svInsq; }
		var sq_cyan = document.getElementById('squad_cyan'); if (sq_cyan) 		{ var svInsq = new Array(sq_cyan.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_cyan.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.cyan=svInsq; }
		var sq_orange = document.getElementById('squad_orange'); if (sq_orange) { var svInsq = new Array(sq_orange.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_orange.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.orange=svInsq; }
		var sq_black = document.getElementById('squad_black'); if (sq_black) 	{ var svInsq = new Array(sq_black.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_black.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.black=svInsq; }
		var sq_grey = document.getElementById('squad_grey'); if (sq_grey) 		{ var svInsq = new Array(sq_grey.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_grey.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.grey=svInsq; }
		var sq_brown = document.getElementById('squad_brown'); if (sq_brown) 	{ var svInsq = new Array(sq_brown.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_brown.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.brown=svInsq; }
		var sq_violet = document.getElementById('squad_violet'); if (sq_violet) { var svInsq = new Array(sq_violet.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_violet.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.violet=svInsq; }
		var sq_pink = document.getElementById('squad_pink'); if (sq_pink) 		{ var svInsq = new Array(sq_pink.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_pink.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.pink=svInsq; }
		var sq_gold = document.getElementById('squad_gold'); if (sq_gold) 		{ var svInsq = new Array(sq_gold.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_gold.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.gold=svInsq; }
		var sq_coral = document.getElementById('squad_coral'); if (sq_coral) 	{ var svInsq = new Array(sq_coral.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_coral.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.coral=svInsq; }
		var sq_tan = document.getElementById('squad_tan'); if (sq_tan) 			{ var svInsq = new Array(sq_tan.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_tan.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { svInsq.push(blkInsq[jj].id.substr(5)+','+blkInsq[jj].getElementsByTagName('img')[0].src); } svSquad.tan=svInsq; }
				
		var data = {};
		data.plus=svPlus;
		data.squads=svSquad;
		data.img_src_tac1=document.getElementById('img_src_tac1').value;
		data.img_src_tac2=document.getElementById('img_src_tac2').value;
		
		if (document.getElementById('btn_save').value!='save') data.editor='<?php echo $_SESSION['id']; ?>';
		
		formData.append("data",JSON.stringify(data));
	}
	
	var blkPlus=document.getElementsByClassName('blkPlus');
	var xhr = new XMLHttpRequest();  
	xhr.open("POST", "http://62.80.182.218/squad/teams.php");
	xhr.send(formData);
	
	
	xhr.onreadystatechange = () => {
		if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
			if (exec==0) {
			} else if (exec==1) { document.getElementById('ret').innerHTML = xhr.responseText; teamExec(2);
			} else if (exec==2) {
				forceLoad=true;
				var rslt='';
				if (xhr.responseText=='') return;
				try {rslt = JSON.parse(xhr.responseText);} catch (e) {
					document.getElementById('btn_save').value='edit';
					for (var ii=0;ii<blkPlus.length;ii++) { blkPlus[ii].setAttribute('draggable', false); }
					isEditable=false;
					return false;
				}
				for (var ii=0;ii<rslt.plus.length;ii++) { document.getElementById('alist_'+rslt.plus[ii]).dispatchEvent(new MouseEvent("dblclick")); }
				if (rslt.squads.red) { document.getElementById('sel_sqColor').value='red'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_red').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.red[0]; for (var ii=1;ii<rslt.squads.red.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.red[ii].substr(0,rslt.squads.red[ii].indexOf(','))); document.getElementById('squad_red').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.red[ii].substr(rslt.squads.red[ii].indexOf(',')+1); } }				
				if (rslt.squads.green) { document.getElementById('sel_sqColor').value='green'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_green').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.green[0]; for (var ii=1;ii<rslt.squads.green.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.green[ii].substr(0,rslt.squads.green[ii].indexOf(','))); document.getElementById('squad_green').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.green[ii].substr(rslt.squads.green[ii].indexOf(',')+1); } }				
				if (rslt.squads.blue) { document.getElementById('sel_sqColor').value='blue'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_blue').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.blue[0]; for (var ii=1;ii<rslt.squads.blue.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.blue[ii].substr(0,rslt.squads.blue[ii].indexOf(','))); document.getElementById('squad_blue').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.blue[ii].substr(rslt.squads.blue[ii].indexOf(',')+1); } }				
				if (rslt.squads.cyan) { document.getElementById('sel_sqColor').value='cyan'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_cyan').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.cyan[0]; for (var ii=1;ii<rslt.squads.cyan.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.cyan[ii].substr(0,rslt.squads.cyan[ii].indexOf(','))); document.getElementById('squad_cyan').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.cyan[ii].substr(rslt.squads.cyan[ii].indexOf(',')+1); } }				
				if (rslt.squads.orange) { document.getElementById('sel_sqColor').value='orange'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_orange').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.orange[0]; for (var ii=1;ii<rslt.squads.orange.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.orange[ii].substr(0,rslt.squads.orange[ii].indexOf(','))); document.getElementById('squad_orange').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.orange[ii].substr(rslt.squads.orange[ii].indexOf(',')+1); } }				
				if (rslt.squads.black) { document.getElementById('sel_sqColor').value='black'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_black').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.black[0]; for (var ii=1;ii<rslt.squads.black.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.black[ii].substr(0,rslt.squads.black[ii].indexOf(','))); document.getElementById('squad_black').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.black[ii].substr(rslt.squads.black[ii].indexOf(',')+1); } }				
				if (rslt.squads.grey) { document.getElementById('sel_sqColor').value='grey'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_grey').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.grey[0]; for (var ii=1;ii<rslt.squads.grey.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.grey[ii].substr(0,rslt.squads.grey[ii].indexOf(','))); document.getElementById('squad_grey').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.grey[ii].substr(rslt.squads.grey[ii].indexOf(',')+1); } }				
				if (rslt.squads.brown) { document.getElementById('sel_sqColor').value='brown'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_brown').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.brown[0]; for (var ii=1;ii<rslt.squads.brown.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.brown[ii].substr(0,rslt.squads.brown[ii].indexOf(','))); document.getElementById('squad_brown').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.brown[ii].substr(rslt.squads.brown[ii].indexOf(',')+1); } }				
				if (rslt.squads.violet) { document.getElementById('sel_sqColor').value='violet'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_violet').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.violet[0]; for (var ii=1;ii<rslt.squads.violet.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.violet[ii].substr(0,rslt.squads.violet[ii].indexOf(','))); document.getElementById('squad_violet').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.violet[ii].substr(rslt.squads.violet[ii].indexOf(',')+1); } }				
				if (rslt.squads.pink) { document.getElementById('sel_sqColor').value='pink'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_pink').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.pink[0]; for (var ii=1;ii<rslt.squads.pink.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.pink[ii].substr(0,rslt.squads.pink[ii].indexOf(','))); document.getElementById('squad_pink').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.pink[ii].substr(rslt.squads.pink[ii].indexOf(',')+1); } }				
				if (rslt.squads.gold) { document.getElementById('sel_sqColor').value='gold'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_gold').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.gold[0]; for (var ii=1;ii<rslt.squads.gold.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.gold[ii].substr(0,rslt.squads.gold[ii].indexOf(','))); document.getElementById('squad_gold').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.gold[ii].substr(rslt.squads.gold[ii].indexOf(',')+1); } }				
				if (rslt.squads.coral) { document.getElementById('sel_sqColor').value='coral'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_coral').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.coral[0]; for (var ii=1;ii<rslt.squads.coral.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.coral[ii].substr(0,rslt.squads.coral[ii].indexOf(','))); document.getElementById('squad_coral').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.coral[ii].substr(rslt.squads.coral[ii].indexOf(',')+1); } }				
				if (rslt.squads.tan) { document.getElementById('sel_sqColor').value='tan'; document.getElementById('btn_addSquad').click(); document.getElementById('squad_tan').parentNode.getElementsByTagName('textarea')[0].textContent = rslt.squads.tan[0]; for (var ii=1;ii<rslt.squads.tan.length;ii++) { var pid=document.getElementById('plus_'+rslt.squads.tan[ii].substr(0,rslt.squads.tan[ii].indexOf(','))); document.getElementById('squad_tan').appendChild(pid); pid.getElementsByTagName('img')[0].src=rslt.squads.tan[ii].substr(rslt.squads.tan[ii].indexOf(',')+1); } }				
				
				addShowRoles();
				
				document.getElementById('img_src_tac1').value=rslt.img_src_tac1; document.getElementById('img_src_tac1').dispatchEvent(new Event('change'));
				document.getElementById('img_src_tac2').value=rslt.img_src_tac2; document.getElementById('img_src_tac2').dispatchEvent(new Event('change'));
				
				clearInterval(timerId);
				if (!rslt.editor || rslt.editor=='' || rslt.editor=='undefined') { 
					document.getElementById('btn_save').value='edit';
					for (var ii=0;ii<blkPlus.length;ii++) { blkPlus[ii].setAttribute('draggable', false); }
					isEditable=false;
				} else if (rslt.editor=='<?php echo $_SESSION['id']; ?>') {
					document.getElementById('btn_save').value='save';
					document.getElementById('btn_save').disabled=false;
					document.getElementById('btn_save').setAttribute('name','<?php echo $_SESSION['id']; ?>');
					for (var ii=0;ii<blkPlus.length;ii++) { blkPlus[ii].setAttribute('draggable', true); }
					isEditable=true;
				} else {
					//document.getElementById('btn_save').style.display='none';
					document.getElementById('btn_save').value='Editing by '+rslt.editor;
					document.getElementById('btn_save').disabled=true;					
					for (var ii=0;ii<blkPlus.length;ii++) { blkPlus[ii].setAttribute('draggable', false); }
					isEditable=false;
					//timerId = setInterval(teamExec(2), 5000);
				}
				
				updCount();
				
				forceLoad=false;				
			}
		}
	}
	
}

function exportList() {
	var ret='';
	
	var svSquad = {};
	var sq_red = document.getElementById('squad_red'); if (sq_red) { ret += "\n --- red --- \n";var svInsq = new Array(sq_red.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_red.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.red=svInsq; ret += sq_red.parentNode.getElementsByTagName('textarea')[0].value+"\n"; }
	var sq_green = document.getElementById('squad_green'); if (sq_green) { ret += "\n --- green --- \n";var svInsq = new Array(sq_green.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_green.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.green=svInsq;  ret += sq_green.parentNode.getElementsByTagName('textarea')[0].value+"\n"; }
	var sq_blue = document.getElementById('squad_blue'); if (sq_blue) { ret += "\n --- blue --- \n";var svInsq = new Array(sq_blue.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_blue.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.blue=svInsq;  ret += sq_blue.parentNode.getElementsByTagName('textarea')[0].value+"\n" }	
	var sq_cyan = document.getElementById('squad_cyan'); if (sq_cyan) { ret += "\n --- cyan --- \n";var svInsq = new Array(sq_cyan.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_cyan.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.cyan=svInsq;  ret += sq_cyan.parentNode.getElementsByTagName('textarea')[0].value+"\n" }	
	var sq_orange = document.getElementById('squad_orange'); if (sq_orange) { ret += "\n --- orange --- \n";var svInsq = new Array(sq_orange.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_orange.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.orange=svInsq;  ret += sq_orange.parentNode.getElementsByTagName('textarea')[0].value+"\n" }
	var sq_black = document.getElementById('squad_black'); if (sq_black) { ret += "\n --- black --- \n";var svInsq = new Array(sq_black.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_black.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.black=svInsq;  ret += sq_black.parentNode.getElementsByTagName('textarea')[0].value+"\n" }
	var sq_grey = document.getElementById('squad_grey'); if (sq_grey) { ret += "\n --- grey --- \n";var svInsq = new Array(sq_grey.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_grey.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.grey=svInsq;  ret += sq_grey.parentNode.getElementsByTagName('textarea')[0].value+"\n" }
	var sq_brown = document.getElementById('squad_brown'); if (sq_brown) { ret += "\n --- brown --- \n";var svInsq = new Array(sq_brown.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_brown.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.brown=svInsq;  ret += sq_brown.parentNode.getElementsByTagName('textarea')[0].value+"\n" }
	var sq_violet = document.getElementById('squad_violet'); if (sq_violet) { ret += "\n --- violet --- \n";var svInsq = new Array(sq_violet.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_violet.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.violet=svInsq;  ret += sq_violet.parentNode.getElementsByTagName('textarea')[0].value+"\n" }
	var sq_pink = document.getElementById('squad_pink'); if (sq_pink) { ret += "\n --- pink --- \n";var svInsq = new Array(sq_pink.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_pink.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.pink=svInsq;  ret += sq_pink.parentNode.getElementsByTagName('textarea')[0].value+"\n" }
	var sq_gold = document.getElementById('squad_gold'); if (sq_gold) { ret += "\n --- gold --- \n";var svInsq = new Array(sq_gold.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_gold.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.gold=svInsq;  ret += sq_gold.parentNode.getElementsByTagName('textarea')[0].value+"\n" }
	var sq_coral = document.getElementById('squad_coral'); if (sq_coral) { ret += "\n --- coral --- \n";var svInsq = new Array(sq_coral.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_coral.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.coral=svInsq;  ret += sq_coral.parentNode.getElementsByTagName('textarea')[0].value+"\n" }
	var sq_tan = document.getElementById('squad_tan'); if (sq_tan) { ret += "\n --- tan --- \n";var svInsq = new Array(sq_tan.parentNode.getElementsByTagName('textarea')[0].value); var blkInsq = sq_tan.getElementsByClassName('blkPlus'); for (var jj=0;jj<blkInsq.length;jj++) { var src=blkInsq[jj].getElementsByTagName('img')[0].src; ret += blkInsq[jj].innerText + " - " + src.substr(src.lastIndexOf('.')-2,2)+"\n";} svSquad.tan=svInsq;  ret += sq_tan.parentNode.getElementsByTagName('textarea')[0].value+"\n" }

	ret +="\n --- ЗАПАС --- \n"; 
	var blkPluses = document.getElementById('plusList').getElementsByClassName('blkPlus');
	for (var ii=0;ii<blkPluses.length;ii++) { ret += blkPluses[ii].innerText+"\n"; }
	document.getElementById('txt_buf').textContent=ret;
	
}
function importList() {
	if (!isEditable) return;
	var notfound='';
	var foundTwice='';
	var ret=document.getElementById('txt_buf').value;
	var formData = new FormData();
	formData.append("exec",'clear');
	formData.append("list",document.getElementById('txt_buf').value);
	var xhr = new XMLHttpRequest();  
	xhr.open("POST", "http://62.80.182.218/squad/import.php");
	xhr.send(formData);
	xhr.onreadystatechange = () => {
		if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
			ret=xhr.responseText;
			if (ret!='') {
				var lines=ret.split("\n"); console.log(ret);
				var rows=document.getElementById('allList').getElementsByTagName('tr');
				for (var ii=0;ii<lines.length;ii++) { 
					var nm = lines[ii].toLowerCase().trim();
					var found = 0;
					for (var jj=0;jj<rows.length;jj++) { 
						var nick = rows[jj].getElementsByTagName('td')[0].innerText.toLowerCase();
						if (nick == nm) { found++; rows[jj].dispatchEvent(new MouseEvent("dblclick"));  }
					}
					if (found ==0) notfound+=lines[ii];//+"\n";
					if (found > 1) foundTwice+=lines[ii] + ' - ' + found;// +"\n";
				}
				document.getElementById('ret').innerHTML="notfound: "+notfound+"<br>foundTwice: "+foundTwice;
			}
		}
	}
}

function allowDrop(ev) { ev.preventDefault(); ev.stopPropagation() }
function drag(ev) { ev.dataTransfer.setData("text", ev.target.id); }
function drop(ev,el) { ev.preventDefault(); var data = ev.dataTransfer.getData("text"); el.appendChild(document.getElementById(data)); updCount(); }

function updCount(){
	var squadListPlus=document.getElementById('squadList').getElementsByClassName('blkPlus');
	var blkPlus=document.getElementsByClassName('blkPlus');
	document.getElementById('spn_plus').innerHTML='Plus: '+blkPlus.length+' - '+squadListPlus.length + ' = ' +(blkPlus.length-squadListPlus.length);
}

teamExec(2);
</script>
</body>
</html>
<?php } ?>