<?
$FLAGSDIR="/Games/Allods/flags";
if($REQUEST_METHOD=="POST")
{
  switch($submit)
  {
    case "�������� ������ ���������":

      $fp=fopen("$FLAGSDIR/accounts.sem","w");
      fclose($fp);
	print "<font color=red>������ ������ ���� ����� � ������� ����� ������</font>";
      break;
  }
}


?>
<form method="post">
<input name="submit" type="submit" value="�������� ������ ���������">
</form>
</table>