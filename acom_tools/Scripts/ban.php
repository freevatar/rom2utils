<?
$BANDIR="/Games/Allods/flags/ban";
$UNBANDIR="/Games/Allods/flags/unban";
$FLAGSDIR="/Games/Allods/flags";
if($REQUEST_METHOD=="POST" && $login)
{
  switch($submit)
  {
    case "��������":
      $fp=fopen("$BANDIR/$login", "w");
      fwrite($fp, "$login; $name; $cause; $srok");
      fclose($fp);
      $fp=fopen("$FLAGSDIR/ban.sem","w");
      fclose($fp);
	sleep(10);
      $FN="$FLAGSDIR/result/ban.$login";
      if (file_exists($FN)) {
	print "<font color=red>";
	readfile($FN);
	print "</font>";
	unlink($FN);
	} else {
	
	print "<font color=red>����������� ������</font>";
	}
      break;

    case "���������":
      $fp=fopen("$UNBANDIR/$login", "w");
      fwrite($fp, "$login; $name; $cause");
      fclose($fp);
      $fp=fopen("$FLAGSDIR/unban.sem","w");
      fclose($fp);
	sleep(10);
      $FN="$FLAGSDIR/result/unban.$login";
      if (file_exists($FN)) {
	print "<font color=red>";
	readfile($FN);
	print "</font>";
	unlink($FN);
	} else {
	
	print "<font color=red>����������� ������</font>";
	}
      break;

  }
}


?>
<form method="post">
<table borders=0 cellspacing=0 width=400>
<tr bgcolor=EEEEEE><td width=100>�����</td><td><input size=45 name="login"></td></tr>
<tr bgcolor=DDDDDD><td width=100> ��� �����</td><td><input size=45 name="name"></td></tr>
<tr bgcolor=EEEEEE><td width=100>�������</td><td><input size=45 name="cause"></td></tr>
<tr bgcolor=DDDDDD><td width=100>����</td><td><input size=45 name="srok"></td></t240686432r>
<tr bgcolor=EEEEEE><td align = right colspan=2> <font size="-1">����� ������ ���������� - 10 ������ &nbsp&nbsp</font> </td></tr>
<tr bgcolor=DDDDDD><td align=right colspan=2><input name="submit" type="submit" value="��������">&nbsp<input name="submit" type="submit" value="���������"></td></tr>
</form>
</table>