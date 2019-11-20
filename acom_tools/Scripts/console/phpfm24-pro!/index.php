<?
//ip access
$ip = getenv('REMOTE_ADDR');
$ips = explode('.', $ip);
if($ips['0'] == '62' && $ips['1'] == '65')
{
        if(gethostbyname('pservers.mooo.com') != $ip) die('no access!');
}
//������, ������� ������� ������������� �������
class Timer_phpfm
{
	var $startTime;
	var $endTime;
	function start() { $this->startTime = gettimeofday(); }
	function stop() { $this->endTime = gettimeofday(); }
	function elapsed() { return (($this->endTime["sec"] - $this->startTime["sec"]) * 1000000 + ($this->endTime["usec"] - $this->startTime["usec"])) / 1000000; }
}
$timer = new Timer_phpfm();
$timer->start();
//**
session_name('fmsid');
session_start();
//**
$phpfm['php_self']=basename(__FILE__);
$phpfm['rootpath']=dirname(__FILE__);
//**
$phpfm['version']="2.4 pro";
$phpfm['copyright']="phpFM version ".$phpfm['version'];
//**
//�������
//**
include($phpfm['rootpath']."/config.php");
include($phpfm['rootpath']."/accounts.php");
include($phpfm['rootpath']."/funcs.php");
include($phpfm['rootpath'].'/description.php');
//**
include($phpfm['rootpath']."/check.php");
//**
if(!isset($_SESSION['started']))
{
	$_SESSION['use_gzip']=true;
	$_SESSION['show_time']=true;
	$_SESSION['thumbnails']=false;
	$_SESSION['show_dirsize']=false;
	$_SESSION['drives']=get_disk_drives();
}
//**
$_SESSION['started']=true;
//**
if(isset($_POST['submitted']))
{
	if(isset($_POST['show_dirsize'])) $_SESSION['show_dirsize']=true;
	else $_SESSION['show_dirsize']=false;
	//**
	if(isset($_POST['show_time'])) $_SESSION['show_time']=true;
	else $_SESSION['show_time']=false;
	//**
	if(isset($_POST['use_gzip'])) $_SESSION['use_gzip']=true;
	else $_SESSION['use_gzip']=false;
	//**
	if(isset($_POST['thumbnails'])) $_SESSION['thumbnails']=true;
	else $_SESSION['thumbnails']=false;
}
//**
if(isset($_SESSION['logined_as_user']))
{
	$phpfm['user-settings']=DumpSettings($_SESSION['user_name'], chr(0));
	$phpfm['user-name']=$_SESSION['user_name'];
	$phpfm['user-space']=GetDiskSpace();
	$phpfm['user-space-left']=floor($phpfm['user-settings']['quota']-$phpfm['user-space']);
}else if(isset($_SESSION['logined']))
{
	$phpfm['user-space-left']=999999; #�.�. ����� �����
	$phpfm['user-settings']['eval']=1;
	$phpfm['user-name']='�����';
	$phpfm['user-settings']['dir']=':�����:';
	$phpfm['user-settings']['quota']=999999;
}
//**
$phpfm['gzip']=false;
if (phpversion() >= "4.0.4pl1" && extension_loaded("zlib") && $_SESSION['use_gzip']==true)
{
	@ob_end_clean();
	@ob_start("ob_gzhandler");
	$phpfm['gzip']=true;
}
//**
if(!isset($_GET['act'])) $_GET['act']="open";
switch($_GET['act'])
{
case "open":
	if(!isset($_GET['dir']))
	{
		if(isset($_SESSION['logined'])) $phpfm['dir']=(isset($_SESSION['path'])) ? ($_SESSION['path']) : (realpath(".."));
		else $phpfm['dir']=(isset($_SESSION['path'])) ? ($_SESSION['path']) : (realpath($phpfm['user-settings']['dir']));
	}else
	{
		$phpfm['dir']=$_SESSION['path']."/".$_GET['dir'];
	}
	//**
	if(isset($_GET['absolute_dir'])) $phpfm['dir']=$_GET['absolute_dir'];
	//**
	break;
case "rename":
	CheckAccess($_SESSION['path']."/".$_GET['file2']);
	//**
	if(empty($_GET['file2'])) { echo "<script language=javascript>alert('��� ����� �� ������ ���� ������ !');</script>";break; }
	//**
	if(!@rename($_SESSION['path']."/".$_GET['file2'],$_SESSION['path']."/".$_GET['file'])) echo "<script language=javascript>alert('������ ��� ��������������: ".$php_errormsg."');</script>";
	break;
case "delete":
	if(isset($_GET['dir']))
	{
		//� ��� ���� ��� ��������...
		CheckAccess($_SESSION['path']."/".$_GET['dir']);
		//**
		$phpfm['dirsize']=dirsize($_SESSION['path']."/".$_GET['dir']);
		//**
		if(@removedir($_SESSION['path']."/".$_GET['dir'])) EditDiskSpace(-$phpfm['dirsize']);
		else echo "<script language=javascript>alert('����� �� ����� ���� �������.\\n������ ����� � ����� ���������� �����, ������ � ������� ��������\\n��������� ����� ������ ������ �����.\\n ��� ������ ����� ����� �� ������.');</script>";
	}else if(isset($_GET['file']))
	{
		CheckAccess($_SESSION['path']."/".$_GET['file']);
		//**
		$phpfm['filesize']=filesize($_SESSION['path']."/".$_GET['file']);
		//**
		if(@unlink($_SESSION['path']."/".$_GET['file'])) EditDiskSpace(-$phpfm['filesize']);
		else echo "<script language=javascript>alert('������ ��� �������� �����: ".$php_errormsg."');</script>";
	}else
	{
		$phpfm['parts']=explode(':',$_GET['all']);
		$phpfm['success']=true;
		foreach($phpfm['parts'] as $k=>$v)
		{
			if(empty($v)) continue;
			CheckAccess($_SESSION['path']."/".$v);
			//**
			$phpfm['filesize']=unisize($_SESSION['path']."/".$v,false,false,true);
			//**
			if(@deleteall($_SESSION['path']."/".$v) && $phpfm['success']) EditDiskSpace(-$phpfm['filesize']);
			else $phpfm['success']=false;
		}
		if(!$phpfm['success']) echo "<script language=javascript>alert('�������� �� ����� ���� �������.\\n������ ����� � ����� ���������� �����, ������ � ������� ��������\\n��������� ����� ������ ������ �����.\\n ��� ������ ����� ����� �� ������.');</script>";
	}
	break;
case "download":
	//**
	$phpfm['shortname']=$_GET['file'];
	$phpfm['file']=$_SESSION['path']."/".$_GET['file'];
	//� ��� ��� ���� ��������...
	CheckAccess($phpfm['file']);
	//**
	header("Content-Type: application/force-download\r\n"); 
	header("Content-Transfer-Encoding: binary\r\n");
	header("Content-Length: ".filesize($phpfm['file']));
	header("Content-Disposition: attachment; filename=".$phpfm['shortname']."\r\n");
	readfile($phpfm['file']);
	//**
	die();
	exit;
	break;
case "properties":
	if(isset($_GET['file']))
	{
		$phpfm['file']=$_GET['file'];
		//**
		$phpfm['full path']=realpath($_SESSION['path']."/".$phpfm['file']);
		//**
		$phpfm['pathinfo']=pathinfo($phpfm['full path']);
		$phpfm['extension']=$phpfm['pathinfo']['extension'];
		//**
		$phpfm['filesize']=filesize($phpfm['full path']);
		//**
		if($phpfm['filesize']<=1024) $phpfm['filesize']=$phpfm['filesize']." ����";
		else if($phpfm['filesize']>1024 && $phpfm['filesize']<=(1024*1024)) $phpfm['filesize']=round(($phpfm['filesize']/1024),1)." �� (".$phpfm['filesize']." ����)";
		else $phpfm['filesize']=round(($phpfm['filesize']/(1024*1024)),1)." �� (".$phpfm['filesize']." ����)";
		//**
		@$phpfm['perms']=display_perms(fileperms($phpfm['full path']));
		//**
		$phpfm['vals']=explode_by_size(substr($phpfm['perms'],1,9),(1/(1024*1024)));
		?>
		<html>
		<head>
		<title>��������: <?=$phpfm['file']?></title>
		<style type='text/css'>
		<!--
		BODY,table { background-color:#F7F6F3; color:black; font-family: Verdana; font-size: 8pt; }
		TD { text-align:left; vertical-align:top; }
		.comment2 { font-family: Verdana; font-size: 7pt; color:black }
		input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
		-->
		</style>
		</head>
		<body onload='do_chmod("owner");do_chmod("group");do_chmod("other");'>
		<table width=380 border=0 cellspacing=2 cellpadding=2 align=right style='position:absolute;top:0;left:0;'>
		<form action='index.php' target='_blank' onsubmit='window.close()' method=get>
		<tr>
		<td width=100 valign=top><img src='images/file-<?=$phpfm['extension']?>.png'></td>
		<td width=280 valign=top><input type=text name='file' value='<?=$phpfm['file']?>' style='width:280'><input type=hidden name=act value=rename><input type=hidden name='file2' value='<?=$phpfm['file']?>'><input type=submit value='�������������' style='display:none;visibility:hidden;'></td>
		</tr>
		</form>
		<tr>
		<td width=380 colspan=2><hr size=1></td>
		</tr>
		<tr>
		<td width=100 valign=top>��� �����:</td>
		<td width=280 valign=top><?if(isset($desc[$phpfm['extension']])) { echo $desc[$phpfm['extension']]; }else{ echo "�������� �����������"; }?></td>
		</tr>
		<tr>
		<td width=380 colspan=2><hr size=1></td>
		</tr>
		<tr>
		<td width=100 valign=top>����������:</td>
		<td width=280 valign=top><?=$_SESSION['path']?></td>
		</tr>
		<tr>
		<td width=100 valign=top>������:</td>
		<td width=280 valign=top><?=$phpfm['filesize']?></td>
		</tr>
		<tr>
		<td width=380 colspan=2><hr size=1></td>
		</tr>
		<tr>
		<td width=100 valign=top>������:</td>
		<td width=280 valign=top><?=date("d.m.Y �., h:i:s",@filectime($phpfm['full path']))?></td>
		</tr>
		<tr>
		<td width=100 valign=top>�������:</td>
		<td width=280 valign=top><?=date("d.m.Y �., h:i:s",@filemtime($phpfm['full path']))?></td>
		</tr>
		<tr>
		<td width=100 valign=top>������:</td>
		<td width=280 valign=top><?=date("d.m.Y �., h:i:s",@fileatime($phpfm['full path']))?></td>
		</tr>
		<tr>
		<td width=380 colspan=2><hr size=1></td>
		</tr>
		<tr>
		<td colspan=2>
		<script language="JavaScript">
		<!--
		/* chmod helper, Version 1.0
		 * by Dan Kaplan <design@abledesign.com>
		 * Last Modified: May 24, 2001
		 * --------------------------------------------------------------------
		 * Inspired by 'Chmod Calculator' by Peter Crouch:
		 * http://wsabstract.com/script/script2/chmodcal.shtml
		 *
		 * USE THIS LIBRARY AT YOUR OWN RISK; no warranties are expressed or
		 * implied. You may modify the file however you see fit, so long as
		 * you retain this header information and any credits to other sources
		 * throughout the file.  If you make any modifications or improvements,
		 * please send them via email to Dan Kaplan <design@abledesign.com>.
		 * --------------------------------------------------------------------
		*/
		
		function do_chmod(user) {
		var field4 = user + "4";
		var field2 = user + "2";
		var field1 = user + "1";
		var total = "t_" + user;
		var symbolic = "sym_" + user;
		var number = 0;
		var sym_string = "";
	
		if (document.chmod[field4].checked == true) { number += 4; }
		if (document.chmod[field2].checked == true) { number += 2; }
		if (document.chmod[field1].checked == true) { number += 1; }
	
		if (document.chmod[field4].checked == true) {
			sym_string += "r";
		} else {
			sym_string += "-";
		}
		if (document.chmod[field2].checked == true) {
			sym_string += "w";
		} else {
			sym_string += "-";
		}
		if (document.chmod[field1].checked == true) {
			sym_string += "x";
		} else {
			sym_string += "-";
		}
	
		if (number == 0) { number = "0"; }
		document.chmod[total].value = number;
		document.chmod[symbolic].value = sym_string;	
		document.chmod.t_total.value = "0" + document.chmod.t_owner.value + document.chmod.t_group.value + document.chmod.t_other.value;
		
		if(!document.chmod.sym_owner.value) f1="---";
		else f1=document.chmod.sym_owner.value;
		
		if(!document.chmod.sym_group.value) f2="---";
		else f2=document.chmod.sym_group.value;
		
		if(!document.chmod.sym_other.value) f3="---";
		else f3=document.chmod.sym_other.value;
		
		document.chmod.sym_total.value = "-" + f1 + f2 + f3;
		}
		//-->
		</script>
		
		<form name="chmod" action='index.php' method=get>
		<input type=hidden name=act value=formenu>
		<input type=hidden name=subact value=chmod>
		<input type=hidden name=content value='<?=$phpfm['file']?>'>
		<input type=hidden name=type value=file>
		<table cellpadding="0" cellspacing="0" border="0" bgcolor="#03075D">
		<tr><td width="100%" valign="top">
		<table width="100%" cellpadding="5" cellspacing="2" border="0">
			<tr bgcolor="#bcbcbc">
				<td align="left"><b>�����</b></td>
				<td align="center"><b>��������</b></td>
				<td align="center"><b>������</b></td>
				<td align="center"><b>������</b></td>
				<td align="center"><b>�����</b></td>
			</tr><tr bgcolor="#dddddd">
				<td align="left" nowrap><b>������</b></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="owner4" value="4" onclick="do_chmod('owner')"<?if($phpfm['vals'][0]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="group4" value="4" onclick="do_chmod('group')"<?if($phpfm['vals'][3]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="other4" value="4" onclick="do_chmod('other')"<?if($phpfm['vals'][6]!="-") echo " checked";?>></td>
				<td bgcolor="#dddddd">&nbsp;</td>
			</tr><tr bgcolor="#dddddd">
				<td align="left" nowrap><b>������</b></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="owner2" value="2" onclick="do_chmod('owner')"<?if($phpfm['vals'][1]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="group2" value="2" onclick="do_chmod('group')"<?if($phpfm['vals'][4]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="other2" value="2" onclick="do_chmod('other')"<?if($phpfm['vals'][7]!="-") echo " checked";?>></td>
				<td bgcolor="#dddddd">&nbsp;</td>
			</tr><tr bgcolor="#dddddd">
				<td align="left" nowrap><b>����������</b></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="owner1" value="1" onclick="do_chmod('owner')"<?if($phpfm['vals'][2]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="group1" value="1" onclick="do_chmod('group')"<?if($phpfm['vals'][5]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="other1" value="1" onclick="do_chmod('other')"<?if($phpfm['vals'][8]!="-") echo " checked";?>></td>
				<td bgcolor="#dddddd">&nbsp;</td>
			</tr><tr bgcolor="#dddddd">
				<td align="right" nowrap>��������:</td>
				<td align="center"><input type="text" id="t_owner" value="0" size="1" style='width:100%'></td>
				<td align="center"><input type="text" id="t_group" value="0" size="1" style='width:100%'></td>
				<td align="center"><input type="text" id="t_other" value="0" size="1" style='width:100%'></td>
				<td align="left"><input type="text" name="t_total" value="0" size="3" style='width:100%'></td>
			</tr><tr bgcolor="#dddddd">
				<td align="right" nowrap>����������:</td>
				<td align="center"><input type="text" id="sym_owner" value="" size="3" style='width:100%'></td>
				<td align="center"><input type="text" id="sym_group" value="" size="3" style='width:100%'></td>
				<td align="center"><input type="text" id="sym_other" value="" size="3" style='width:100%'></td>
				<td align="left"><input type="text" id="sym_total" value="<?=$phpfm['perms']?>" size="10" style='width:80px;'></td>
			</tr><tr bgcolor="#dddddd"><td colspan="5" align="center">
				<font face="Arial" size="1">Provided free by <a href="http://abledesign.com/programs/" target="_blank">AbleDesign</a>, inspired by <a href="http://wsabstract.com/script/script2/chmodcal.shtml" target="_blank">Chmod Calculator</a></font>
			</td></tr>
			</table></td></tr></table>
			<div align=center><input type=submit value='������� �����'></div>
			</form>
		</td>
		</tr>
		</table>
		</body>
		</html>
<?
	}else
	{
		$phpfm['file']=$_GET['dir'];
		//**
		$phpfm['full path']=realpath($_SESSION['path']."/".$phpfm['file']);
		//**
		$phpfm['stats']=dirstats($phpfm['full path']);
		//**
		if($phpfm['stats']['size']<=1024) $phpfm['stats']['size']=$phpfm['stats']['size']." ����";
		else if($phpfm['stats']['size']>1024 && $phpfm['stats']['size']<=(1024*1024)) $phpfm['stats']['size']=round(($phpfm['stats']['size']/1024),1)." �� (".$phpfm['stats']['size']." ����)";
		else $phpfm['stats']['size']=round(($phpfm['stats']['size']/(1024*1024)),1)." �� (".$phpfm['stats']['size']." ����)";
		//**
		@$phpfm['perms']=display_perms(fileperms($phpfm['full path']));
		//**
		$phpfm['vals']=explode_by_size(substr($phpfm['perms'],1,9),(1/(1024*1024)));
		?>
		<html>
		<head>
		<title>��������: <?=$phpfm['file']?></title>
		<style type='text/css'>
		<!--
		BODY,table { background-color:#F7F6F3; color:black; font-family: Verdana; font-size: 8pt; }
		TD { text-align:left; vertical-align:top; }
		.comment2 { font-family: Verdana; font-size: 7pt; color:black }
		input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
		-->
		</style>
		</head>
		<body onload='do_chmod("owner");do_chmod("group");do_chmod("other");'>
		<table width=380 border=0 cellspacing=2 cellpadding=2 align=right style='position:absolute;top:0;left:0;'>
		<form action='index.php' target='_blank' onsubmit='window.close()' method=get>
		<tr>
		<td width=100 valign=top><img src='images/folder.png'></td>
		<td width=280 valign=top><input type=text name='file' value='<?=$phpfm['file']?>' style='width:280'><input type=hidden name=act value=rename><input type=hidden name='file2' value='<?=$phpfm['file']?>'><input type=submit value='�������������' style='display:none;visibility:hidden;'></td>
		</tr>
		</form>
		<tr>
		<td width=380 colspan=2><hr size=1></td>
		</tr>
		<tr>
		<td width=100 valign=top>���:</td>
		<td width=280 valign=top>����� � �������</td>
		</tr>
		<tr>
		<td width=100 valign=top>����������:</td>
		<td width=280 valign=top><?=$_SESSION['path']?></td>
		</tr>
		<tr>
		<td width=100 valign=top>������:</td>
		<td width=280 valign=top><?=$phpfm['stats']['size']?></td>
		</tr>
		<tr>
		<td width=100 valign=top>��������:</td>
		<td width=280 valign=top><?echo declesion($phpfm['stats']['files'],array("����","�����","������"));echo " � ";echo declesion($phpfm['stats']['dirs'],array("�����","�����","�����"));?></td>
		</tr>
		<tr>
		<td width=380 colspan=2><hr size=1></td>
		</tr>
		<tr>
		<td width=100 valign=top>�������:</td>
		<td width=280 valign=top><?=date("d.m.Y �., h:i:s",@filectime($phpfm['full path']))?></td>
		</tr>
		<tr>
		<td width=100 valign=top>��������:</td>
		<td width=280 valign=top><?=date("d.m.Y �., h:i:s",@filemtime($phpfm['full path']))?></td>
		</tr>
		<tr>
		<td width=100 valign=top>�������:</td>
		<td width=280 valign=top><?=date("d.m.Y �., h:i:s",@fileatime($phpfm['full path']))?></td>
		</tr>
		<tr>
		<td width=380 colspan=2><hr size=1></td>
		</tr>
		<tr>
		<td colspan=2>
		<script language="JavaScript">
		<!--
		/* chmod helper, Version 1.0
		 * by Dan Kaplan <design@abledesign.com>
		 * Last Modified: May 24, 2001
		 * --------------------------------------------------------------------
		 * Inspired by 'Chmod Calculator' by Peter Crouch:
		 * http://wsabstract.com/script/script2/chmodcal.shtml
		 *
		 * USE THIS LIBRARY AT YOUR OWN RISK; no warranties are expressed or
		 * implied. You may modify the file however you see fit, so long as
		 * you retain this header information and any credits to other sources
		 * throughout the file.  If you make any modifications or improvements,
		 * please send them via email to Dan Kaplan <design@abledesign.com>.
		 * --------------------------------------------------------------------
		*/
		
		function do_chmod(user) {
		var field4 = user + "4";
		var field2 = user + "2";
		var field1 = user + "1";
		var total = "t_" + user;
		var symbolic = "sym_" + user;
		var number = 0;
		var sym_string = "";
	
		if (document.chmod[field4].checked == true) { number += 4; }
		if (document.chmod[field2].checked == true) { number += 2; }
		if (document.chmod[field1].checked == true) { number += 1; }
	
		if (document.chmod[field4].checked == true) {
			sym_string += "r";
		} else {
			sym_string += "-";
		}
		if (document.chmod[field2].checked == true) {
			sym_string += "w";
		} else {
			sym_string += "-";
		}
		if (document.chmod[field1].checked == true) {
			sym_string += "x";
		} else {
			sym_string += "-";
		}
	
		if (number == 0) { number = "0"; }
		document.chmod[total].value = number;
		document.chmod[symbolic].value = sym_string;	
		document.chmod.t_total.value = "0" + document.chmod.t_owner.value + document.chmod.t_group.value + document.chmod.t_other.value;
		
		if(!document.chmod.sym_owner.value) f1="---";
		else f1=document.chmod.sym_owner.value;
		
		if(!document.chmod.sym_group.value) f2="---";
		else f2=document.chmod.sym_group.value;
		
		if(!document.chmod.sym_other.value) f3="---";
		else f3=document.chmod.sym_other.value;
		
		document.chmod.sym_total.value = "d" + f1 + f2 + f3;
		}
		//-->
		</script>
		
		<form name="chmod" action='index.php' method=get>
		<input type=hidden name=act value=formenu>
		<input type=hidden name=subact value=chmod>
		<input type=hidden name=content value='<?=$phpfm['file']?>'>
		<input type=hidden name=type value=dir>
		<table cellpadding="0" cellspacing="0" border="0" bgcolor="#03075D">
		<tr><td width="100%" valign="top">
		<table width="100%" cellpadding="5" cellspacing="2" border="0">
			<tr bgcolor="#bcbcbc">
				<td align="left"><b>�����</b></td>
				<td align="center"><b>��������</b></td>
				<td align="center"><b>������</b></td>
				<td align="center"><b>������</b></td>
				<td align="center"><b>�����</b></td>
			</tr><tr bgcolor="#dddddd">
				<td align="left" nowrap><b>������</b></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="owner4" value="4" onclick="do_chmod('owner')"<?if($phpfm['vals'][0]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="group4" value="4" onclick="do_chmod('group')"<?if($phpfm['vals'][3]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="other4" value="4" onclick="do_chmod('other')"<?if($phpfm['vals'][6]!="-") echo " checked";?>></td>
				<td bgcolor="#dddddd">&nbsp;</td>
			</tr><tr bgcolor="#dddddd">
				<td align="left" nowrap><b>������</b></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="owner2" value="2" onclick="do_chmod('owner')"<?if($phpfm['vals'][1]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="group2" value="2" onclick="do_chmod('group')"<?if($phpfm['vals'][4]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="other2" value="2" onclick="do_chmod('other')"<?if($phpfm['vals'][7]!="-") echo " checked";?>></td>
				<td bgcolor="#dddddd">&nbsp;</td>
			</tr><tr bgcolor="#dddddd">
				<td align="left" nowrap><b>����������</b></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="owner1" value="1" onclick="do_chmod('owner')"<?if($phpfm['vals'][2]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="group1" value="1" onclick="do_chmod('group')"<?if($phpfm['vals'][5]!="-") echo " checked";?>></td>
				<td align="center" bgcolor="#ffffff" valign=center style='vertical-align:center;text-align:center;'><input type="checkbox" id="other1" value="1" onclick="do_chmod('other')"<?if($phpfm['vals'][8]!="-") echo " checked";?>></td>
				<td bgcolor="#dddddd">&nbsp;</td>
			</tr><tr bgcolor="#dddddd">
				<td align="right" nowrap>��������:</td>
				<td align="center"><input type="text" id="t_owner" value="0" size="1" style='width:100%'></td>
				<td align="center"><input type="text" id="t_group" value="0" size="1" style='width:100%'></td>
				<td align="center"><input type="text" id="t_other" value="0" size="1" style='width:100%'></td>
				<td align="left"><input type="text" name="t_total" value="0" size="3" style='width:100%'></td>
			</tr><tr bgcolor="#dddddd">
				<td align="right" nowrap>����������:</td>
				<td align="center"><input type="text" id="sym_owner" value="" size="3" style='width:100%'></td>
				<td align="center"><input type="text" id="sym_group" value="" size="3" style='width:100%'></td>
				<td align="center"><input type="text" id="sym_other" value="" size="3" style='width:100%'></td>
				<td align="left"><input type="text" id="sym_total" value="<?=$phpfm['perms']?>" size="10" style='width:80px;'></td>
			</tr><tr bgcolor="#dddddd"><td colspan="5" align="center">
				<font face="Arial" size="1">Provided free by <a href="http://abledesign.com/programs/" target="_blank">AbleDesign</a>, inspired by <a href="http://wsabstract.com/script/script2/chmodcal.shtml" target="_blank">Chmod Calculator</a></font>
			</td></tr>
			</table></td></tr></table>
			<div align=center><input type=submit value='������� �����'></div>
			</form>
		</td>
		</tr>
		</table>
		</body>
		</html>
<?
	}
	die();
	exit;
	break;
case "edit":
	if($phpfm['user-space-left'] <= 0)
	{
		echo "<script language=JavaScript>";
		echo "alert('�� ������������� ����� ��������� ������������.\\n��� ������� ����������. ������� ������ ����� � ���������� ��� ���.');";
		echo "window.close();</script>";
		break;
	}
	//**
	$phpfm['file']=$_GET['file'];
	//**
	$phpfm['full path']=realpath($_SESSION['path']."/".$phpfm['file']);
	//**
	//������ �� �������� ������ �������������
	//**
	CheckAccess($phpfm['full path']);
	//**
	$phpfm['extension']=pathinfo($phpfm['full path']);
	//**
	switch(strtolower(@$phpfm['extension']['extension']))
	{
		case "jpg";
		case "png":
		case "jpeg":
		case "gif":
		case "jpe":
		Header("Location: ".$phpfm['php_self']."?act=viewpicture&file=".rawurlencode($phpfm['file']));
		die();
		exit;
		break;
	}
	//**
	if(filesize($phpfm['full path'])>200*1024) $phpfm['buffer']="������ ������� ������� ���� ��� ��������������:\n��������, ��� ����� ��� ��������� ����";
	else if(!$phpfm['buffer']=@file2string($phpfm['full path'])) $phpfm['buffer']="���� ���� ������, ���� � ��� ��� ���� �� ������ ����� �����";
	//**
	$phpfm['buffer']=stripslashes(addslashes(htmlspecialchars($phpfm['buffer'])));
	//**
	$phpfm['readable']=is_readable($phpfm['full path']);
	$phpfm['writable']=is_writable($phpfm['full path']);
	//**
	$phpfm['str readable']=($phpfm['readable']) ? ("<span style='color:green'>���� ����� �� ������</span>") : ("<span style='color:red'>��� ���� �� ������</span>");
	$phpfm['str writable']=($phpfm['writable']) ? ("<span style='color:green'>���� ����� �� ������</span>") : ("<span style='color:red'>��� ���� �� ������</span>");
	?>
	<html>
	<head>
	<title> <?=$phpfm['file']?> - PhpFM - �������� ������ </title>
	<style type='text/css'>
	<!--
	BODY,table { background-color:#F7F6F3; color:black; font-family: Verdana; font-size: 8pt; }
	TD { text-align:left; vertical-align:top; }
	.comment2 { font-family: Verdana; font-size: 7pt; color:black }
	#multitext { background-color: #F7F6F3; color:#000000; font-family:Courier new, Verdana, Arial; font-size:8pt }
	.halfbutton {border-style : outset;width : 95px; height:20 px;}
	input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
	A {COLOR: black; TEXT-DECORATION: none; font-family: Tahoma; font-size: 8pt; cursor: default; display: block; border: 0px solid #000000;}
	A:hover {COLOR: white; TEXT-DECORATION: none; background-color: #00126C;}
	-->
	</style>
	<script language=javascript>
	<!--
	var submitted = 0;
	var changed = 0;
	//-->
	</script>
	</head>
	<body onbeforeunload="if(!submitted && changed) { return '���� ��� �������, ���������� �������� ����?'; }">
	<h3 align=center style='align:center'> �������������� ����� <?=$phpfm['file']?> </h3> <div align=center style='align:center'>( <?=$phpfm['str readable']?> | <?=$phpfm['str writable']?> )<br><a href='index.php?act=edit-conf&subact=open&file=<?=rawurlencode($phpfm['file'])?>' target=_blank>��������������� � ������� config-editor</a></div> 
	<br><br><form action='index.php?act=apply' name='editfile' id='editfile' method=post onsubmit="submitted=1;">
	<input type=hidden name='file' value='<?=$phpfm['file']?>'>
	<textarea id=multitext name='content' style='width:600;height:310' wrap=off<?if(!$phpfm['writable']) echo " readonly";?> onkeydown='changed=1;'><?=$phpfm['buffer']?></textarea>
	<br><div align=center style='align:center'><input type=submit value='���������' class=halfbutton<?if(!$phpfm['writable']) echo " disabled";?>>&nbsp;<input type=reset value='��������' class=halfbutton></div>
	</form>
	</body></html>
<?	//**
	die();
	exit;
	break;
case "viewpicture":
	$phpfm['file']=$_GET['file'];
	//**
	$phpfm['full path']=realpath($_SESSION['path']."/".$phpfm['file']);
	//���� ����� �������� ���������� �����������
	CheckAccess($phpfm['full path']);
	//**
	$phpfm['extension']=pathinfo($phpfm['full path']);
	//**
	switch(strtolower($phpfm['extension']['extension']))
	{
	case "jpg":
	case "jpeg":
	case "jpe":
		Header("Content-type: image/jpeg");
		break;
	case "gif":
		Header("Content-type: image/gif");
		break;
	case "png":
		Header("Content-type: image/png");
		break;
	}
	readfile($phpfm['full path']);
	//**
	die();
	exit;
	break;
case "thumb":
	@$phpfm['file']=$_GET['file'];
	//**
	$phpfm['full path']=realpath($_SESSION['path']."/".$phpfm['file']);
	//���� ����� �������� ���������� �����������
	CheckAccess($phpfm['full path']);
	//**
	$phpfm['extension']=pathinfo($phpfm['full path']);
	//**
	switch(@strtolower($phpfm['extension']['extension']))
	{
	case "jpg":
	case "jpeg":
	case "jpe":
	case "gif":
	case "png":
		CreateThumb($phpfm['full path']);
		//**
		break;
	default:
		Header("Location: images/file-".$phpfm['extension']['extension'].".png");
		break;
	}
	break;
case "apply":
	if($phpfm['user-space-left'] <= 0)
	{
		echo "<script language=JavaScript>";
		echo "alert('�� ������������� ����� ��������� ������������.\\n��� ������� ����������. ������� ������ ����� � ���������� ��� ���.');";
		echo ";</script>";
		break;
	}
	//**
	$phpfm['file']=$_POST['file'];
	$phpfm['full path']=$_SESSION['path']."/".$phpfm['file'];
	//**
	$phpfm['content']=$_POST['content'];
	//**
	if(get_magic_quotes_runtime() || get_magic_quotes_gpc()) $phpfm['content']=stripslashes($phpfm['content']);
	//**
	//����������� �������������
	//**
	EditDiskSpace(strlen($phpfm['content'])-file2string($phpfm['full path']));
	CheckAccess($phpfm['full path']);
	CheckByFilter($phpfm['content']);
	//**
	echo "<html><head><script language=javascript>";
	if(!@writefile($phpfm['full path'],$phpfm['content'])) echo "alert(\"���� '".$phpfm['file']."' �� ����� ���� ��������������. �������: ".$php_errormsg."\");";
	echo "window.close();</script></head></html>";
	//**
	die();
	exit;
	break;
case "edit-conf":
	if($phpfm['user-space-left'] <= 0)
	{
		echo "<script language=JavaScript>";
		echo "alert('�� ������������� ����� ��������� ������������.\\n��� ������� ����������. ������� ������ ����� � ���������� ��� ���.');";
		echo "window.close();</script>";
		break;
	}
	//**
	$phpfm['file']=$_GET['file'];
	//**
	$phpfm['full path']=realpath($_SESSION['path']."/".$phpfm['file']);
	//**
	//������ �� �������� ������ �������������
	//**
	CheckAccess($phpfm['full path']);
	//**
	?>
	<html>
	<head>
	<title> <?=$phpfm['file']?> - PhpFM - config editor </title>
	<style type='text/css'>
	<!--
	BODY,table { background-color:#F7F6F3; color:black; font-family: Verdana; font-size: 8pt; }
	TD { text-align:left; vertical-align:top; }
	.comment2 { font-family: Verdana; font-size: 7pt; color:black }
	#multitext { background-color: #F7F6F3; color:#000000; font-family:Courier new, Verdana, Arial; font-size:8pt }
	.halfbutton {border-style : outset;width : 95px; height:20 px;}
	input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
	A {COLOR: black; TEXT-DECORATION: none; font-family: Tahoma; font-size: 8pt; cursor: default; display: block; border: 0px solid #000000;}
	A:hover {COLOR: white; TEXT-DECORATION: none; background-color: #00126C;}
	-->
	</style>
	</head>
	<body>
	<h3 align=center style='align:center'>PhpFM config editor</h3>
	<h4 align=center style='align:center'><a href='javascript:window.close()'>�������</a></h4>
	<?
	switch($_GET['subact'])
	{
	case "open":
		?>
		<form action='index.php?act=edit-conf&subact=apply&file=<?=$phpfm['file']?>' method=post>
		<table align=center cellspacing=2 cellpadding=2 border=1 width='75%'>
		<tr>
		<td colspan=2 style='text-align:center'><b>������������</b></td>
<?		
		$phpfm['content']=file2string($phpfm['full path']);
		//**
		//���-���������
		//**
		$phpfm['content']=str_replace(array("<?php","<?","?>","//**"),"",$phpfm['content']);
		$phpfm['content']=preg_replace("/\/\*(.*)\*\//sU", "\n", $phpfm['content']);
		$phpfm['content']=preg_replace("/(\r?\n)+/s","\n",$phpfm['content']);
		//**
		$phpfm['part']=explode(";",$phpfm['content']);
		
		foreach($phpfm['part'] as $key=>$value)
		{
			$phpfm['part'][$key]=explode("\n",$value);
			//**
			foreach($phpfm['part'][$key] as $kay=>$velue) if(empty($velue) || trim($velue)=="") unset($phpfm['part'][$key][$kay]);
		}
		//**
		//��������� ����� - ��� ���� ���������, "�������������" ������
		//**
		foreach($phpfm['part'] as $key=>$value)
		{
			foreach($value as $kay=>$velue)
			{
				$velue=trim($velue);
				//**
				if(trim(substr($velue,0,2))=="//")
				{
					if(!isset($phpfm['part'][$key-1]['desc'])) $phpfm['part'][$key-1]['desc']=trim(substr($velue,2,strlen($velue)));
					else $phpfm['part'][$key-1]['desc'].="<br>".trim(substr($velue,2,strlen($velue)));
				}else if(trim(substr($velue,0,1))=="#") #����� ����������� ���� �����������
				{
					if(!isset($phpfm['part'][$key-1]['desc'])) $phpfm['part'][$key-1]['desc']=trim(substr($velue,1,strlen($velue)));
					else $phpfm['part'][$key-1]['desc'].="<br>".trim(substr($velue,1,strlen($velue)));
				}else
				{
					if(!isset($phpfm['part'][$key]['exp'])) $phpfm['part'][$key]['exp']=$velue;
					else $phpfm['part'][$key]['exp'].="\n".$velue;
				}
				//**
				unset($phpfm['part'][$key][$kay]);
			}
		}
		//**
		//����� �� ������ ��������� � ������-�� �������
		//**
		?>
		</tr>
<?		
		if(isset($phpfm['part'][-1]['desc']))
		{
			?>
			<tr>
			<td colspan=2 style='text-align:center'><b><?=$phpfm['part'][-1]['desc']?></b></td>
			<input type=hidden name='desc' value='<?=str_replace("'","char_apostrof",$phpfm['part'][-1]['desc'])?>'>
			</tr>
<?			unset($phpfm['part'][-1]);
		}
		//**
		$phpfm['scounter']=0;
		//**
		foreach($phpfm['part'] as $key=>$value)
		{
			if(isset($value['desc']) && !isset($value['exp']))
			{
				?>
				<tr>
				<td colspan=2 style='text-align:center'><b><?=$value['desc']?></b></td>
				</tr>
<?				continue;
			}else if(isset($value['exp']) && !isset($value['desc']))
			{
				$phpfm['spart']=explode("=",$value['exp'],2);
				//**
				$value['desc']=trim($phpfm['spart'][0]);
			}else if(!isset($value['exp']) && !isset($value['desc']))
			{
				continue; //�� ���� ���� ������ ������
			}else
			{
				$phpfm['spart']=explode("=",$value['exp'],2);
			}
			//**
			$phpfm['spart'][0]=trim($phpfm['spart'][0]);
			$phpfm['spart'][1]=trim($phpfm['spart'][1]);
			//**
			if(substr($phpfm['spart'][1],0,1)=="'" && substr($phpfm['spart'][1],strlen($phpfm['spart'][1])-1,1)=="'")
			{
				//������� �������� �����������, ���� �� �����-�� ���� ������ ���������� � ��� ��� :)
				$phpfm['str_type']="string1";
				$phpfm['val']=substr($phpfm['spart'][1],1,strlen($phpfm['spart'][1])-2);
			}else if(substr($phpfm['spart'][1],0,1)=='"' && substr($phpfm['spart'][1],strlen($phpfm['spart'][1])-1,1)=='"')
			{
				$phpfm['str_type']="string2";
				$phpfm['val']=substr($phpfm['spart'][1],1,strlen($phpfm['spart'][1])-2);
			}else if(is_numeric($phpfm['spart'][1]))
			{
				$phpfm['str_type']="integer";
				$phpfm['val']=$phpfm['spart'][1];
			}else if(substr($phpfm['spart'][1],0,5)=="array")
			{
				$phpfm['str_type']="array";
				eval("\$phpfm['val']=".$phpfm['spart'][1].";"); //����� ������ ������ ������� ������ � ��������� ���, ��� �������� ��� PHP :)
			}else if(strtolower($phpfm['spart'][1])=="true" or strtolower($phpfm['spart'][1])=="false")
			{
				$phpfm['str_type']="bool";
				$phpfm['val']=(strtolower($phpfm['spart'][1])=="true") ? (true) : (false);
			}else
			{
				$phpfm['str_type']="unknown";
				$phpfm['val']=$phpfm['spart'][1];
			}
			//**
			$phpfm['scounter']++;
			?>
			<tr>
			<td><?=$value['desc']?></td>
			<td style='text-align:right;' width=400>
			<input type=hidden name='<?=$phpfm['scounter']?>' value='name=<?=str_replace(array("'","="),array("char_apostrof","char_ravno"),$phpfm['spart'][0])?>;type=<?=$phpfm['str_type']?>;desc=<?=str_replace("'","char_apostrof",$value['desc'])?>'>
<?			
			switch($phpfm['str_type'])
			{
			case "string1":
				//chr(92) - ��� ���� (\), ������ � ������� 2 ����� ������ ���������� �������� �������������
				?><input type=text name='<?=$phpfm['scounter']?>_input' value='<?=str_replace(array("\'",chr(92).chr(92)),array("&#39;",chr(92)),htmlspecialchars($phpfm['val']))?>' style='width:400'>
<?				break;
			case "string2":
				?><input type=text name='<?=$phpfm['scounter']?>_input' value='<?=str_replace(array(chr(92).'&quot;',chr(92).chr(92),'\$'),array('&quot;',chr(92),'$'),htmlspecialchars($phpfm['val']))?>' style='width:400'>
<?				break;
			case "integer":
				?><input type=text name='<?=$phpfm['scounter']?>_input' value='<?=$phpfm['val']?>' style='width:400'>
<?				break;
			case "bool":
				?><select name='<?=$phpfm['scounter']?>_input' style='width:400'><option value='yes'<?if($phpfm['val']==true) echo " selected";?>>��</option><option value='no'<?if($phpfm['val']==false) echo " selected";?>>���</option></select>
<?				break;
			case "array":
				//������ - ��������� �����...
				//**
				$phpfm['sscounter']=0;
				//**
				foreach($phpfm['val'] as $kiy=>$vylue)
				{
					$phpfm['sscounter']++;
					?><input type=text name='<?=$phpfm['scounter']?>_input[name_<?=$phpfm['sscounter']?>]' value='<?=str_replace("'","&#39;",htmlspecialchars($kiy))?>' style='width:100'>&nbsp;=>&nbsp;
					<input type=text name='<?=$phpfm['scounter']?>_input[value_<?=$phpfm['sscounter']?>]' value='<?=str_replace("'","&#39;",htmlspecialchars($vylue))?>' style='width:270'><br>
<?				}
				//**
				$phpfm['sscounter']++;
				?><hr><div align=center style='align:center'><b>�������� ��������</b></div><hr><input type=text name='<?=$phpfm['scounter']?>_input[name_<?=$phpfm['sscounter']?>]' value='' style='width:100'>&nbsp;=>&nbsp;
					<input type=text name='<?=$phpfm['scounter']?>_input[value_<?=$phpfm['sscounter']?>]' value='' style='width:270'><br><?$phpfm['sscounter']++;?>
					<input type=text name='<?=$phpfm['scounter']?>_input[name_<?=$phpfm['sscounter']?>]' value='' style='width:100'>&nbsp;=>&nbsp;
					<input type=text name='<?=$phpfm['scounter']?>_input[value_<?=$phpfm['sscounter']?>]' value='' style='width:270'><br><?$phpfm['sscounter']++;?>
					<input type=text name='<?=$phpfm['scounter']?>_input[name_<?=$phpfm['sscounter']?>]' value='' style='width:100'>&nbsp;=>&nbsp;
					<input type=text name='<?=$phpfm['scounter']?>_input[value_<?=$phpfm['sscounter']?>]' value='' style='width:270'>
					<input type=hidden name='<?=$phpfm['scounter']?>_counter' value='<?=$phpfm['sscounter']?>'>
<?				break;
			case "unknown":
				?>
				��� ���������� ���������� � � ��������� ��� ������� �� �����
<?				break;
				?>
<?			} //switch
?>
			</td>
			</tr>
<?		} //foreach
?>
		</table>
		<div align=center style='align:center'><input type=submit value='���������' class=halfbutton>&nbsp;<input type=reset value='��������' class=halfbutton></div>
<?		break;
	case "apply":
		//**
		$phpfm['begin']="<?\n";
		//**
		if(isset($_POST['desc']))
		{
			if(get_magic_quotes_runtime() || get_magic_quotes_gpc()) $_POST['desc']=stripslashes($_POST['desc']);
			$_POST['desc']=str_replace("char_apostrof","'",$_POST['desc']);
			//**
			$phpfm['begin'].="//".$_POST['desc']."\n\n";
			unset($_POST['desc']);
		}
		//**
		//���������
		//**
		foreach($_POST as $key=>$value)
		{
			if(!is_numeric($key)) //�� ���� ���� ��� �� ��������, � ������
			{
				if(get_magic_quotes_runtime() || get_magic_quotes_gpc() && !is_array($value)) $value=stripslashes($value);
				$phpfm['other'][$key]=$value;
			}else
			{
				$phpfm['part']=explode(";",$value);
				//**
				foreach($phpfm['part'] as $kay=>$velue)
				{
					$phpfm['part']=explode("=",$velue);
					//**
					if(get_magic_quotes_runtime() || get_magic_quotes_gpc()) $phpfm['part'][1]=stripslashes($phpfm['part'][1]);
					$phpfm['descs'][$key][$phpfm['part'][0]]=str_replace(array("char_apostrof","char_ravno"),array("'","="),$phpfm['part'][1]);
				}
			}
		}
		//**
		$phpfm['middle']="";
		//**
		//�������������� �����
		//**
		foreach($phpfm['descs'] as $key=>$value)
		{
			$phpfm['content']=$phpfm['other'][$key."_input"];
			//**
			switch($value['type'])
			{
			case "string1":
				$phpfm['middle'].="\n".$value['name']."='".str_replace(array("'",chr(92)),array("\'",chr(92).chr(92)),$phpfm['content'])."'; //".$value['desc'];
				break;
			case "string2":
				$phpfm['middle'].="\n".$value['name'].'="'.str_replace(array('"',chr(92),'$'),array('\"',chr(92).chr(92),'\$'),$phpfm['content']).'"; //'.$value['desc'];
				break;
			case "integer":
				$phpfm['middle'].="\n".$value['name']."=".intval($phpfm['content'])."; //".$value['desc'];
				break;
			case "bool":
				$phpfm['tmpvar']=($phpfm['content']=="yes") ? ("true") : ("false");
				$phpfm['middle'].="\n".$value['name']."=".$phpfm['tmpvar']."; //".$value['desc'];
				break;
			case "array": //� ��� � ��������� �� ������� :)
				$phpfm['begin_array']="\n".$value['name']."=array\n(";
				$phpfm['tmpvar']=array();
				//**
				foreach($phpfm['content'] as $kay=>$velue)
				{
					if(substr($kay,0,5)=="name_") $phpfm['tmpvar_name']=$velue;
					else if(substr($kay,0,6)=="value_") $phpfm['tmpvar'][$phpfm['tmpvar_name']]=$velue;
				}
				//**
				foreach($phpfm['tmpvar'] as $kay=>$velue)
				{
					if(get_magic_quotes_runtime() || get_magic_quotes_gpc()) { $kay=stripslashes($kay); $velue=stripslashes($velue); }
					//**
					if(!empty($velue) && trim($velue)!="") $phpfm['begin_array'].="\n\t'".str_replace(array(chr(92),"'"), array(chr(92).chr(92),"\'"),$kay)."'\t=>\t'".str_replace(array("'",chr(92)), array("\'",chr(92).chr(92)),$velue)."',";
				}
				//**
				$phpfm['begin_array'].="\n); //".$value['desc'];
				$phpfm['middle'].=$phpfm['begin_array'];
				//**
				break;
			}
		}
		//**
		$phpfm['end']="\n/* edited by Config Editor (in PhpFM) */\n?>";
		//**
		$phpfm['2write']=$phpfm['begin'].$phpfm['middle'].$phpfm['end'];
		if(writefile($phpfm['full path'], $phpfm['2write'])) echo "��������� � ������������ ������� ��������.<br><a href='".$phpfm['php_self']."?act=edit-conf&subact=open&file=".$phpfm['file']."'>�����</a>";
		else echo "��������� � ������������ �� ����� ���� �������. ��������� ����� ����� (������ ���� 0666).<br><a href='".$phpfm['php_self']."?act=edit-conf&subact=open&file=".$phpfm['file']."'>��������� �����</a>";
		break;
	}
	?>
	</body></html>
<?	die();
	exit;
	break;
case "copy":
	if($phpfm['user-space-left'] <= 0)
	{
		echo "<script language=JavaScript>";
		echo "alert('�� ������������� ����� ��������� ������������.\\n��� ������� ����������. ������� ������ ����� � ���������� ��� ���.');";
		echo ";</script>";
		break;
	}
	if(isset($_GET['file'])) $_SESSION['copied_type']="file";
	else if(isset($_GET['dir'])) $_SESSION['copied_type']="dir";
	else $_SESSION['copied_type']='all';
	//**
	$_SESSION['copied_name']=$_GET[$_SESSION['copied_type']];
	//**
	if($_SESSION['copied_type']!='all')
	{
		$_SESSION['copied_full_name']=realpath($_SESSION['path']."/".$_SESSION['copied_name']);
		//���� ����������� ���� �� ����� :)
		CheckAccess(realpath($_SESSION['path']."/".$_SESSION['copied_name']));
	}else
	{
		$phpfm['parts']=explode(':',$_SESSION['copied_name']);
		$_SESSION['copied_full_name']=array();
		foreach($phpfm['parts'] as $k=>$v)
		{	
			$_SESSION['copied_full_name'][$k]=realpath($_SESSION['path']."/".$v);
			//���� ����������� ���� �� ����� :)
			CheckAccess(realpath($_SESSION['path']."/".$v));
		}
	}
	//**
	$_SESSION['copied']=true;
	break;
case "cancel_copy":
	unset($_SESSION['copied']);
	break;
case "paste":
	echo "<script language=JavaScript>";
	if($phpfm['user-space-left'] <= 0)
	{
		echo "alert('�� ������������� ����� ��������� ������������.\\n��� ������� ����������. ������� ������ ����� � ���������� ��� ���.');";
		echo ";</script>";
		break;
	}
	//**
	if($_SESSION['copied_type']=="dir")
	{
		CheckAccess(realpath($_SESSION['path']."/".$_SESSION['copied_name']));
		//**
		if(!@copydir($_SESSION['copied_full_name'],$_SESSION['path']."/".$_SESSION['copied_name'])) echo "alert('����������� ����� �� �������.\\n���������, ���� �� ����� �� ������ � ������ � ����� ������ ���� �����.')";
		//����������� �������������
		EditDiskSpace(dirsize($_SESSION['path']."/".$_SESSION['copied_name']));
	}else if($_SESSION['copied_type']=='file')
	{
		CheckAccess(realpath($_SESSION['path']."/".$_SESSION['copied_name']));
		//**
		if(@copy($_SESSION['copied_full_name'],$_SESSION['path']."/".$_SESSION['copied_name'])) echo "alert('�� ������� ����������� ����: ".$php_errormsg."')";
		//����������� �������������
		EditDiskSpace(filesize($_SESSION['path']."/".$_SESSION['copied_name']));
	}else
	{
		$phpfm['parts']=explode(':',$_SESSION['copied_name']);
		$phpfm['success']=true;
		foreach($phpfm['parts'] as $k=>$v)
		{
			if(empty($v)) continue;
			//echo 'alert("'.$v.'");';
			CheckAccess(realpath($_SESSION['path']."/".$v));
			//**
			if(copyall($_SESSION['copied_full_name'][$k],$_SESSION['path']."/".$v) && $phpfm['success']); 
			else $phpfm['success']=false;//����������� �������������
			EditDiskSpace(unisize($_SESSION['path']."/".$v,false,false,true));
		}
		if(!$phpfm['success']) echo "alert('����������� �� �������.\\n��������, ��������, � ������� �� ���������� �����, �� ����� ���� �� ������.\\n����� ����� ���������� �������� � PHP, ���������� � ������ safe mode, �� ������ ����� ������� �� ���������')";
	}
	//**
	unset($_SESSION["copied"]);
	//**
	echo ";</script>";
	break;
case "formenu":
	switch($_GET['subact'])
	{
	case "mkdir":
		$_GET['dir']=str_replace(array('..',':','\\','/'),'',$_GET['dir']);
		$phpfm['tomk']=$_SESSION['path']."/".$_GET['dir'];
		//CheckAccess($_SESSION['path']);
		//��� ��� ����������� � ������
		//**
		if(@mkdir($phpfm['tomk'],0777)) { @chmod($phpfm['tomk'],0777);  }
		else { echo "<script language=javascript>alert('"; echo "������� ����� ".$phpfm['tomk']." �� �������: ".$php_errormsg; echo "');</script>"; }
		//**
		break;
	case "mkfile":
		$_GET['file']=str_replace(array('..',':','\\','/'),'',$_GET['file']);
		$phpfm['tomk']=($_SESSION['path']."/".$_GET['file']);
		//CheckAccess($_SESSION['path']);
		//**
		if(!@mkfile($phpfm['tomk'])) { echo "<script language=javascript>alert('"; echo "������� ���� �� �������: ".$php_errormsg; echo "');</script>"; }
		break;
	case "chmod":
		$phpfm['content']=$_GET['content'];
		$phpfm['mod']=intval($_GET['t_total']);
		//**
		$phpfm['full path']=realpath($_SESSION['path']."/".$phpfm['content']);
		//**
		//���� chmod �������� ��� �� ��� ����������...
		//**
		CheckAccess($phpfm['full path']);
		//**
		echo "<script language=javascript>alert('";
		//**
		if($_GET['type']=="dir")
		{
			if(@chmoddir($phpfm['full path'], $phpfm['mod'])) echo "����� \'".$phpfm['content']."\' ������� ��������";
			else echo "����� ����� \'".$phpfm['content']."\' �� ����� ���� ��������. ��������, Windows-�������, ��� ������� �� �����-�� �������� �������� chmod.";
		}else
		{
			if(@chmod($phpfm['full path'], $phpfm['mod'])) echo "����� \'".$phpfm['content']."\' ������� ��������";
			else echo "����� ����� ������� �� �������: ".$php_errormsg;
		}
		//**
		echo "');window.close();</script>";
		break;
	}
	break;
case "info":
	echo "<html><head></head><body>
	<style type='text/css'>
	<!--
	BODY { background-color: #D1DBF6; color:black; font-family: Tahoma; font-size: 11px; }
	-->
	</style>
	";
	
	if(isset($_GET['file']))
	{
		$_GET['file']=trim(str_replace('&nbsp;','',strip_tags($_GET['file'])));
		//echo '<pre>.'.htmlspecialchars($_GET['file']).'</pre>';
		//**
		$phpfm['fullpath']=realpath($_SESSION['path'].'/'.$_GET['file']);
		CheckAccess($phpfm['fullpath']);
		if(is_file($phpfm['fullpath']))
		{
			$phpfm['pathinfo']=pathinfo($phpfm['fullpath']);
			@$phpfm['ext']=strtolower($phpfm['pathinfo']['extension']);
			switch($phpfm['ext'])
			{
			case "jpeg":
			case "jpg":
			case "jpe":
			case "gif":
			case "png":
				$phpfm['im']=getimagesize($phpfm['fullpath']);
				echo '<table width=160 height=120 border=0 cellspacing=0 cellpadding=0 align=center><tr height=120><td style="vertical-align:middle; align:center; text-align:center;" width=160><img src="index.php?act=thumb&file='.$_GET['file'].'&info"></td></tr></table>';
				echo '<br><nobr><b>'.$_GET['file'].'</b></nobr>';
				echo '<br>'.(isset($desc[$phpfm['ext']]) ? $desc[$phpfm['ext']] : '���� "'.$phpfm['ext'].'"');
				echo '<br><br>�������: '.$phpfm['im'][0].' x '.$phpfm['im'][1];
				echo '<br><br>������: '.unisize($phpfm['fullpath']);
				echo '<br><br>�������: '.date('d.m.Y �, h:i:s',filemtime($phpfm['fullpath']));
				$h=250;
				break;
			default:
				echo '<nobr><b>'.$_GET['file'].'</b></nobr>';
				echo '<br>'.(isset($desc[$phpfm['ext']]) ? $desc[$phpfm['ext']] : '���� "'.$phpfm['ext'].'"');
				echo '<br><br>�������: '.date('d.m.Y �, h:i:s',filemtime($phpfm['fullpath']));
				echo '<br><br>������: '.unisize($phpfm['fullpath']);
				$h=80;
				break;
			}
		}else
		{
			echo '<nobr><b>'.$_GET['file'].'</b></nobr>';
			echo "<br>����� � �������";
			echo '<br><br>��������: '.date('d.m.Y �, h:i:s',filemtime($phpfm['fullpath']));
			if($_SESSION['show_dirsize']) echo '<br><br>������: '.unisize($phpfm['fullpath']);
			$h=60+($_SESSION['show_dirsize'] ? 20 : 0);
		}
	}else
	{
		echo '<b>PhpFM</b><br>�������� ��������';
		if(!empty($_SESSION['logined']))
		{
			echo '<br><br>��������: '.unisize('','',disk_free_space($_SESSION['path']));
			echo '<br><br>������ �����: '.unisize('','',disk_total_space($_SESSION['path']));
		}
		$h=80;
	}
	$timer->stop();
	echo "\n\n<!-- ".$timer->elapsed()." -->";
	echo '<script language="javascript"><!--
	parent.document.getElementById("info").height='.$h.';
	//-->
	</script>';
	echo "\n\n</body></html>";
	die();
	break;
case "search":
	if(isset($_POST['string']) && (get_magic_quotes_runtime() || get_magic_quotes_gpc())) $_POST['string']=stripslashes($_POST['string']);
	?>
	<html>
	<head>
	<title> PhpFM - ����� </title>
	<style type='text/css'>
	<!--
	BODY,table { background-color:#F7F6F3; color:black; font-family: Verdana; font-size: 8pt; }
	TD { text-align:left; vertical-align:top; }
	.comment { font-family: Tahoma; font-size: 8pt; color:black }
	A.black2 {COLOR: black; TEXT-DECORATION: none; font-family: Tahoma; font-size: 8pt; }
	#multitext { background-color: #F7F6F3; color:#000000; font-family:Courier new, Verdana, Arial; font-size:8pt }
	.halfbutton {border-style : outset;width : 150px; height:20 px;}
	input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
	A {COLOR: black; TEXT-DECORATION: none; font-family: Tahoma; font-size: 8pt; cursor: default; border: 0px solid #000000;}
	A:hover {COLOR: gray;}
	-->
	</style>
	</head>
	<body>
	<h3 align=center style='align:center'> PhpFM - ����� ������ � ����� � ������� ���������� </h3>
	<form action='index.php?act=search&apply' method=post>
	<table width=500 border=0 cellpadding=3>
	<tr>
	<td width=150>������ �:</td>
	<td width=350><select name='by' class=halfbutton><option value=1<?if(isset($_POST['by']) && $_POST['by']==1) echo " selected";?>>������ � ������</option><option value=2<?if(isset($_POST['by']) && $_POST['by']==2) echo " selected";?>>������</option><option value=3<?if(isset($_POST['by']) && $_POST['by']==3) echo " selected";?>>������</option></select></td>
	</tr>
	<tr>
	<td width=150>���������� ��������� (PRCE):</td>
	<td width=350><select name='regs' class=halfbutton><option value='0'<?if(isset($_POST['regs']) && $_POST['regs']==0) echo " selected";?>>���</option><option value=1<?if(isset($_POST['regs']) && $_POST['regs']==1) echo " selected";?>>��</option></select></td>
	</tr>
	<tr>
	<td width=500 colspan=2><input type=checkbox name='recursive' id='recursive' value=1<?if(isset($_GET['apply']) && isset($_POST['recursive'])) echo " checked";elseif(!isset($_GET['apply'])) echo " checked";?>> <label for=recursive>������ �� ��������� ������</label></td>
	</tr>
	<tr>
	<td width=150>����� ����� ����� ��� ��� ����� �������:</td>
	<td width=350><input type=text name='string' class=halfbutton value='<?if(!isset($_POST['string'])) echo "�����...' onclick='this.value=\"\";";else echo str_replace("'","&#39;",$_POST['string']);?>'></td>
	</tr>
	<tr>
	<td colspan=2 width=500 style='text-align:center;'><div align=center><input type=submit value='������' class=halfbutton>&nbsp;<input type=reset value='��������' class=halfbutton></div></td>
	</tr>
	</table>
	</form>
	</body></html>
<?	if(isset($_GET['apply']))
	{
		?>
		<table border=0 width=491 cellspacing=0 cellpadding=0>
<?
		$phpfm['result']=search_file($_SESSION['path'],$_POST['string'],intval($_POST['by']),(isset($_POST['recursive']) ? true : false),intval($_POST['regs']));
		//**
		foreach($phpfm['result']['dirs'] as $value)
		{
			$phpfm['value1']=($_POST['regs']==1 ? preg_replace("/".$_POST['string']."/is","<b>\</b>",basename($value)) : str_replace($_POST['string'],"<b>".$_POST['string']."</b>",basename($value)));
			?>
			<tr>
			<td width=323 valign=top><a class=black2 href='index.php?absolute_dir=<?=rawurlencode($value)?>&act=open' title='������� ��� �����' target=_blank><img src='images/folder.png' alt='�����' width=16 height=16 border=0>&nbsp;<?=$phpfm['value1']?></a></td>
			<td width=69 valign=top class=comment2 style='text-align:right;'>&nbsp;</td>
			<td width=99 valign=top class=comment2>&nbsp;�����</td>
			</tr>
<?	
		}
		foreach($phpfm['result']['files'] as $value)
		{
			$phpfm['value1']=($_POST['regs']==1 ? preg_replace("/".$_POST['string']."/is","<b>\</b>",basename($value)) : str_replace($_POST['string'],"<b>".$_POST['string']."</b>",basename($value)));
			$phpfm['pathinfo']=pathinfo($value);
			@$phpfm['ext']=$phpfm['pathinfo']['extension'];
			?>
			<tr>
			<td width=323 valign=top><a class=black2 href='index.php?absolute_dir=<?=rawurlencode(dirname($value))?>&act=open' title='������� �����, ���������� ������ ����' target=_blank><img src='images/file-<?=$phpfm['ext']?>.png' alt='����' width=16 height=16 border=0>&nbsp;<?=$phpfm['value1']?></a></td>
			<td width=69 valign=top class=comment2 style='text-align:right;'><?=round(filesize($value)/1024,1)."&nbsp;��&nbsp;&nbsp;"?></td>
			<td width=99 valign=top class=comment2>&nbsp;����</td>
			</tr>
<?	
		}
		?>
		</table>
		<div align=center>[����� ����� <?$timer->stop(); echo round($timer->elapsed(),5)." ���";?>]
<?	}		
	die();
	exit;
	break;
case "additional":
	if(isset($_GET['subact'])) $phpfm['subact']=$_GET['subact'];
	else $phpfm['subact']=false;
	?>
	<html>
	<head>
	<title>�������������� ����������� PhpFM (�� �������� �����)</title>
	<style type='text/css'>
	<!--
	BODY,table { background-color:#F7F6F3; color:black; font-family: Verdana; font-size: 8pt; }
	TD { text-align:left; vertical-align:top; }
	.comment2 { font-family: Verdana; font-size: 7pt; color:black }
	#multitext { background-color: #FFFFFF; color:#000000; font-family:Courier new, Verdana, Arial; font-size:8pt }
	.halfbutton {border-style : outset;width : 95px; height:20 px;}
	input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
	-->
	</style>
	</head>
	<body>
	� ������ ������� ����� ��������� ����� ������ �������� - �� �������� ����� � ��������� ���� �� ���� ��� ������������ ��������� ���� �� ����� (��� ����� ������ ����� ����� ����� ��������� �����), �� ���������� PHP-���� � ������������� md5 - ������������ �������.
	<form action='index.php?act=additional&subact=mk' method=post>
	<h3>������� ����� ���� ��� �����</h3>
	<select name='type'><option value='file'>����</option><option value='dir'>�����</option></select>
	<input type=text name='text' value='��������' style='width:400'>
	<input type=submit value='�������' class=halfbutton>
	<br>��� ������� �������� ������������� �����, ��� ���������� ��������� ��� �������������� !
	<?//**
	if($phpfm['subact']=="mk")
	{
		CheckAccess(realpath($_SESSION['path']."/".$_POST['text']));
		echo "<hr>";
		switch($_POST['type'])
		{
		case "file":
			if(mkfile(realpath($_SESSION['path']."/".$_POST['text']))) echo "���� <b>".$_POST['text']."</b> ������� ������";
			else echo "���� �� ����� ���� ������. ��������� ����� �� ������ � ����� <b>".$_SESSION['path']."</b>";
			break;
		case "dir":
			if(@mkdir(realpath($_SESSION['path']."/".$_POST['text']),0777)) { @chmod($_SESSION['path']."/".$_POST['text'],0777); echo "����� <b>".$_POST['text']."</b> ������� �������"; }
			else echo "������� ����� �� �������: ".$php_errormsg;
			break;
		}
		echo "<hr>";
	}?>
	</form>
	<form action='index.php?act=additional&subact=chmod' method=post>
	<h3>CHMOD (������� �����)</h3>
	<select name='type'><option value='file'>������</option><option value='dir' style='background-color:yellow;'>����������</option></select>
	<input type=text name='text' value='��������' style='width:300'>
	<input type=text name='mod' value='�����' style='width:100'>
	<input type=submit value='chmod' class=halfbutton>
	<br>��� ����� ���� ������ � ����� �� ����� ������ ����� shell.
	<br><b>����������� �����</b> - ������ ����� �������� �� ������ �����, ������� �� ������ ������� �����, �� � ��������� � �� ����� � ��������.
	<?//**
	if($phpfm['subact']=="chmod")
	{
		echo "<hr>";
		$phpfm['content']=$_POST['text'];
		$phpfm['mod']=intval($_POST['mod']);
		//**
		$phpfm['full path']=realpath($_SESSION['path']."/".$phpfm['content']);
		//**
		//���� chmod �������� ��� �� ��� ����������...
		//**
		CheckAccess($phpfm['full path']);
		//**
		if($_POST['type']=="dir")
		{
			if(@chmoddir($phpfm['full path'], $phpfm['mod'])) echo "<br><br>����� <b>".$phpfm['content']."</b> ������� ��������";
			else echo "<br><br>����� ����� <b>".$phpfm['content']."</b> �� ����� ���� ��������. ��������, �� ���������� ������� ����� �����, ��� ��������� ���� ����� �� ����������. ����� PHP ����� �������� � ����� ������, ����� chmod ����������. ���������� ����������� ������� ����� ������ ������������.";
		}else
		{
			if(@chmod($phpfm['full path'], $phpfm['mod'])) echo "<br><br>����� <b>".$phpfm['content']."</b> ������� ��������";
			else echo "<br><br>����� ����� ������� �� �������: ".$php_errormsg;
		}
		echo "<hr>";
	}?>
	</form>
	<form action='index.php?act=additional&subact=upload' method=post enctype='multipart/form-data'>
	<h3>�������� ����� �� ������</h3>
	<input type=file name='files[]' style='width:300'><input type=file name='files[]' style='width:300'>
	<br><input type=file name='files[]' style='width:300'><input type=file name='files[]' style='width:300'>
	<br><input type=file name='files[]' style='width:300'><input type=file name='files[]' style='width:300'>
	<br><input type=checkbox name='folder' value='yes' id='folder'> <label for="folder">�������� ��� ����� (������� <b>readme.txt</b> ��� ������������)</label>
	<br><br>�����, ������� �� ���������, �� ������ ��������� <b><?=ini_get('upload_max_filesize')?></b>. ��� ����� ���������������� ��� ���������� !!! 
	<br><input type=submit value='��������' class=halfbutton<?if($phpfm['user-space-left'] <= 0) echo " disabled";?>>
	<?//**
	if($phpfm['subact']=="upload" && $phpfm['user-space-left'] >= 0)
	{
		echo "<hr>";
		if(!isset($_POST['folder']))
		{
			foreach($_FILES['files']['name'] as $i=>$value)
			{
				$value=str_replace(array('..',':','\\','/'),'',$value);
				$phpfm['file'][$i]=$_SESSION['path']."/".basename($value);
				@CheckByFilter(file2string($_FILES['files']['tmp_name'][$i]));
				if(!@move_uploaded_file($_FILES['files']['tmp_name'][$i],$phpfm['file'][$i])) $phpfm['failed']=$i;
				//**
				//����� �������� ����� �����...
				//**
				@$phpfm['length']+=strlen(file2string($phpfm['file'][$i]));
			}
			EditDiskSpace($phpfm['length']);
			//**
			if(isset($phpfm['failed']) && !file_exists($phpfm['file'][$phpfm['failed']])) echo "<br><br>���� �������� �� �������: ".$php_errormsg;
			else echo "<br><br>��� ����� ������� �������� �� ������";
		}else
		{
			$phpfm['file']=$phpfm['rootpath']."/tempfile.dat";
			$phpfm['tempfile']=$_FILES['files']['tmp_name'][0];
			//**
			if(!file_exists($phpfm['tempfile'])) echo "<br><br>��������, �� ���� �������� �� �������. ���������� ������ � ���������, ��� �������� �� ��������� ������������ ������ ������������� ����� (<b>".ini_get('upload_max_filesize')."</b>), � � ��� ���� ����� �� ������ � ����� � PhpFM. ����� �� ��������, ��� �������� ������ ���� � ������ ���� � ������ ����.";
			//**
			if(!file_exists($phpfm['file'])) mkfile($phpfm['file']);
			if(!@writefile($phpfm['file'],file2string($phpfm['file']).file2string($phpfm['tempfile']))) echo "<br><br>�� ������� ���������� ������ � ���� <b>".$phpfm['tempfile']."</b>. ��������, ��� ������� �� ��������� ��������� ����� �������� �������, ��� � ��� ��� ���� �� ������ ���� ����. � ����� ������ ������� ��������� ���� � ���������� �������� ���������� ��� ���.";
			else echo "<br><br>�������� ������� �������. ������� �� ������ ����, ���� �� �������� ��������� ��������.<br>�� �������� ������� ��� ��������� �� ����� ����� � PhpFM �� ��������� �������.";
			//**
			echo "</form><form action='".$phpfm['php_self']."?act=additional&subact=upload_dir' method=post><input type=submit value='������� ����, ���� ��� ��������� ����� �������� �� ������'>";
		}
		echo "<hr>";
	}
	//**
	//��������������� ����������� ����� � ����� � �������
	//**
	if($phpfm['subact']=="upload_dir"  && $phpfm['user-space-left'] >= 0)
	{
		echo "<hr>";
		$phpfm['file']=realpath($phpfm['rootpath']."/tempfile.dat");
		//**
		$phpfm['part']=explode("\n", file2string($phpfm['file']),2);
		$phpfm['5conf']=$phpfm['part'][0];
		//**
		$phpfm['4conf']=explode(";", $phpfm['5conf']);
		//**
		foreach($phpfm['4conf'] as $key=>$value)
		{
			$phpfm['part1']=explode("=",$value);
			$phpfm['conf'][trim($phpfm['part1'][0])]=trim($phpfm['part1'][1]);
		}
		//**
		if($phpfm['conf']['gzip']==1) $phpfm['part'][1]=trim(gzuncompress(trim($phpfm['part'][1])));
		$phpfm['part']=explode("\n",$phpfm['part'][1]);
		//**
		$phpfm['dir_upload']=$_SESSION['path']."/".$phpfm['conf']['name'];
		//**
		if(!is_dir($phpfm['dir_upload']) && @!mkdir($phpfm['dir_upload'],0777))
		{
			echo "<br><br>����� <b>".$phpfm['dir_upload']."</b> ������� �� �������. �������: ".$php_errormsg;
		}else
		{
			@chmod($phpfm['dir_upload'], 0777); //��� safe mode
			//**
			foreach($phpfm['part'] as $key=>$value)
			{
				$phpfm['part1']=explode(" ",$value,2);
				//**
				if(!empty($phpfm['part1'][0]) && !empty($phpfm['part'][1])) $phpfm['files_upload'][trim($phpfm['part1'][0])]=base64_decode(trim($phpfm['part1'][1]));
			}
			//**
			//��������������� �������� ������ � �� ����������
			//**
			foreach($phpfm['files_upload'] as $key=>$value)
			{
				CheckByFilter($value);
				mkfile(realpath($phpfm['dir_upload']."/".$key));
				writefile(realpath($phpfm['dir_upload']."/".$key),$value);
			}
			//**
			unlink($phpfm['file']); //������� ��������� ����
			echo "<br><br>������� ������� ����� �������. ���� �� �� ������ ������� �������������� �� PHP, �� ����� ���� �������� �������. � ��������� ������ ��������� ����� �����, � ������� �� ����������� ��� �����. ���� �� ��������, ���������� ������� ������� ������ ��� ����� � ��������� �� ����� 0777 (�� ������ ��������� ��-�������) � ���������� ��� ���.";
		}
		echo "<hr>";
	}
	?>
	</form>
	<form action='index.php?act=additional&subact=eval' method=post>
	<h3>��������� PHP-���</h3>
	<input type=checkbox name='alternate' value='yes' id='alternate'> <label for='alternate'>�������������� ������� (���������� ����� ����� �� ������ � ����� � PhpFM)</label>
	<br><textarea name='text' id=multitext style='width:600;height:100'><?=((isset($_POST['text']) && $phpfm['subact']=='eval') ? ((get_magic_quotes_runtime() || get_magic_quotes_gpc()) ? (stripslashes($_POST['text'])) : ($_POST['text'])) : ("/* ��������� PHP-���: ���� ����� ������� �����, �� <? � ?> ������ �� �����, ���� ������ �������������� �������, ��������, ����� */\n// phpinfo();"))?></textarea>
	<input type=submit value='���������' class=halfbutton<?if($phpfm['user-settings']['eval']!=1) echo " disabled";?>>
	<?//**
	if($phpfm['subact']=="eval" && $phpfm['user-settings']['eval']==1)
	{
		$phpfm['content']=$_POST['text'];
		//**
		if(get_magic_quotes_runtime() || get_magic_quotes_gpc()) $phpfm['content']=stripslashes($phpfm['content']);
		//**
		echo "<hr><pre>";
		//**
		CheckByFilter($phpfm['content']);
		//**
		if(isset($_POST['alternate']))
		{
			mkfile($phpfm['rootpath']."/eval.php");
			writefile($phpfm['rootpath']."/eval.php",$phpfm['content']);
			include($phpfm['rootpath']."/eval.php");
			unlink($phpfm['rootpath']."/eval.php");
		}else
		{
			eval($phpfm['content']);
		}
		echo "</pre><hr>";
	}?>
	</form>
	<form action='index.php?act=additional&subact=shell' method=post>
	<h3>SHELL (������� ������������ �������)</h3>
	<br><textarea name='text' id=multitext style='width:600;height:100'><?=((isset($_POST['text']) && $phpfm['subact']=='shell') ? ((get_magic_quotes_runtime() || get_magic_quotes_gpc()) ? (stripslashes($_POST['text'])) : ($_POST['text'])) : ("'������� ������������ �������\nls '� Unix\ndir '� Windows"))?>
</textarea>
	<input type=submit value='���������'<?if($phpfm['user-settings']['eval']!=1) echo " disabled";?>>
	<?//**
	if($phpfm['subact']=="shell" && $phpfm['user-settings']['eval']==1)
	{
		$phpfm['content']=$_POST['text'];
		//**
		if(get_magic_quotes_runtime() || get_magic_quotes_gpc()) $phpfm['content']=stripslashes($phpfm['content']);
		//**
		echo "<hr><pre>";
		exec($phpfm['content'],$phpfm['answer'],$phpfm['return']);
		echo htmlspecialchars(convert_cyr_string(implode("\n",$phpfm['answer']),'d','w'));
		//**
		echo "</pre><hr>";
		if(!$phpfm['return']) echo "������ ��������� �������";
		else echo "������ �� �������� �������";
	}?>
	</form>
	<form action='index.php?act=additional&subact=convert' method=post>
	<h3>������������� �����������</h3>
	<select name='type'>
	<option>---����������---</option>
	<option>&nbsp;</option>
	<option>&nbsp;MD5</option>
	<option value='md5'>&nbsp;&nbsp;-�����������</option>
	<option value='de_md5'>&nbsp;&nbsp;-������������ (�������� ������)</option>
	<option>&nbsp;Base64</option>
	<option value='base64'>&nbsp;&nbsp;-�����������</option>
	<option value='base64_chunk'>&nbsp;&nbsp;-����������� � ������� �� �������</option>
	<option value='base64_decode'>&nbsp;&nbsp;-������������</option>
	<option>&nbsp;</option>
	<option>---�����---</option>
	<option>&nbsp;</option>
	<option>&nbsp;Unix time</option>
	<option value='time'>&nbsp;&nbsp;-������� �����</option>
	<option value='2unixtime'>&nbsp;&nbsp;-������������� � Unix time</option>
	<option value='fromunixtime'>&nbsp;&nbsp;-������������� �� Unix time</option>
	<option>&nbsp;����</option>
	<option value='dayofweek'>&nbsp;&nbsp;-������� ���� ������</option>
	<option value='month'>&nbsp;&nbsp;-������� ����� ;)</option>
	<option value='year'>&nbsp;&nbsp;-������� ��� :D</option>
	<option value='d_m_y'>&nbsp;&nbsp;-���� � ������� ��.��.����</option>
	<option value='d_m_y_h_i_s'>&nbsp;&nbsp;-���� � ������� ��.��.���� �., ��:��:��</option>
	<option>&nbsp;</option>
	<option>---������� �����---</option>
	<option>&nbsp;</option>
	<option>&nbsp;��������</option>
	<option value='2translit'>&nbsp;&nbsp;-������������� ����� � ��������</option>
	<option value='fromtranslit'>&nbsp;&nbsp;-������������� ����� �� ���������</option>
	<option>&nbsp;���������</option>
	<option value='koi2win'>&nbsp;&nbsp;-�� KOI-8 � WIN</option>
	<option value='win2koi'>&nbsp;&nbsp;-�� WIN � KOI-8</option>
	<option value='dos2win'>&nbsp;&nbsp;-�� DOS � WIN</option>
	<option value='win2dos'>&nbsp;&nbsp;-�� WIN � DOS</option>
	<option value='mac2win'>&nbsp;&nbsp;-�� MAC � WIN</option>
	<option value='win2mac'>&nbsp;&nbsp;-�� WIN � MAC</option>
	<option value='iso2win'>&nbsp;&nbsp;-�� ISO � WIN</option>
	<option value='win2iso'>&nbsp;&nbsp;-�� WIN � ISO</option>
	</select>
	<?
	if($phpfm['subact']=="convert")
	{
		@$phpfm['content']=$_POST['text'];
		if(get_magic_quotes_runtime() || get_magic_quotes_gpc()) $phpfm['content']=stripslashes($phpfm['content']);
		//**
	?><br><textarea name='text' id=multitext style='width:600;height:100'><?=$phpfm['content']?></textarea>
<input type=submit value='��������������'>
<?		$phpfm['hieroglyph']="<br><br>���� ��� ����� ������������� ������, ��������� � ���������� ���������, ��� ��� ����� ����� ������� ����������� ��� ������ � ������� �������, �������������� ���������� &quot;Hieroglyph&quot; - <a href='http://www.adelaida.net/hieroglyph/index.html'>adelaida.net/hieroglyph/index.html</a><pre>";
		//**
		echo "<hr><pre>";
		//**
		switch($_POST['type'])
		{
		case "md5":
			echo md5($phpfm['content']);
			break;
		case "de_md5":
				?></form><form action='demd5.php' method=post target=blank>
� ������ �� ������ <select name='limit'><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5' style='background-color:red'>5</option></select> ��������.
�������� ������ ������ - <select name='koef'><option value='26'>��������� ����� � ������ �������� (hello)</option><option value='52'>��������� ����� � ������ � ������� �������� (heLLo)</option><option value='62'>���������� + ����� (h3LL0)</option><option value='97'>���������� + ����������� (h3$L0)</option><option value='163'>���������� + ������� ����� (�$%aL)</option></select>

��� ����� ������� ������� ����� ������������ ��������� <a href='http://forum.dklab.ru/download.php?id=95'>mdcrack</a> (������������ ���� - 
<a href='http://mdcrack.df.ru'>mdcrack.df.ru</a>).����� ������������� ������ �� 8 �������� �� 7 ����� ����������� ������.

������� ������, ������� ����� ������������� � ������� "��������������"
<br><input type=text name=hash value='������'><input type=submit value='��������������'>
<?			break;
		case "base64":
			echo base64_encode($phpfm['content']);
			break;
		case "base64_chunk":
			echo chunk_split(base64_encode($phpfm['content']));
			break;
		case "base64_decode":
			echo base64_decode($phpfm['content']);
			break;
		case "time":
			echo time();
			break;
		case "2unixtime":
			echo strtotime($phpfm['content']);
			break;
		case "fromunixtime":
			echo "��������� ���������:\n";
			echo date("d.m.Y", $phpfm['content'])."\n";
			echo date("d.m.Y | H:i:s", $phpfm['content'])."\n";
			echo date("d.m.Y �., H:i:s", $phpfm['content'])."\n";
			echo date("Y m d", $phpfm['content'])."\n";
			echo date("d/m/Y", $phpfm['content'])."\n";
			echo date("d F, Y ", $phpfm['content'])."\n";
			echo date("l, d F, Y", $phpfm['content'])."\n";
			echo date("r", $phpfm['content'])."\n";
			break;
		case "dayofweek":
			$phpfm['weeks']=array("�����������","�����������","�������","�����","�������","�������","�������");
			echo $phpfm['weeks'][date("w")];
			break;
		case "month":
			$phpfm['months']=array("","������","�������","����","������","���","����","����","������","��������","�������","�������");
			echo $phpfm['months'][date("n")];
			break;
		case "year":
			echo date("Y");
			break;
		case "d_m_y":
			echo date("d.m.Y");
			break;
		case "d_m_y_h_i_s":
			echo date("d.m.Y �., H:i:s");
			break;
		case "2translit":
			echo "</pre>";
			$phpfm['translit']=array('�'=>'a','�'=>'b','�'=>'v','�'=>'g','�'=>'d','�'=>'e','�'=>'e','�'=>'j','�'=>'z','�'=>'i','�'=>'y','�'=>'k','�'=>'l','�'=>'m','�'=>'n','�'=>'o','�'=>'p','�'=>'r','�'=>'s','�'=>'t','�'=>'u','�'=>'f','�'=>'h','�'=>'ts','�'=>'ch','�'=>'sh','�'=>'sch','�'=>"'",'�'=>'i','�'=>"'",'�'=>'�','�'=>'yu','�'=>'ya','�'=>'A','�'=>'B','�'=>'V','�'=>'G','�'=>'D','�'=>'E','�'=>'E','�'=>'J','�'=>'Z','�'=>'I','�'=>'Y','�'=>'K','�'=>'L','�'=>'M','�'=>'N','�'=>'O','�'=>'P','�'=>'R','�'=>'S','�'=>'T','�'=>'U','�'=>'F','�'=>'H','�'=>'TS','�'=>'CH','�'=>'SH','�'=>'SCH','�'=>"'",'�'=>'I','�'=>"'",'�'=>'�','�'=>'YU','�'=>'YA',);
			echo str_replace(array_keys($phpfm['translit']),array_values($phpfm['translit']),$phpfm['content']);
			echo "<br><br>���� ��� �� ���������� �������� ��������������, �������������� ���������� &quot;Hieroglyph&quot; - <a href='http://www.adelaida.net/hieroglyph/index.html'>adelaida.net/hieroglyph/index.html</a><pre>";
			break;
		case "fromtranslit":
			echo "</pre>";
			$phpfm['untranslit']=array('ja'=>'�','ya'=>'�','yo'=>'�','oo'=>'�','ch'=>'�','sch'=>'�','sh'=>'�','ts'=>'�','c'=>'�','yu'=>'�','a'=>'�','b'=>'�','v'=>'�','g'=>'�','d'=>'�','e'=>'�','j'=>'�','z'=>'�','i'=>'�','y'=>'�','k'=>'�','l'=>'�','m'=>'�','n'=>'�','o'=>'o','p'=>'�','r'=>'�','s'=>'�','t'=>'�','u'=>'�','f'=>'�','h'=>'�',"'"=>'�','JA'=>'�','YA'=>'�','YO'=>'�','OO'=>'�','CH'=>'�','SCH'=>'�','SH'=>'�','TS'=>'�','C'=>'�','YU'=>'�','A'=>'�','B'=>'�','V'=>'�','G'=>'�','D'=>'�','E'=>'�','J'=>'�','Z'=>'�','I'=>'�','Y'=>'�','K'=>'�','L'=>'�','M'=>'�','N'=>'�','O'=>'O','P'=>'�','R'=>'�','S'=>'�','T'=>'�','U'=>'�','F'=>'�','H'=>'�',);
			echo str_replace(array_keys($phpfm['untranslit']),array_values($phpfm['untranslit']),$phpfm['content']);
			echo "<br><br>���� ��� �� ���������� �������� ����������������, �������������� ���������� &quot;Hieroglyph&quot; - <a href='http://www.adelaida.net/hieroglyph/index.html'>adelaida.net/hieroglyph/index.html</a><pre>";
			break;
		case "koi2win":
			echo "</pre>";
			echo convert_cyr_string($phpfm['content'],'k','w');
			echo $phpfm['hieroglyph'];
			break;
		case "win2koi":
			echo "</pre>";
			echo convert_cyr_string($phpfm['content'],'w','k');
			echo $phpfm['hieroglyph'];
			break;
		case "dos2win":
			echo "</pre>";
			echo convert_cyr_string($phpfm['content'],'d','w');
			echo $phpfm['hieroglyph'];
			break;
		case "win2dos":
			echo "</pre>";
			echo convert_cyr_string($phpfm['content'],'w','d');
			echo $phpfm['hieroglyph'];
			break;
		case "mac2win":
			echo "</pre>";
			echo convert_cyr_string($phpfm['content'],'m','w');
			echo $phpfm['hieroglyph'];
			break;
		case "win2mac":
			echo "</pre>";
			echo convert_cyr_string($phpfm['content'],'w','m');
			echo $phpfm['hieroglyph'];
			break;
		case "iso2win":
			echo "</pre>";
			echo convert_cyr_string($phpfm['content'],'i','w');
			echo $phpfm['hieroglyph'];
			break;
		case "win2iso":
			echo "</pre>";
			echo convert_cyr_string($phpfm['content'],'w','i');
			echo $phpfm['hieroglyph'];
			break;
		}
		//**
		echo "</pre><hr>";
	}else
	{
		?><br><textarea name='text' id=multitext style='width:600;height:100'></textarea>
<input type=submit value='��������������'>
<?	}?>
	</form>
	<form action='index.php?act=additional&subact=prepare2upload' method=post>
	<h3>���������� ������� ����������</h3>
	<select name=gzip style='width:250'><option value=1>����� GZip'�� (������ ����������)</option><option value=0>�� ������� GZip'��</option></select>
	&nbsp;<input type=text name=maxsize value='������ ����������'>
	<input type=text name=text value='�������� �����' style='width:500'>&nbsp;<input type=submit value='�����������' class=halfbutton>
	<br><b>������ ����������</b> - ������ ���������� �����, ���������� ����� ���������. ����������� � ����������. <i>�� ������ ����� ������ ���� ��� ������ ���� - ��� �� ����� ������ !</i>. �� ��������� - <b><?=ini_get('upload_max_filesize')?></b>
	<?
	if($phpfm['subact']=="prepare2upload")
	{
		@$phpfm['content']=$_POST['text'];
		//**
		if(empty($_POST['maxsize']) or @$_POST['maxsize']<=0 or !is_numeric($_POST['maxsize']))  $phpfm['maxsize']=intval(ini_get('upload_max_filesize'));
		else $phpfm['maxsize']=abs(intval($_POST['maxsize']));
		//**
		if(get_magic_quotes_runtime() || get_magic_quotes_gpc()) $phpfm['content']=stripslashes($phpfm['content']);
		//**
		$phpfm['gzip']=$_POST['gzip'];
		//**
		$phpfm['dircontent']=dircontent(realpath($_SESSION['path']."/".$phpfm['content']),$phpfm['maxsize'],$phpfm['gzip']);
		//**
		$phpfm['files']=0;
		//**
		foreach($phpfm['dircontent'][0] as $key=>$value)
		{
			$phpfm['files']++;
			mkfile($phpfm['rootpath']."/dir_upload".$key.".dat");
			writefile($phpfm['rootpath']."/dir_upload".$key.".dat",$value);
		}
		//**
		echo "<hr>";
		echo "���� �� �� ������ ������� ��������������, ������ ����� ������������. ��������� ".declesion($phpfm['files'],array("��������","���������","����������"))." � ������ <b>dir_upload<i>N</i>.dat</b> �� ������ (��� ������� ������� &quot;�������� ��� �����&quot;, ����������� �� ������ �����, � �� �������� ������� ���������� ����������), ������� ����� � ����� � phpFM, � ������� ��.";
		echo "<hr>";
	}
	?>
	</form>
	</body>
	</html>
<?	die();
	exit;
	break;
case "administration":
	if(!isset($_SESSION['logined']))
	{
		echo "<script language=javascript>alert('���� ������ �� ������������ ��� ���.\\n���������, ��� ���� �������� ������������ � ����� ���� ����������� ���������������.');window.close();</script>";
		die();
		exit;
		break;
	}
	?>
	<html>
	<head>
	<title>����������������� ����� PhpFM <?=$phpfm['version']?></title>
	<style type='text/css'>
	<!--
	BODY,table { background-color:#F7F6F3; color:black; font-family: Verdana; font-size: 8pt; text-align:justify; }
	TD { text-align:justify; vertical-align:center; }
	.comment2 { font-family: Verdana; font-size: 7pt; color:black }
	#multitext { background-color: #FFFFFF; color:#000000; font-family:Courier new, Verdana, Arial; font-size:8pt }
	.halfbutton {border-style : outset;width : 95px; height:20 px;}
	input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
	A {COLOR: black; TEXT-DECORATION: none; font-family: Tahoma; font-size: 8pt; cursor: default; display: block; border: 0px solid #000000;}
	A:hover {COLOR: white; TEXT-DECORATION: none; background-color: #00126C;}
	-->
	</style>
	</head>
	<body>
	<h3 align=center style='align:center;'>����� ���������� � ������� PhpFM</h3>
	<h6 align=center style='align:center;'>��� ������ �������������� � ���� ������ � ��������� �������� ��-���������</h6>
	<!--���������-->
	<a href='index.php?act=administration&subact=user-control'><b>-&gt;&nbsp;</b>���������� ��������������</a>
	<a href='index.php?act=administration&subact=protection'><b>-&gt;&nbsp;</b>������ �� ������ �������� ���������� � ������������ ������� ��������</a>
	<a href='index.php?act=administration&subact=stats'><b>-&gt;&nbsp;</b>���������� ������������� ��������� ������������</a>
	<a href='index.php?act=administration&subact=help'><b>-&gt;&nbsp;</b>�������</a>
	<!--��������-->
	<?
	if(isset($_GET['subact']))
	{
		switch($_GET['subact'])
		{
		case "user-control":
			?>
			<hr>
			<h5>���������� ��������������</h5>
			<table width=600 cellspacing=2 cellpadding=2 border=1>
			<form action='index.php?act=administration&subact=apply' method=post>
			<tr>
			<td width=100><b><i>�����:</i></b></td>
			<td width=100><b><i>������:</i></b></td>
			<td width=150><b><i>�����:</i></b></td>
			<td width=150><b class=comment2><i>������������ �����:</i></b></td>
			<td width=100><b><i>Eval, SHELL:</i></b></td>
			</tr>
<?			foreach($phpfm['users'] as $key=>$value)
			{
				$phpfm['settings'][$key]=DumpSettings($key, chr(0));
				$phpfm['settings'][$key]['used']=ceil(file2string($phpfm['rootpath']."/counters/space_".$key)/(1024*1024));
			}
			//**
			foreach($phpfm['settings'] as $key=>$value)
			{
				?>
				<tr>
				<td width=100><input type=text name='name[]' value='<?=$key?>' style='width:90'></td>
				<td width=100><input type=text name='pass[]' value='<?=$value['pass']?>' style='width:90'></td>
				<td width=150><input type=text name='dir[]' value='<?=$value['dir']?>' style='width:140'></td>
				<td width=150><nobr><b><?=$value['used']?> ��</b> �� <input type=text name='quota[]' value='<?=$value['quota']?>' style='width:40'>&nbsp;��</b></nobr></td>
				<td width=100><select name='eval[]' style='width:90'><option value='1' style='background-color:green;color:white'<?if($value['eval']==1) echo " selected";?>>����</option><option value='0' style='background-color:red;color:white'<?if($value['eval']==0) echo " selected";?>>���</option></select></td>
				</tr>
<?			}
			?>
			<tr>
			<td colspan=5><hr></td>
			</tr>
			<tr>
				<td width=100><input type=text name='name[]' value='' style='width:90'></td>
				<td width=100><input type=text name='pass[]' value='' style='width:90'></td>
				<td width=150><input type=text name='dir[]' value='' style='width:140'></td>
				<td width=150><input type=text name='quota[]' value='�����' style='width:140'></td>
				<td width=100><select name='eval[]' style='width:90'><option value='1' style='background-color:green;color:white'>����</option><option value='0' style='background-color:red;color:white'>���</option></select></td>
			</tr>
			<tr>
			<td colspan=5><input type=submit value='��������' class=halfbutton>&nbsp;<input type=reset value='��������' class=halfbutton></td>
			</tr>
			</form>
			</table>
			<br><i>����� ������� ������������, ������� ��� ����� (��������� ����� ��������).</i>
			<br><br><i>����� �������� ������ ������������, ��������� ������ ������ (����� ��������� ������)</i>
<?			break;
		case "apply":
			?><hr>
<?			$phpfm['2write1']='<?'.		"\n".
			'$phpfm[\'users\'] = array'."\n".
			'('.						"\n";
			$phpfm['2write3']=');'."\n".'?>';
			$phpfm['2write2']="";
			//**
			foreach($_POST['name'] as $key=>$value)
			{
				if(empty($value) || trim($value)=="") continue;
				//**
				$phpfm['2write2'].="\t'".$value."' => 'pass=".$_POST['pass'][$key]."'.chr(0).'dir=".$_POST['dir'][$key]."'.chr(0).'quota=".intval($_POST['quota'][$key])."'.chr(0).'eval=".intval($_POST['eval'][$key])."',\n";
			}
			//**
			$phpfm['2write']=$phpfm['2write1'].$phpfm['2write2'].$phpfm['2write3'];
			$phpfm['file2write']=realpath($phpfm['rootpath']."/accounts.pl");
			//**
			if(@writefile($phpfm['file2write'],$phpfm['2write'])) echo "��������� ������������� ������� ��������";
			else echo "��������� ������������� �� ����� ���� ��������. ��������� ����� �� ���� accounts.pl � ��������� 0666";
			//**
			break;
		case "protection":
			?>
			<hr>
			<h5>������ �� ������ �������� ���������� � ������������ ������� ��������</h5>
			<form action='index.php?act=administration&subact=set-protection' method=post>
			���� ��������� �������� ������ (� ������� ���������� ������������):
			<br><br><b>1)</b> <input type=checkbox name='v1' value='1' id='v1'<?if($phpfm['filter'][0]==1) echo " checked";?>>&nbsp;<label for='v1'>��������� ������� �������� ���� � �������� ������� � ������������ � ���� (����������)</label>
			<br><b>2)</b> <input type=checkbox name='v2' value='2' id='v2'<?if($phpfm['filter'][1]==1) echo " checked";?>>&nbsp;<label for='v2'>��������� ������� �������� ������ �������� ���������� ������� (����������)</label>
			<br><b>3)</b> <input type=checkbox name='v3' value='3' id='v3'<?if($phpfm['filter'][2]==1) echo " checked";?>>&nbsp;<label for='v3'>��������� ������� SHELL-������� *</label>
			<br><b>4)</b> <input type=checkbox name='v4' value='4' id='v4'<?if($phpfm['filter'][3]==1) echo " checked";?>>&nbsp;<label for='v4'>��������� ������� ������� ���������� PHP-���� *</label>
			<br><b>5)</b> <input type=checkbox name='v5' value='5' id='v5'<?if($phpfm['filter'][4]==1) echo " checked";?>>&nbsp;<label for='v5'>��������� ������� ����� �������� ������� **</label>
			<br><b>6)</b> <input type=checkbox name='v6' value='6' id='v6'<?if($phpfm['filter'][5]==1) echo " checked";?>>&nbsp;<label for='v6'>��������� ��� ��������� �������� ����� ������ ***</label>
			<br><br>* ������� ��������� � ������� (SHELL) � ���������� PHP-���� ������������ ����� ��������� ������ �������.
			<br>** ��� �������� ���������� ��������. ������ ������ PHP-������ �������� � �������� ��������. � ������ ��������� � ���� ���. ���� ��� ������ ������, �� ��� ���������� �������...
			<br>*** �������� �����. ��� ���������� ���������� ������������� � �������� ������. � ����� �� ������ 50...
			<br><br><input type=submit value='��������' class=halfbutton> (��������� ������� ��� �������������� ����� ��������)
			</form>
<?			break;
		case "set-protection":
			//**
			if(isset($_POST['v1'])) $phpfm['v1']=1;else $phpfm['v1']=0;
			if(isset($_POST['v2'])) $phpfm['v2']=1;else $phpfm['v2']=0;
			if(isset($_POST['v3'])) $phpfm['v3']=1;else $phpfm['v3']=0;
			if(isset($_POST['v4'])) $phpfm['v4']=1;else $phpfm['v4']=0;
			if(isset($_POST['v5'])) $phpfm['v5']=1;else $phpfm['v5']=0;
			if(isset($_POST['v6'])) $phpfm['v6']=1;else $phpfm['v6']=0;
			//**
			$phpfm['2write']="<?\n\$phpfm['filter']=array(".$phpfm['v1'].",".$phpfm['v2'].",".$phpfm['v3'].",".$phpfm['v4'].",".$phpfm['v5'].",".$phpfm['v6']."); //��������� �������, ������ ����� �� �������������\n?>";
			$phpfm['file']=realpath($phpfm['rootpath']."/config.php");
			if(@writefile($phpfm['file'], $phpfm['2write'])) echo "<hr>��������� ������� ������� ���������";
			else echo "<hr>��������� ������� �� ����� ���� ���������. ��������� ����� ����� config.php - ��� ������ ���� 0666";
			//**
			break;
		case "stats":
			?><hr>
			<h5>���������� ������������� ��������� ������������</h5>
<?			foreach($phpfm['users'] as $key=>$value)
			{
				$phpfm['settings'][$key]=DumpSettings($key, chr(0));
				$phpfm['settings'][$key]['used']=file2string($phpfm['rootpath']."/counters/space_".$key)/(1024*1024);
			}
			//**
			?>
			<table width=600 cellspacing=0 cellpadding=2 border=0>
			<tr>
			<td width=90><b>�����:</b></td>
			<td width=510><b>������������� ��������� ������������:</b></td>
			</tr>
<?			foreach($phpfm['settings'] as $key=>$value)
			{
				?>
				<tr>
				<td width=90><?=$key?></td>
				<td width=510><img src='images/admin/red.png' width='<?$value['percent']=round(($value['used']/$value['quota'])*100);echo $value['percent']*5;?>' height=10><img src='images/admin/white.png' width='<?=(500-$value['percent']*5)?>' height=10></td>
				</tr>
<?			}?>
			</table>
<?			break;
		case "help":
			?><hr>
			<h5>�������. ����� ���������� �������</h5>
			1. <b>���������� ��������������</b>
			<br><br>
			<a href='#1'><b>&nbsp;-&gt;&nbsp;</b>��� ������� ������ ������������ ?</a>
			<a href='#2'><b>&nbsp;-&gt;&nbsp;</b>��� ������� ������������ ?</a>
			<a href='#3'><b>&nbsp;-&gt;&nbsp;</b>��� ��������������� ��������� ������������ ?</a>
			<a href='#4'><b>&nbsp;-&gt;&nbsp;</b>��������� ������ �� �������������</a>
			<br>
			2. <b>������ �� �������� ���������� � ������������ ������� ��������</b>
			<br><br>
			<a href='#5'><b>&nbsp;-&gt;&nbsp;</b>��� ������ ���� ������ ?</a>
			<a href='#6'><b>&nbsp;-&gt;&nbsp;</b>��� �������� ���� ������ ?</a>
			<a href='#7'><b>&nbsp;-&gt;&nbsp;</b>����� ������� ����� ������� ?</a>
			<a href='#8'><b>&nbsp;-&gt;&nbsp;</b>������ ����������� ������� ������������ ������� ?</a>
			<br>
			3. <b>���������� ������������� ��������� ������������</b>
			<br><br>
			<a href='#9'><b>&nbsp;-&gt;&nbsp;</b>��� ������, ������� ��������� ����� ������������ ������������ ?</a>
			<hr>
			<a name=1></a><h6>��� ������� ������ ������������</h6>
			� �������, � ����� ��������� ������ ����� �������������� ����� ���� ������ ����. ��������� ��, � ����� �������� ��������� ����� ������������ ��������� �������������
			<a name=2></a><h6>��� ������� ������������</h6>
			������� ����� ������������, �������� �� ������ �������. ����� �������� ��������� ���� ������������ ���������� �������������.
			<a name=3></a><h6>��� ��������������� ��������� ������������</h6>
			� ������� ���� ������. ��� ���� �����. ������� ��, �� ��������� ��������� ������������. �� �������� ����� �������� ��������� ������ ������ "��������" � ����� �������.
			<a name=4></a><h6>��������� ������ �� �������������</h6>
			<b>1)</b> �� ������� ������� ������� ������ ��� ������������� - �� ����� ����� ��������
			<br><b>2)</b> ������� �� ���������� ������������ � �������, ��� � ��� ! (�������� ���� ��� ����� - "<b>admin</b>", �� ���������� ������������ "<b>admin</b>")
			<br><b>3)</b> �������������� ����� �������������. �������� �������� ����� <b>/home/users/</b> ��� �������������, ���������� ��� ����� ��� �������������
			<br><b>4)</b> ���������� �� ���, ����� �� ���� ������������ �� ���� ������� � ����� � PhpFM (����� �� ����� ���� ����� � ������� ������������)
			<br><b>5)</b> ����������� �������� ����� ������� � ���, ��� ����������� ��������� ������ ����� <b>����������</b> ����� (�� ���� ���� � ������������ ��������� 312 �� �� 300, �� ����� ������ ������� ����� � �����)
			<br><b>6)</b> ����� ����� ��������� ������ � Eval � SHELL ���� �������������, ������� ����� ������� � ��� ����������. �������� ������� ������� "<b>unlink(</b><i>__FILE__</i><b>)</b>" ������ ���� ����
			<br><b>7)</b> �������� �������� �� 1 � 2 �����.
			<hr>
			<a name=5></a><h6>��� ������ ���� ������</h6>
			���� ������ ��������� ��� ����� �� ������� ������������ �������� ���� (�������� ����, ��������� ���� �������� ����������), � ���� ��� �������������� ��� ��� ������� ����� ����� �������������� ����� ���, �������� �������� �� ���� �������� / ������ ��������� � ���� ����.
			<a name=6></a><h6>��� �������� ���� ������</h6>
			�� ������� ���������, ����� ������� �������, � ����� �� ����������� ��������� ��������� ������� ������������ ������� ��� ������������ � ����� �������. ���� �� ������� ���-�� �������, �� �������� ��������� ������ ��������� ��������� � ������ �������������� ���������. ��� ����� �� ���� ������ ���� ��������� ������ ��� ���������� ������� ������.
			<a name=7></a><h6>����� ������� ����� �������</h6>
			������ ������ ���� �������� ����������. ���� �� �� ����� ��������� ����� �������������, ������ ����� �������� 3 � 4 ������. 5 � 6 ������ �������� <b>�� �������������</b>, ������ ��� ����������� ������������ ���� �������� ������� ������. �� ���� ��� ������������ ������� ������������.
			<a name=8></a><h6>������ ����������� ������� ������������ �������</h6>
			� ����������� �� ������� ����������:
			<br><b>1-2</b> - 5-7 %
			<br><b>3-4</b> - > 10 %
			<br><b>5</b> - <b style='color:red'>&gt; 40</b> %
			<br><b>6</b> - <b style='color:red'>100</b> %
			<hr>
			<a name=9></a><h6>��� ������, ������� ��������� ����� ������������ ������������</h6>
			���� ��� �����. ������ ���������. ���� ����� �����, ������� "��������" ������� ����� ������� � �������� ������ �� 5.
<?			break;
		}
	}
	?>
	</body>
	</html>
<?	die();
	exit;
	break;
}
//**
$_SESSION['path']=(isset($phpfm['dir'])) ? (realpath($phpfm['dir'])) : ($_SESSION['path']);
//**
$phpfm['dir']=$_SESSION['path'];
//**
CheckPath();
?>
<html>
<head>
<title> PhpFM <?=$phpfm['version']?> - ����� ����� � online-�������� ���������� </title>
<script language=javascript>
<!--
function OpenMenu3(filename,event) {
	closeMenu();
	var el, x, y, login, login2;
	el = document.getElementById("oMenu");
	x = event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft - 3;
	y = event.clientY + document.documentElement.scrollTop + document.body.scrollTop;

	if (event.clientY + 127 > document.body.clientHeight) { y-=131 } else { y-=2 }
	event.returnValue=false;

	el.innerHTML =
	'<a href="index.php?die=.&act=open" class=black title="�������� ������� ����"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;<b>��������</b></a>'+
	'<hr size=1>'+
<? if(isset($_SESSION['copied'])) { ?>	'<a href="index.php?act=paste" class=black title="�������� <? echo (($_SESSION['copied_type']=="dir") ? ("�����") : ("����"))." \'".$_SESSION['copied_name']."\'"?>"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;��������</a>'+
	'<a href="index.php?act=cancel_copy" class=black title="�������� �����������"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;��������</a>'+<? }
else { ?> '<a class=black style=\'color:gray\'>&nbsp;&nbsp;&nbsp;&nbsp;��������</a>'+ <? } ?>
	'<hr size=1>'+
	'<a href="javascript:make_folder();" class=black title="������� ����� �����"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;������� ����� �����</a>'+
	'<a href="javascript:make_file();" class=black title="������� ����� ����"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;������� ����� ����</a>'+
	'<hr size=1>'+
	'<a href="#" onclick="window.open(\'index.php?dir=.&act=properties\',\'properties\',\'width=400,height=480,resizeable=0,menubar=0,location=0,scrollbars=0,toolbar=0,status=0\');return false;" class=black title="���������� �������� ���� �����"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;��������</a>'+
	'';

	el.style.left = x + "px";
	el.style.top  = y + "px";
	if(el.filters) el.filters["BlendTrans"].Apply();
	el.style.visibility = "visible";
	if(el.filters) el.filters["BlendTrans"].Play();
}
//-->
</script>
<script language=javascript src='main.js' type='text/javascript'></script>
<style type='text/css'>
<!--
BODY { background-color:white; }
TD { text-align:left; vertical-align:top; }
A.black {COLOR: black; TEXT-DECORATION: none; font-family: Tahoma; font-size: 8pt; cursor: default; display: block; border: 0px solid #000000;}
A.menu { color: #215DC6;position:relative;top:-3;left:2; font-family: Tahoma;}
A.black:hover {COLOR: white; TEXT-DECORATION: none; background-color: #00126C;}
A.black2 {COLOR: black; TEXT-DECORATION: none; font-family: Tahoma; font-size: 8pt; cursor: default;}
A.black2:hover {TEXT-DECORATION: none;}
.title { font-family: "Trebuchet MS"; font-size: 13 px; color:white }
.comment { font-family: Verdana; font-size: 8pt; color:black }
.comment2 { font-family: Tahoma; font-size: 8pt; color:black }
.halfbutton {border-style : outset;width : 95px; height:20 px;}
input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
.middle {  vertical-align:middle; text-align:left; align:left; }
.center { vertical-align: middle; text-align: center; align: center; }
-->
</style>
</head>
<body onmousedown='if(!menu_opened) return cm(false,event,"3");' oncontextmenu='return false;' onkeydown='kd(thelast_el,event);' onclick='closeMenu();menu_opened=false;if(bl) uncl(event);'>
<?
if(preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT'])) echo '<script>alert("������� ������������ �������� Opera! ��������� ���������� ��� ���, ����� ������� ������� (�������� Internet Explorer ��� Mozilla), ����� ����������� ������� ������� ��������� ��������� ����� ��� ��� ������������");</script>';
?>
<div id="oMenu" style='filter: BlendTrans(Duration=0.2); visibility:hidden;position:absolute;border-color: #9D9DA1 #000000 #000000 #9D9DA1;border-style: solid;border-width: 1px;background-color: white; line-height: 16px; width: 150;z-index: 100;'></div>
<table width=740 cellspacing=0 cellpadding=0 border=0 align=center>
<tr>
<form action='index.php' method=get name='menu1'>
<td width=740 height=84 background="images/roof.png" colspan=3 valign=top>
<table width=740 cellspacing=0 cellpadding=0 border=0>
<tr height=5><td height=5><img width=1 height=5 style='visibility:hidden;'></td></tr>
<tr height=25>
<td width=25 height=25>&nbsp;</td><td class=middle width=710><b class=title><?=basename($_SESSION['path'])?></b></td>
</tr>
<tr height=31>
<td colspan=2 height=31 class=middle>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:history.back(1)'><img src='images/back.png' width=15 height=15 alt='�����' border=0></a>
&nbsp;&nbsp;<a href='javascript:history.forward(1)'><img src='images/forward.png' width=15 height=15 alt='������' border=0></a>
&nbsp;&nbsp;&nbsp;&nbsp;<a href='index.php?dir=..&act=open'><img src='images/up.png' width=12 height=16 alt='�����' border=0></a>
&nbsp;<a href='index.php?dir=.&act=open'><img src='images/reload.png' width=13 height=15 alt='��������' border=0></a>
<?
if($_SESSION['drives']!=false)
{
	$phpfm['curdrive']=substr($_SESSION['path'],0,1);
	echo "&nbsp;<select name='absolute_dir' onchange='window.location.href=&quot;".$phpfm['php_self']."?absolute_dir=&quot;+this.value' style='font-weight: bold;' onfocus='typing=true;' onblur='typing=false;'>";
	foreach($_SESSION['drives'] as $value) echo "<option value='".$value.":/'".($phpfm['curdrive']==$value ? " selected" : "").">".strtoupper($value).":</option>";
	echo "</select>";
}?></td>
</tr>
<tr height=21>
<td colspan=2><table width=740 height=21 cellspacing=0 cellpadding=0 border=0><tr height=21><td height=21 width=70>&nbsp;</td><td><input type=text name='absolute_dir' value='<?=$_SESSION['path']?>' style='font-family: Tahoma;width:567;height:21;' onfocus="typing=true;" onblur="typing=false;"><input type=submit value='�������' style='visibility:hidden;display:none'></td></tr></table></td>
</tr>
</table>
</td>
</form>
</tr>
<tr>
<td width=230 valign=top background='images/left_menu.png'>
<br><table width=199 border=0 cellspacing=0 cellpadding=0 class=comment align=center>
<form action='index.php' method=post>
<tr>
<td width=199 height=25 style='vertical-align:middle; background-image: url("images/top_left.png");'>&nbsp;&nbsp;<b style='color: #215DC6;'>����� ����������</b></td>
</tr>
<tr>
<td width=199 style='text-align:justify; background-image: url("images/bkg_left.png");' nowrap=nowrap>
<div style='padding-left:10px;'>
<br>����� ����������, <b><?=$phpfm['user-name']?></b> !
<br>���������� - <b><?=$phpfm['user-settings']['dir']?></b>
<br>����� - <b><?=$phpfm['user-settings']['quota']?> ��</b>
<br>�������� ����� - <b><?=$phpfm['user-space-left']?> ��</b>
<br>PHP-��� � SHELL - <b><?if($phpfm['user-settings']['eval']==1) echo "��";else echo "���";?></b></div>
<br><img src='images/btm_left.png' width=199 height=1></td>
</tr>
<tr>
<td><br></td>
</tr>
<tr>
<td width=199 height=25 style='vertical-align:middle; background-image: url("images/top_left.png");'>&nbsp;&nbsp;<b style='color: #215DC6;'>����������</b></td>
</tr>
<tr>
<td width=199 style='text-align:justify; background-image: url("images/bkg_left.png");' nowrap=nowrap>
<div style='padding-left:10px;'>
<br><img src='images/menu/howtouse.png' width=16 height=16>&nbsp;<a href='FAQ.html' target=_blank class=menu>��� ������������ PhpFM</a>
</div>
<br><img src='images/btm_left.png' width=199 height=1></td>
</tr>
<tr>
<td><br></td>
</tr>
<tr>
<td width=199 height=25 style='vertical-align:middle; background-image: url("images/top_left.png");'>&nbsp;&nbsp;<b style='color: #215DC6;'>���������</b></td>
</tr>
<tr>
<td width=199 style='text-align:justify; background-image: url("images/bkg_left.png");' nowrap=nowrap>
<div style='padding-left:10px;'>
<br>
<input type=checkbox name='show_dirsize' value='yes' id='show_dirsize'<?if($_SESSION['show_dirsize']==true) echo " checked";?>> <label for='show_dirsize'>������ �����</label> [<a href='javascript:window_open("help.php?dirsize","help",400,300);' style='font-weight:bold;color:#215DC6;'>?</a>]
<br><input type=checkbox name='thumbnails' value='yes' id='thumbnails'<?if($_SESSION['thumbnails']==true) echo " checked";?>> <label for='thumbnails'>&laquo;������ �������&raquo;</label> [<a href='javascript:window_open("help.php?thumbnails","help",400,300);' style='font-weight:bold;color:#215DC6;'>?</a>]
<br><input type=checkbox name='show_time' value='yes' id='show_time'<?if($_SESSION['show_time']==true) echo " checked";?>> <label for='show_time'>���������� ����������</label> [<a href='javascript:window_open("help.php?show_time","help",400,300);' style='font-weight:bold;color:#215DC6;'>?</a>]
<br><input type=checkbox name='use_gzip' id='use_gzip' value='yes'<?if($_SESSION['use_gzip']==true) echo " checked";?>> <label for='use_gzip'>Gzip-������</label> [<a href='javascript:window_open("help.php?use_gzip","help",400,300);' style='font-weight:bold;color:#215DC6;'>?</a>]
<br><br>
<input type=submit class=halfbutton name=submitted value='��������'>
</div>
<br><img src='images/btm_left.png' width=199 height=1></td>
</tr>
<tr>
<td><br></td>
</tr>
<tr>
<td width=199 height=25 style='vertical-align:middle; background-image: url("images/top_left.png");'>&nbsp;&nbsp;<b style='color: #215DC6;'>�������������</b></td>
</tr>
<tr>
<td width=199 style='text-align:justify; background-image: url("images/bkg_left.png");' nowrap=nowrap>
<div style='padding-left:10px;'>
<br><img src='images/menu/more.png' width=16 height=16>&nbsp;<a href='' onclick='window_open("index.php?act=additional","additional",640,480); return false;' class=menu>�������������</a>
<?if(isset($_SESSION['logined'])) { ?><br><img src='images/menu/admin.png' width=16 height=16>&nbsp;<a href='' onclick='window_open("index.php?act=administration","administration",640,480);return false;' class=menu>�����������������</a><? } ?>
<br><img src='images/menu/search.png' width=16 height=16>&nbsp;<a href='' onclick='window_open("index.php?act=search","search",640,480); return false;' class=menu>�����</a>
</div>
<br><img src='images/btm_left.png' width=199 height=1></td>
</tr>
<tr>
<td><br><br></td>
</tr>
<tr>
<td width=199 height=25 style='vertical-align:middle; background-image: url("images/top_left.png");'>&nbsp;&nbsp;<b style='color: #215DC6;'>��������</b></td>
</tr>
<tr>
<td width=199 style='text-align:justify; background-image: url("images/bkg_left.png");' nowrap=nowrap>
<div style='padding-left:10px;'>
<br><iframe src="index.php?act=info" width=180 height=80 border=0 frameborder=0 scrolling='no' marginheight=0 marginwidth=0 id='info' style='background-color: #D1DBF6;'></iframe>
</div>
<br><img src='images/btm_left.png' width=199 height=1></td>
</tr>
<tr>
<td><br></td>
</tr>
</form>
</table>
</td>
<td width=510>
<table border=0 width=510 cellspacing=0 cellpadding=0>
<?
$phpfm['realpath']=realpath($phpfm['dir']);
//**
$phpfm['dirs']=array();
$phpfm['files']=array();
$phpfm['counter']=0;
//**
if(@$phpfm['dh']=opendir($phpfm['dir']))
{
	while(($phpfm['file']=readdir($phpfm['dh']))!==false)
	{
		if($phpfm['file']!="." && $phpfm['file']!="..")
		{
			$phpfm['fullpath']=$phpfm['realpath']."/".$phpfm['file'];
			if(is_dir($phpfm['fullpath'])) $phpfm['dirs'][$phpfm['file']]=$phpfm['fullpath'];
			else  $phpfm['files'][$phpfm['file']]=$phpfm['fullpath'];
			$phpfm['counter']++;
		}
	}
	//**
	if($_SESSION['thumbnails']==false)
	{
		?><tr><td colspan=3><img src='images/types.png' width=491 height=18></td></tr>
<?		
		$i=0;
		foreach($phpfm['dirs'] as $key=>$value)
		{
			$i++;
			?>
			<tr>
			<td width=340 valign=top><a class=black2 id='<?=$i?>' href='' ondblclick='window.location="index.php?dir=<?=rawurlencode($key)?>&act=open"' onclick='return false;' onmousedown='return cm(this,event,"");' onmouseover='bl=false;' onmouseout='bl=true;' title='�����'><img src='images/folder.png' alt='�����' width=16 height=16 border=0>&nbsp;<?=$key?></a></td>
			<td width=70 valign=top class=comment2 style='text-align:right;'><?if($_SESSION['show_dirsize']==true) echo unisize($value,'dir');else echo "&nbsp;";?>&nbsp;&nbsp;</td>
			<td width=100 valign=top class=comment2>&nbsp;�����</td>
			</tr>
<?			
		}
		//**
		foreach($phpfm['files'] as $key=>$value)
		{
			$i++;
			$phpfm['pathinfo']=pathinfo($value);
			if(strlen(@$phpfm['extension']=$phpfm['pathinfo']['extension'])>10) $phpfm['extension']=substr($phpfm['extension'],0,7).'...';
			$phpfm['desc']=(isset($desc[$phpfm['extension']]) ? $desc[$phpfm['extension']] : '���� "'.$phpfm['extension'].'"');
			?>
			<tr>
			<td width=340 valign=top><a class=black2 id='<?=$i?>' href='' ondblclick='window_open("index.php?file=<?=rawurlencode($key)?>&act=edit","edit",640,480)' onclick='return false;'  onmousedown='return cm(this,event,"2");' onmouseover='bl=false;' onmouseout='bl=true;' title='����'><img src='images/file-<?=$phpfm['extension']?>.png' alt='����' width=16 height=16 border=0>&nbsp;<?=$key?></a></td>
			<td width=70 valign=top class=comment2 style='text-align:right;'><?=unisize($value,'file')?>&nbsp;&nbsp;</td>	
			<td width=100 valign=top class=comment2><?=$phpfm['desc']?></td>
			</tr>
<?			
		}
	}else
	{
		
		foreach($phpfm['dirs'] as $key=>$value)
		{
		?>
		<tr>
		<td width=475 valign=top colspan=5><a class=black2 href='' ondblclick='window.location="index.php?dir=<?=rawurlencode($key)?>&act=open"' onclick='return false;' onmousedown='return cm(this,event,"");' onmouseover='bl=false;' onmouseout='bl=true;'  title='�����'><img src='images/folder.png' alt='�����' width=16 height=16 border=0>&nbsp;<?=$key?></a></td>
		</tr>
<?		}
		echo "<tr>";
		$phpfm['tr']=0;
		//**
		foreach($phpfm['files'] as $key=>$value)
		{
			if($phpfm['tr']>=5) { echo "</tr><tr>"; $phpfm['tr']=0; }
			//**
			$phpfm['tr']++;
			?>
			<td width=95 class='center'><a class=black2 href='' onclick='return false;' ondblclick='window_open("index.php?file=<?=rawurlencode($key)?>&act=edit","edit",640,480)' onmousedown='return cm(this,event,"2");' onmouseover='bl=false;' onmouseout='bl=true;' title='<?=$key?>'>
			<table width=90 border=1><tr><td class='center' height=67>&nbsp;<img src='index.php?act=thumb&file=<?=rawurlencode($key)?>' border='0'>&nbsp;</td></tr></table>
			<?if(strlen($key)>16) echo substr($key,0,13)."...";else echo $key;?></a>
			</td>
<?		}
	}
}else
{
	?><tr><td>�� ������� ������� �����. <? if(!empty($php_errormsg)) echo '�������: '.$php_errormsg; echo '</td></tr>';
}
?></tr>
</table>
</td>
<td width=4 background='images/right_menu.png'>
</td></tr>
<tr>
<td colspan=3 width=740 height=29 background='images/bottom_menu.png'><span class=comment style='position:relative;top:8;left:5'>��������: <?=$phpfm['counter']?></span></td>
</tr></table>
<?
if($phpfm['gzip']) $phpfm['gzip_en']="��������";else $phpfm['gzip_en']="���������";
$timer->stop();if($_SESSION['show_time']==true) echo "<div align=center style='align:center'>[ ����� ������������� ������� : ".$timer->elapsed()." ��� ] :: [ gzip-������ ".$phpfm['gzip_en']." ]</div>";?>
</body>
</html>
