<?php
if($_SERVER["REQUEST_METHOD"] == 'POST' && empty($_POST['lgn']) == FALSE)
{
$lgn = $_POST['lgn'];
$lgn = trim($lgn);
$file = "c:/Games/Allods/Chr/".$lgn[0]."/".$lgn.".lgn" ;
$shell = "unlock.exe c:/Games/Allods/Chr/".$lgn[0]."/".$lgn.".lgn" ;
exec($shell, $out, $return);
echo htmlspecialchars(convert_cyr_string(implode("\n",$out),'d','w'));
if(!$return)
{
print "<font color=green><b>����� ������� �������������</b></font>";
}
else
{
print "<font color=red><b>������ ��� ������������� ������</b></font>";
}
echo "<br>";
print $file;
echo "<hr>";
}
?>
<form method="post">
�����<br>
<input name="lgn"><br>
<input name="submit" type="submit" value="��������������">
</form>