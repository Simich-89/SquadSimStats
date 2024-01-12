<?php 
ini_set('max_execution_time', '1800');
ini_set('memory_limit', '512M');

$statDays = 30;
$statMtchs = 20;

if ( isset($_GET['formStat']) && isset($_GET['server']) ) {
try {
	$flLog='stat-'.$_GET['server'].'.log';
	
	file_put_contents($flLog,date("Y-m-d H:i:s").' ['.getmypid().'] :: START formStat='.$_GET['formStat'].'; server='.$_GET['server'].';'."\n");
	$ret=array();
	     if ($_GET['server']==3) $mysqli = new mysqli("62.80.182.218", "sjs450", "sjs450", "sjs450");
	else if ($_GET['server']==2) $mysqli = new mysqli("db-mysql-squadjs-do-user-13107778-0.b.db.ondigitalocean.com:25060", "squadjs2", 'BjduBie!LA!7qdty', "squadjs2");
	else if ($_GET['server']==1) $mysqli = new mysqli("db-mysql-squadjs-do-user-13107778-0.b.db.ondigitalocean.com:25060", "squadjs1", 'CK*95J$M9zF&nJe$', "squadjs1");
	else { echo 'no such server'; exit; }
	
	if (!$mysqli) { echo 'no db'; exit; }
	
	$stats=array();
	
	$whrMtchs='1=1';$mtchs='';
	if (!isset($_GET['getAll'])) {
		$qry="SELECT `id`,`startTime`,`winner` FROM `DBLog_Matches` WHERE `startTime` > '".date("Y-m-d", strtotime("-$statDays days"))."' AND `layerClassname` NOT LIKE ('%Invasion%') AND `layerClassname` NOT LIKE ('%Seed%') AND `layerClassname` NOT LIKE ('%Destruction%') AND `layerClassname` NOT LIKE ('%Insurgency%') ORDER BY `startTime` ASC";
		file_put_contents($flLog,"$qry\n", FILE_APPEND);
		if ( $rsltAll = $mysqli->query($qry) ) {
			if ($rsltAll->num_rows > ($statMtchs-1)) { 
				$ii=0; 
				while ($rAll = $rsltAll->fetch_assoc()) {
					if ($ii==0) $ret['statStart']=$rAll['startTime'];
					if ($ii==$rsltAll->num_rows) break;
					$mtchs.=','.$rAll['id']; $ii++; 
				} 
			} else { echo 'No enought matches'; exit; }
			$rsltAll->free();
		} else { file_put_contents($flLog,'DB Fail to get matches '.$mysqli->error."\n".$qry); exit; } 
		if (strlen($mtchs)>0) $mtchs=substr($mtchs,1,strrpos($mtchs,',')-1); else { echo 'No matches'; exit; }
	} else $statMtchs = 1;
	if ($mtchs!='') $whrMtchs="`match` IN ($mtchs)";
	
	$ret['lastTime']='';
	if ($rsltAll = $mysqli->query("SELECT `time` FROM `DBLog_Wounds` ORDER BY id DESC LIMIT 1;")) { if ($rAll = $rsltAll->fetch_assoc()) { $ret['lastTime'] = $rAll['time']; $rsltAll->free(); } } 
		
	$vehWpn="M1A1|M256A1|Turret|_coax|_AP|Warrior|Scimitar|40mm_GPR|RWS|AAVP7A1|TLAV|CROWS|LAV|ASLAV|Coyote|BRDM2|HJ73|ZBD05|Cupola|BMD4M|100mm|_PKT|_KPVT|BTR-D|Refleks|MTLB_NSV|BTR80|Arbalet|_BFV|_Frag|30mm|40MM_MK19|_AT3|120mm|23mm|115mm|DZJ-08";
	$morWpn="BP_BM21Grad_Weapon_INS|BP_BM21_Rocket_Proj2|BP_Projectile_Hell_Cannon|BP_Mortarround4|BP_S5_Proj2";
	$knfWpn="BP_SOCP_Knife_AUS|BP_SA80Bayonet|BP_Bayonet2000|BP_AKMBayonet|BP_QNL-95_Bayonet|BP_M9Bayonet|BP_AK74Bayonet";
	$expWpn="BP_L109A1Frag|BP_RGD5Frag|BP_M67Frag|BP_F1Frag|BP_Type86p_Frag|BP_C13Frag|BP_F1Frag_au|BP_RKG3Antitank|BP_40MM_VOG_Proj2|BP_40MM_Proj2|BP_Projectile_RifleGrenade_FNFAL_HEAT|BP_PLA_Deployable_TNT_Explosive_Timed|BP_Deployable_CompB_Explosive|BP_Deployable_TNT_1lb_Explosive_Timed|BP_Deployable_IED|BP_Deployable_TNT_600g_Explosive_Timed|BP_Deployable_SZ1_Explosives_Timed|BP_Deployable_Type72Mine|BP_Deployable_M15Mine|P_Deployable_TM62Mine";
	$atlWpn="BP_NLAW_Proj|BP_RPG7_Heat_Proj2|BP_RPG7V2_Tandem_2Mag|BP_M72A7_Rocket_Proj2|BP_SMAW_Heat_Proj2|BP_RPG7_Frag_Proj2|BP_RPG26_Rocket_Proj2|BP_RPG7_Tandem_Heat_Proj2|BP_Soldier_MIL_HAT_RPG29|BP_RPG29_Tandem_Heat_Proj2|BP_AT4_Rocket_Proj2|BP_SMAW_Tandem_Heat_Proj2|BP_PF-98_Tandem_Proj|BP_FFV751_Tandem_Heat_Proj2|BP_FFV551_Rocket_Proj2";
	$cmdWpn="BP_Projectile_155mm_Artillery|BP_Projectile_30mm_CAS|BP_Heavy_Mortarround4";
	
	$qry="SELECT * FROM DBLog_SteamUsers"; file_put_contents($flLog,"$qry\n", FILE_APPEND); //WHERE `steamID`='76561198022370044'
	$arrUsers=array(); if ($rslt = $mysqli->query($qry)) { while ($row = $rslt->fetch_assoc()) $arrUsers[] = $row; $rslt->free(); } else file_put_contents($flLog,"ERROR\n", FILE_APPEND);
	
	$qry="SELECT `victim`, `attacker`, `weapon`, `teamkill`, `match` FROM `DBLog_Wounds` WHERE `teamkill` IS NOT NULL AND $whrMtchs ORDER BY `match`"; file_put_contents($flLog,"$qry\n", FILE_APPEND);
	$arrWounds=array(); if ($rslt = $mysqli->query($qry)) { while ($row = $rslt->fetch_assoc()) $arrWounds[] = $row; $rslt->free(); } else file_put_contents($flLog,"ERROR\n", FILE_APPEND);
	
	$qry="SELECT `victim`, `attacker` FROM `DBLog_Deaths` WHERE $whrMtchs"; file_put_contents($flLog,"$qry\n", FILE_APPEND);
	$arrDeaths=array(); if ($rslt = $mysqli->query($qry)) { while ($row = $rslt->fetch_assoc()) $arrDeaths[] = $row; $rslt->free(); } else file_put_contents($flLog,"ERROR\n", FILE_APPEND);
	
	$qry="SELECT `reviver` FROM `DBLog_Revives` WHERE $whrMtchs"; file_put_contents($flLog,"$qry\n", FILE_APPEND);
	$arrRevives=array(); if ($rslt = $mysqli->query($qry)) { while ($row = $rslt->fetch_assoc()) $arrRevives[] = $row; $rslt->free(); } else file_put_contents($flLog,"ERROR\n", FILE_APPEND);
	
	$mysqli->close();
	
	$indxUsers=0; $cntUsers=count($arrUsers);
	file_put_contents($flLog,date("Y-m-d H:i:s")." Processing players $cntUsers / ", FILE_APPEND);
	foreach( $arrUsers as $user ) {
		$indxUsers++;
		if ($indxUsers % 100 == 0) file_put_contents($flLog,"$indxUsers ", FILE_APPEND);
		$usr=array();
		$usr['pos']=0;
		$usr['matchs']=0; $mtch=0;
		$usr['kills'] = 0;
		$usr['deaths'] = 0;
		$usr['falls'] = 0;
		$usr['knocks'] = 0;
		$usr['revs'] = 0;
		
		$usr['vehKnocks'] = 0;
		$usr['morKnocks'] = 0;
		$usr['knfKnocks'] = 0;
		$usr['expKnocks'] = 0;
		$usr['atlKnocks'] = 0;
		$usr['cmdKnocks'] = 0;
				
		$usr['tk'] = 0;
		$usr['wtk'] =0;
		
		$usr['steamID'] = $uid = $user['steamID'];
		$usr['lastName'] = $user['lastName'];
		
		$usr['weapons']=$usrWeps=array();
				
		foreach ( $arrWounds as $arr ) {
			if ($arr['victim']==$uid || $arr['attacker']==$uid) {
				if ($arr['victim']==$uid) { $usr['falls'] += 1; if ($arr['teamkill']==1) $usr['wtk'] += 1; }
				else if ($arr['attacker']==$uid) { 
					if ($arr['teamkill']==1) $usr['tk'] += 1;
					else {
						$usr['knocks'] += 1; 
						$fndWep=false; for ($ii=0;$ii<count($usrWeps);$ii++) { 	if ($usrWeps[$ii][0]==$arr['weapon']) { $usrWeps[$ii][1] += 1; $fndWep=true; break; } } if (!$fndWep) $usrWeps[]=array($arr['weapon'],1);
						if (preg_match("/$vehWpn/",$arr['weapon'])===1) $usr['vehKnocks'] += 1; else
						if (preg_match("/$morWpn/",$arr['weapon'])===1) $usr['morKnocks'] += 1; else 
						if (preg_match("/$knfWpn/",$arr['weapon'])===1) $usr['knfKnocks'] += 1; else 
						if (preg_match("/$expWpn/",$arr['weapon'])===1) $usr['expKnocks'] += 1; else 
						if (preg_match("/$atlWpn/",$arr['weapon'])===1) $usr['atlKnocks'] += 1; else 
						if (preg_match("/$cmdWpn/",$arr['weapon'])===1) $usr['cmdKnocks'] += 1; 
					}
				}
				if ($mtch!=$arr['match']) { $usr['matchs'] +=1; $mtch=$arr['match']; }
			}
		}
		if ($usr['matchs'] < $statMtchs) continue;
		
		foreach ( $arrDeaths as $arr ) {
			     if ($arr['victim']==$uid) $usr['deaths'] += 1;
			else if ($arr['attacker']==$uid) $usr['kills'] += 1;
		}
		
		usort($usrWeps, fn($b, $a) => $a[1] <=> $b[1]); for ($ii=0;$ii<count($usrWeps);$ii++) { $usr['weapons'][]=$usrWeps[$ii]; if ($ii==19) break; }
		
		foreach ( $arrRevives as $arr ) { if ($arr['reviver']==$uid) $usr['revs'] += 1; }
		
		$usr['kd'] = $usr['kills'];   if ($usr['deaths'] > 0) $usr['kd']  = round( $usr['kills'] / $usr['deaths']  , 3);
		//$usr['knd'] = $usr['knocks']; if ($usr['deaths'] > 0) $usr['knd'] = round( $usr['knocks'] / $usr['deaths'] , 3);
		//$usr['krd'] = $usr['kills']+$usr['revs']; if ($usr['deaths'] > 0) $usr['krd'] = round( $usr['krd'] / $usr['deaths'] , 3);
		//$usr['fd'] = $usr['deaths'];  if ($usr['deaths']  > 0) $usr['fd']  = round( $usr['falls'] / $usr['deaths']  , 3);
		
		$dt = $usr['deaths']; if ($dt==0) $dt=1;
		$kn_kl = $usr['knocks'] - $usr['kills']; if ($kn_kl < 0) $kn_kl=0; 
		$fl_dt = $usr['falls'] - $usr['deaths']; if ($fl_dt < 0) $fl_dt=0; 
		$dtfldt= $usr['deaths'] + ($fl_dt / 2); if ($dtfldt <= 0) $dtfldt=1;
		$usr['mvp']=round( ( $usr['kills'] + ($kn_kl/2) + ($usr['revs']/2) )  / $dtfldt , 3 );
		$usr['mvp_svk']=round( ( ( $usr['kills'] + ($kn_kl/2) + ($usr['revs']/2) )  / $dtfldt ) * log10($usr['matchs']), 3 ); $usr['mvp_svk'] = round(floatval($usr['mvp_svk']) * 1000);
		
		$stats[]=$usr; 
//break;
	}
	file_put_contents($flLog,"\n".date("Y-m-d H:i:s")." $indxUsers done \n", FILE_APPEND);
		
	usort($stats, fn($b, $a) => $a['mvp'] <=> $b['mvp']);
	
	for($ii=0;$ii<count($stats);$ii++) { $stats[$ii]['pos']=$ii+1; }
	
	$ret['stats']=$stats;
	$ret['mtchs']=$mtchs;
	$ret['dttm']=date("Y-m-d H:i:s");
	
	$isAll=''; if (isset($_GET['getAll'])) $isAll='-all';
	file_put_contents('stat-'.$_GET['server'].$isAll.'.json',json_encode($ret));
		
	file_put_contents($flLog,date("Y-m-d H:i:s").' ['.getmypid().'] :: END formStat='.$_GET['formStat'].'; server='.$_GET['server'].';'."\n", FILE_APPEND);
	
	exit;
} catch (Exception $ee) {
	$mysqli->close();
	file_put_contents($flLog,date("Y-m-d H:i:s").' ['.getmypid().'] :: Exception'. $ee->getMessage()."\n", FILE_APPEND);
	exit;
}
} else { 
$isAdm=false; if (isset($_GET['adm'])) $isAdm=true;
?>
<html>
<head>
<title>STATS</title><meta charset="utf-8">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@100;200;300&display=swap" rel="stylesheet">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<style>

body, table, td, th, div, span, p, a { font-family: 'Kanit', sans-serif; color: #fff; } 
body { background: #89866d; }

table { border-collapse: collapse; }

#blkMain { width:100%; }
#blkStat { position:relative; width:1200px; display:inline-block; vertical-align:top; }
#blkMatches { width:500px; display:inline-block; vertical-align:top; }
#tblMatches { width:100%; }

#tblStat { width:100%; border-collapse: collapse; }
#tblStat td { border:1px solid lightgrey; padding: 2px; text-align:center; } 

#tblStat > thead { position: sticky; top: 0; background:#a89dff; border-bottom: 2px solid lightgrey ; } 
#tblStat > thead { color: #fff; background: #604439; }
#tblStat > thead th { border:1px solid lightgrey; padding: 0px; font-size:110%;  width: 70px; transition: all 2s ease-out; }
#tblStat > thead th { color: #fff; background: #604439; }
#tblStat > thead th:nth-child(3) { width: 300px; }
.colClose, .colClose > img { width:0 !important; padding: 0px !important; font-size:0% !important;  }
#tblStat tr:hover { background:#554840; cursor:pointer; }

#tblStat img { width:30px; margin-bottom:-7px; }

.rowInf {  }
.rowInf:hover {  }

.rowInf td { text-align:left !important; }

.infBlk { display:inline-block; margin:5px; padding:5px; border:1px solid lightgrey; }

.blkSrv { display:inline-block; padding:5px 10px; background:#554840; cursor:pointer; }
.blkSrv.sel { background:#604439; }

#blkLoading { position:fixed; width:100%; height:100%; top:0; left:0; background:rgba(255,255,255,0.8); padding:0; margin:0; text-align:center; z-index: 9999; display:none; }
#blkLoading img { border-radius:600px; margin-top:50px; }

#blkTotal { position:relative; border: 1px solid #604439; padding:10px; }
#blkccStat > .blkClan { position:relative; display:inline-block; border: 1px solid #604439; margin:5px; padding:2px; cursor:pointer; text-align:center; }
#blkccStat > .blkClan:hover, #blkStat > .blkClan.sel { background:#604439; }

</style>
<script> var server=0; var isGetall='';  </script>
</head>
<body>
<!--input type="button" onclick="getStat(0,0)" value="upd" /-->
<div id="blkLoading"><img src="icons/loading.webp"></div>
<?php if ($isAdm) {  ?> 
<!--script>
function formStat() {
	document.getElementById('blkLoading').style.display='block';
	var xhr = new XMLHttpRequest();  
	xhr.open("GET", "?formStat=0&server="+server+"&"+isGetall);
	xhr.setRequestHeader("Cache-Control", "no-cache, no-store, max-age=0");
	xhr.onreadystatechange = () => {
		if (xhr.readyState === XMLHttpRequest.DONE) { 
			if (xhr.status === 200) {
				console.log(xhr.responseText);
			}
			document.getElementById('blkLoading').style.display='none';
		}
	}
	xhr.send();	
}
</script-->
<input type="checkbox" onclick="if (this.checked == true) isGetall='getAll'; else isGetall='';" />get all <!--input type="button" value="form stat now" onclick="formStat();" /-->
<a target="_blank" href="stat.log">Лог</a>
<?php   } ?>
<div id="usrStar"></div>
<div id="blkMain">
<div style="width:100%;position:relative;">
	<div class="blkSrv" onclick="selServer(1,this); getStat(0);" >Keep Calm And Ukraine #1</div>
	<div class="blkSrv" onclick="selServer(2,this); getStat(0);" >Keep Calm And Ukraine #2</div>
<?php if ($isAdm) { ?> <div class="blkSrv" onclick="selServer(3,this); getStat(0);" >4.5.0. Training</div> <?php } ?>
</div>
<div id="blkTotal"></div>
<div id="blkStat"><div id="blkccStat"></div>
<table id="tblStat"><thead id="tbhStat"></thead><tbody id="tbdStat"></tbody></table>
</div></div>
<script>
function showMoreKnocks(e) {
	e.preventDefault(); e.stopPropagation();
	var cells = document.querySelectorAll('.colClose'); for (var ii=0;ii<cells.length;ii++) {
		cells[ii].classList.remove("colClose"); cells[ii].classList.add("colOpened");
	}
}
function selServer(id,el) { server=id; const elements = document.getElementsByClassName('blkSrv'); for(var ii=0;ii<elements.length;ii++) { elements[ii].classList.remove("sel"); } el.classList.add("sel"); }
function getStat() {
	document.getElementById('tbdStat').innerHTML = '';
	document.getElementById('blkLoading').style.display='block';
	var ttlKills=0; var ttlRevs=0; var ttlTks=0; var ttlGames=0; var lastTime='';
<?php if ($isAdm) { ?> 
	var cs450=0; var csUCL=0; var csSOF=0; var csU24=0; var csRATS=0; var csOP=0; var csBROS=0;  var csSTMG=0; var csWG=0;
	var cc450=0; var ccUCL=0; var ccSOF=0; var ccU24=0; var ccRATS=0; var ccOP=0; var ccBROS=0;  var ccSTMG=0; var ccWG=0;
<?php } ?>
	var xhr = new XMLHttpRequest();
	var isAll=''; if (isGetall!='') isAll='-all';
	xhr.open("GET", "stat-"+server+isAll+".json");
	xhr.setRequestHeader("Cache-Control", "no-cache, no-store, max-age=0");
	xhr.onreadystatechange = () => {
		if (xhr.readyState === XMLHttpRequest.DONE) { 
			if (xhr.status === 200) {
				document.getElementById('tbhStat').innerHTML = '<tr><th title="pos by mvp">pos</th><th title="(kills + (knocks-kills / 2) + (revs / 2) / (death + (falls-death / 2)))">mvp</th><th>Name</th><th><img src="icons/kills.png" /><br/>kills</th><th><img src="icons/deaths.png" /><br/>deaths</th><th><img src="icons/knocks.png" /><br/>knocks<span style="cursor:pointer; color: #fff; padding-left: 5px; top: -10px; position: relative;" onclick="showMoreKnocks(event);">&gt;</span></th><th class="colClose"><img src="icons/veh.png" /><br/>veh</th><th class="colClose"><img src="icons/mort.png" /><br/>mort</th><th class="colClose">gren<br/>expl</th><th class="colClose">atl</th><th class="colClose">knife</th><th class="colClose">cmd</th><th><img src="icons/revs.png" /><br/>revs</th><th><img src="icons/falls.png" /><br/>falls</th><th><img src="icons/tk.png" /><br/>tk</th><th><img src="icons/wtk.png" /><br/>wtk</th><th>k / d</th><?php if ($isAdm) { ?><th>mvp_svk</th><?php } ?><th>games</th><th>+</th></tr>';
				var ret='';
				var arr = JSON.parse(xhr.responseText); //console.log(xhr.responseText);
				for (var ii=0;ii<arr['stats'].length;ii++) {
					var tblWeapons='';
					//let srtWep = []; for (var wep in arr['stats'][ii].weapons) srtWep.push([wep, arr['stats'][ii].weapons[wep]]); srtWep.sort(function(a, b) { return b[1] - a[1]; });
					//var wIndx=1; for (const key in srtWep) { if (srtWep.hasOwnProperty(key)) { tblWeapons+='<tr><td>'+srtWep[key][0]+'</td><td>'+srtWep[key][1]+'</td></tr>'; } }
					for (var jj=0;jj<arr['stats'][ii].weapons.length;jj++) { tblWeapons+='<tr><td>'+arr['stats'][ii].weapons[jj][0]+'</td><td>'+arr['stats'][ii].weapons[jj][1]+'</td></tr>'; }
					
					ret += '<tr><td>'+arr['stats'][ii].pos+'</td><td>'+arr['stats'][ii].mvp+'</td><td style="text-align:left;"><a target="_blank" href="https://steamcommunity.com/profiles/'+arr['stats'][ii].steamID+'">'+arr['stats'][ii].lastName+'</a></td><td>'+arr['stats'][ii].kills+'</td><td>'+arr['stats'][ii].deaths+'</td><td>'+arr['stats'][ii].knocks+'</td><td class="colClose">'+arr['stats'][ii].vehKnocks+'</td><td class="colClose">'+arr['stats'][ii].morKnocks+'</td><td class="colClose">'+arr['stats'][ii].expKnocks+'</td><td class="colClose">'+arr['stats'][ii].atlKnocks+'</td><td class="colClose">'+arr['stats'][ii].knfKnocks+'</td><td class="colClose">'+arr['stats'][ii].cmdKnocks+'</td><td>'+arr['stats'][ii].revs+'</td><td>'+arr['stats'][ii].falls+'</td><td>'+arr['stats'][ii].tk+'</td><td>'+arr['stats'][ii].wtk+'</td><td>'+arr['stats'][ii].kd+'</td><?php if ($isAdm) { ?><td>'+arr['stats'][ii].mvp_svk+'</td><?php } ?>'+/*<td>'+arr['stats'][ii].knd+'</td><td>'+arr['stats'][ii].krd+'</td><td>'+arr['stats'][ii].fd+'</td>*/'<td>'+arr['stats'][ii].matchs+'</td><td><input type="button" onclick="getInfo(this.parentNode,this.parentNode.parentNode)" value="+"> \
<span style="display:none;"><div class="infBlk"><table>'+tblWeapons+'</table></div></span></td></tr>'; //<div class="infBlk"><div style="background: #604439;"><img src="icons/kills.png" /><b>Топ жертв</b></div><table>'+tblVictims+'</table></div><div class="infBlk"><div style="background: #604439;"><img src="icons/deaths.png" /><b>Топ убивць</b></div><table>'+tblAttackers+'</table></div>
					
					ttlKills += parseInt(arr['stats'][ii].kills); ttlRevs+=parseInt(arr['stats'][ii].revs); ttlTks+=parseInt(arr['stats'][ii].tk);

<?php if ($isAdm) { ?> 					
					     if (arr['stats'][ii].lastName.indexOf('4.5.0.') ===0) { cs450  += arr['stats'][ii].mvp; cc450++; }
					else if (arr['stats'][ii].lastName.indexOf('[UCL]') ===0) { csUCL  += arr['stats'][ii].mvp; ccUCL++; }
					else if (arr['stats'][ii].lastName.indexOf('[SOF]') ===0) { csSOF  += arr['stats'][ii].mvp; ccSOF++; }
					else if (arr['stats'][ii].lastName.indexOf('U24.')  ===0) { csU24  += arr['stats'][ii].mvp; ccU24++; }
					else if (arr['stats'][ii].lastName.indexOf('[RATS]')===0) { csRATS += arr['stats'][ii].mvp; ccRATS++; }
					else if (arr['stats'][ii].lastName.indexOf('ОП |')  ===0) { csOP   += arr['stats'][ii].mvp; ccOP++; }
					else if (arr['stats'][ii].lastName.indexOf('[BROS]')===0) { csBROS += arr['stats'][ii].mvp; ccBROS++; }
					else if (arr['stats'][ii].lastName.indexOf('[STMG]')===0) { csSTMG += arr['stats'][ii].mvp; ccSTMG++; }
					else if (arr['stats'][ii].lastName.indexOf('[WG]')  ===0) { csWG   += arr['stats'][ii].mvp; ccWG++; }
<?php } ?>					
					
				}				
				document.getElementById('tbdStat').innerHTML = ret;
				
				if (arr['lastTime'] != undefined || arr['lastTime'] != '') { var lt = Date.parse(arr['lastTime']); if (lt) lastTime=arr['lastTime'];  }
				if (arr['mtchs'] != undefined ) ttlGames=arr['mtchs'].split(',').length;
				document.getElementById('blkTotal').innerHTML = '<p style="margin:0;">'
+'Статистика ведеться по іграм в режимах AAS, RAAS, TC за останні <b><?php echo $statDays ?></b> днів ( <b>'+arr['statStart']+'</b> 0 UTC ) по гравцям, що зіграли <b><?php echo $statMtchs;?></b> матчів<br>'
+'Останнє оновлення: <b>'+arr['dttm'] + '</b> <i>(last event '+lastTime+' 0 UTC)</i><br>'
+'Було зіграно <b>'+ttlGames+'</b> матчі, героїчно загинуло <b>'+ttlKills+'</b>, з них підступно застрелено в спину своїми <b>'+ttlTks+'</b>, а <b>'+ttlRevs+'</b> дочекались своїх медиків.'
+'</p>'

<?php if ($isAdm) { ?>
				var ccStats=''; document.getElementById('blkccStat').innerHTML='';
				if (cc450>0)  ccStats+=('<div id="blkCS450" class="blkClan"  onclick="filterClanStat(\'4.5.0.\', this);">4.5.0.<br/>' + (cs450  / cc450) .toFixed(2) + '</div>' );
				if (ccUCL>0)  ccStats+=('<div id="blkCSUCL" class="blkClan"  onclick="filterClanStat(\'[UCL]\', this);">[UCL]<br/>' + (csUCL  / ccUCL) .toFixed(2) + '</div>' );
				if (ccSOF>0)  ccStats+=('<div id="blkCSSOF" class="blkClan"  onclick="filterClanStat(\'[SOF]\', this);">[SOF]<br/>' + (csSOF  / ccSOF) .toFixed(2) + '</div>' );
				if (ccU24>0)  ccStats+=('<div id="blkCSU24" class="blkClan"  onclick="filterClanStat(\'U24.\',  this);">U24.<br/>' + (csU24  / ccU24) .toFixed(2) + '</div>' );
				if (ccRATS>0) ccStats+=('<div id="blkCSRATS" class="blkClan" onclick="filterClanStat(\'[RATS]\',this);">[RATS]<br/>' + (csRATS / ccRATS).toFixed(2) + '</div>' );
				if (ccOP>0)   ccStats+=('<div id="blkCSOP" class="blkClan"   onclick="filterClanStat(\'ОП |\',  this);">ОП |<br/>' + (csOP   / ccOP)  .toFixed(2) + '</div>' );
				if (ccBROS>0) ccStats+=('<div id="blkCSBROS" class="blkClan" onclick="filterClanStat(\'[BROS]\',this);">[BROS]<br/>' + (csBROS / ccBROS).toFixed(2) + '</div>' );
				if (ccSTMG>0) ccStats+=('<div id="blkCSSTMG" class="blkClan" onclick="filterClanStat(\'[STMG]\',this);">[STMG]<br/>' + (csSTMG / ccSTMG).toFixed(2) + '</div>' );
				if (ccWG>0)   ccStats+=('<div id="blkCSWG" class="blkClan"   onclick="filterClanStat(\'[WG]\',  this);">[WG]<br/>' + (csWG   / ccWG)  .toFixed(2) + '</div>' );
				document.getElementById('blkccStat').innerHTML = ccStats + document.getElementById('blkccStat').innerHTML;
<?php } ?>				
				
				document.getElementById('tblStat').querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
					const elements = document.getElementsByClassName('rowInf'); while(elements.length > 0){ elements[0].parentNode.removeChild(elements[0]); }
					const table = document.getElementById('tbdStat');
					Array.from(table.querySelectorAll(':scope > tr:nth-child(n+1)'))
						.sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
						.forEach(tr => table.appendChild(tr) );
					var rows = table.querySelectorAll(':scope > tr');
					for (var ii=0; ii<rows.length; ii++) { rows[ii].getElementsByTagName('td')[0].innerHTML=(ii+1); }
				})));
				
			}
			document.getElementById('blkLoading').style.display='none';
		}
	}
	xhr.send();
}

function filterClanStat(cln,el) {
	var rows = document.getElementById('tbdStat').querySelectorAll(':scope > tr');
	for (var ii=0; ii < rows.length; ii++) { rows[ii].style.display='none'; }
	for (var ii=0; ii < rows.length; ii++) {
		if ( rows[ii].innerHTML.indexOf('">'+cln) > 0 ) {
			rows[ii].style.display='table-row'; 
		}
	}
}

function getInfo(inf,el) {
	const elements = document.getElementsByClassName('rowInf'); while(elements.length > 0){ elements[0].parentNode.removeChild(elements[0]); }
	var row = document.getElementById('tbdStat').insertRow(el.rowIndex);
	row.classList.add("rowInf");
	row.onclick=function() { this.parentNode.removeChild(this) }  ;
	row.innerHTML = '<td style="text-align:center;" colspan="'+el.cells.length+'">'+inf.getElementsByTagName('span')[0].innerHTML+'</td>';
}

const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

const comparer = (idx, asc) => (a, b) => ((v1, v2) => 
    v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
    )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

</script>

</body>
</html>

<?php } 
/*

BP_L109A1Frag
BP_RGD5Frag
BP_M67Frag
BP_F1Frag
BP_Type86p_Frag
BP_C13Frag
BP_F1Frag_au
BP_RKG3Antitank

BP_40MM_VOG_Proj2
BP_40MM_Proj2

BP_Projectile_RifleGrenade_FNFAL_HEAT

BP_PLA_Deployable_TNT_Explosive_Timed
BP_Deployable_CompB_Explosive
BP_Deployable_TNT_1lb_Explosive_Timed
BP_Deployable_IED
BP_Deployable_TNT_600g_Explosive_Timed
BP_Deployable_SZ1_Explosives_Timed

BP_Deployable_Type72Mine
BP_Deployable_M15Mine
BP_Deployable_TM62Mine

BP_SOCP_Knife_AUS
BP_SA80Bayonet
BP_Bayonet2000
BP_AKMBayonet
BP_QNL-95_Bayonet
BP_M9Bayonet
BP_AK74Bayonet

BP_NLAW_Proj
BP_RPG7_Heat_Proj2
BP_RPG7V2_Tandem_2Mag
BP_M72A7_Rocket_Proj2
BP_SMAW_Heat_Proj2
BP_RPG7_Frag_Proj2
BP_RPG26_Rocket_Proj2
BP_RPG7_Tandem_Heat_Proj2
BP_Soldier_MIL_HAT_RPG29
BP_RPG29_Tandem_Heat_Proj2
BP_AT4_Rocket_Proj2
BP_SMAW_Tandem_Heat_Proj2
BP_PF-98_Tandem_Proj
BP_FFV751_Tandem_Heat_Proj2
BP_FFV551_Rocket_Proj2

BP_BM21Grad_Weapon_INS
BP_BM21_Rocket_Proj2
BP_Projectile_Hell_Cannon
BP_Mortarround4
BP_S5_Proj2

BP_Heavy_Mortarround4
BP_Projectile_155mm_Artillery
BP_Projectile_30mm_CAS
BP_APKWS_Proj2 ??
BP_InfantryRazorwire

BP_Projectile_76mm_HEAT
BP_Projectile_76mm_Frag
BP_Projectile_76mm_Smoke

BP_Emplaced_ZU23-2_Antiaircannon_Weapon_Deployable

BP_QJZ89_RWS
BP_ZPT-98_coax
BP_ZPT-98_HEAT

BP_M1A1_FLEX_M2
BP_M256A1_coax_AUS
BP_M256A1_AP_AUS
BP_Mag58_Loaders_Turret_Desert
BP_L55_coax
BP_L94A1_coax
BP_L30A1_AP
BP_Warrior_762_Tan
BP_Scimitar_762
BP_Warrior_Rarden_AP_Tan
BP_Projectile_30mm_HE_Red_Rarden
BP_Scimitar_Rarden_AP
BP_Warrior_CTAS_762
BP_Projectile_40mm_GPR_Red
BP_Warrior_CTAS_AP
BP_EnforcerRWS_L37A2
BP_AAVP7A1_M2_Woodland
BP_TLAV_M240_Desert
BP_TLAV_M2_Desert
BP_C6_LUVW_Desert
BP_CROWS_M2_Nanuk_Desert
BP_CROWS_C6_LAV_Desert
BP_LAV_762_Desert
LAV_M252_AP_Desert
ASLAV_762
ASLAV_M252_AP
BP_Coyote_762_Desert
Coyote_M252_AP_Desert
BP_M240G_MGO_Turret_Weapon_Desert
BP_BRDM2_PKT_Insurgents
BP_BRDM2_KPVT_Insurgents
BP_PMV_RWS_M2
BP_EnforcerRWS_M2_Woodland
BP_EnforcerRWS_M2
BP_2A45_AP
BP_HJ73_Proj2
BP_ZBD05_HJ73C_ATGM
BP_ZTD05_coax
BP_ZBD05_QTJ02
BP_ZBD05_ZPT-99_APFSDS
BP_Cupola_QJZ89_Naval
BP_BMD4M_2A70_coax
BP_Projectile_100mm_Frag
BP_BMD4M_2A72_AP
BP_BMD1M_PKT
BP_ZBD04A_2A70_coax
BP_ZBD04A_ZPT99_AP
BP_SPG9_pg9v_Heat_Proj2
BP_QJY88_CSK131
BP_Sprut_RWS_PKT
BP_QJZ89_CSK131
BP_TAPV_CROWS_M2_Desert
BP_TAPV_CROWS_C6_Desert
BP_2A45_coax
BP_Projectile_125mm_HEAT
BP_BTRMDM_Bow_PKT
BP_PK_RWS_Gun
BP_BMD1M_Bow_PKT
BP_Kord_BTR-D
BP_Projectile_23mm_HE_Green
BP_MTLB_PKT_Militia
BP_MTLB30mm_PKT_green
BP_Projectile_30mm_HE
BP_MTLB_30mm_AP_gun_Green
BP_BTR82A_PKT_desert
BP_BTR82A_RUS_2A72_AP_Desert
BP_BMP2_PKT_Desert
BP_BMP2_2A42_AP_Desert
BP_Kord_Cupola_Weapon_Desert
BP_Refleks_Proj2
BP_2A46_coax
BP_MTLB_NSV_Desert
BP_BTR80_RUS_PKT_Desert
BP_BTR80_RUS_KPVT_Desert
BP_Arbalet_Kord_Desert
BP_M240_Loaders_Turret
BP_CROWS_M240
BP_CROWS_M2_Stryker
BP_CROWS_M240_Stryker
BP_BFV_762
BFV_M252_AP
BP_Projectile_30mm_HE_Red
LAV25_M252_AP
BP_BTR80_MEA_PKT
BP_BTR80_MEA_KPVT
BP_BMP2_MEA_PKT
BP_Projectile_30mm_HE_Green
BP_BMP2_MEA_2A42_AP
BP_AT3_Proj2
BP_BMP1_PKT
BP_T72AV_Kord_Cupola_Weapon
BP_Projectile_125mm_Frag
BP_2A46_T72AV_coax
BP_2A20_coax
BP_CROWS_M2
BP_40MM_MK19_Proj
BP_AAVP7A1_M2
BP_M1A1_USMC_Cmdr_M2
BP_M256A1_coax
BP_Projectile_120mm_HEAT
BP_Projectile_23mm_HE
BP_Kornet_Proj2
BP_2A20_AP
BP_Projectile_115mm_HEAT
BP_2A46_T72AV_AP
BP_2A46_AP
BP_ZTD05_ZPL-98A_AP
BP_L55_AP
BP_ZPT-98_AP
BP_DZJ-08_Heat_Proj


tail -f /var/log/apache2/error.log
tail -f /var/www/html/stats/stat-1.log
mysql -hdb-mysql-squadjs-do-user-13107778-0.b.db.ondigitalocean.com -usquadjs1 -p'CK*95J$M9zF&nJe$' --port=25060 squadjs1
sudo su - www-data -c 'wget -q -O /dev/null "http://62.80.182.218/stats/index.php?formStat=0&server=1";'
0 * * * * wget -q -O /dev/null "http://62.80.182.218/stats/index.php?formStat=0&server=1"; wget -q -O /dev/null "http://62.80.182.218/stats/index.php?formStat=0&server=2"; wget -q -O /dev/null "http://62.80.182.218/stats/index.php?formStat=0&server=3";


*/
?>