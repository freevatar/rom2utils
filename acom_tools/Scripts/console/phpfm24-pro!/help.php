<html>
<head>
<title>�������</title>
<style type='text/css'>
<!--
BODY,table { background-color:#F7F6F3; color:black; font-family: Verdana; font-size: 8pt; }
TD { text-align:justify; vertical-align:middle; }
.comment2 { font-family: Verdana; font-size: 7pt; color:black }
#multitext { background-color: #F7F6F3; color:#000000; font-family:Courier new, Verdana, Arial; font-size:8pt }
.halfbutton {border-style : outset;width : 95px; height:20 px;}
input,select,button {font-size : 8pt; font-family : Verdana, Arial, sans-serif;color : #000000;vertical-align:bottom}
A {COLOR: black; TEXT-DECORATION: none; font-family: Tahoma; font-size: 8pt;}
A:hover {COLOR: white; TEXT-DECORATION: none;}
-->
</style>
</head>
<body>
<table width=377 border=0 cellpadding=0 cellspacing=0 style='position:absolute; top:3;left:3;'>
<tr>
<td width='100%' height=20 style='background-color:darkblue;color:white;font-weight:bold;' colspan=2>���������� �������</td>
</tr>
<tr>
<td width='100%' colspan=2>
<br>
<?
switch ($_SERVER['QUERY_STRING'])
{
case "dirsize":
	echo "<b>���������� ������ �����</b><br><br>";
	echo "��� ����� �������� � ��������� ����������� �������� �����. ������, ��� ��������� ���� ����� ����� �������� ��������� ������ ��������� ��������� PhpFM.";
	break;
case 'thumbnails':
	echo "<b>����� &laquo;������ �������&raquo;</b><br><br>";
	echo "���� ��� ����� ��������, ������ ������, ���������� ��������, ����� ������������ �� ����������� �����������. ������, ��� PhpFM ���������� ���������� GD, ������� ����� ������������� ".((extension_loaded("gd") || extension_loaded("gd2")) ? " (� ��� ��� ����, ��� ��� ��� �� � ��� ������������)" : "(� ��� � ���, ������������� � ����������)").", � ��� ���������� ����� ����� ��������, ��� ��� �� ��������������� ���� ������������ (�� ������ �������� ������������ ���������� ��� ��������� ������� �����������).";
	break;
case "show_time":
	echo "<b>���������� ����������</b><br><br>";
	echo "���� ��� ����� ��������, ��� ����� ������������ ����������, � ������� �������� ����� ������������� ������� � � ���, �������� �� Gzip-������.";
	break;
case "use_gzip":
	echo "<b>������������ Gzip-������</b><br><br>";
	echo "���� ��������, ���������� �������� ��������� � ������� ��������� ������ Gzip, � ���������� � ������ ���� ��������. ����� �������, �� ������� �������� � 5 ��� ������ ��������, ��� ���� �� ������ �� �����������. ��� �������� ��������� ��� ������ � �������� ������������ (��������� ����� ������), ����� ����������� ������ ����� ���������� 95 %, ��� ���� ������.";
	break;
}
?>
<br><br>
</td>
</tr>
<tr height=20>
<td width='100%' height=20 style='background-color:darkblue;color:white;font-weight:bold;'>Copyright &copy; 2004 PhpFM</td>
<td height=20 style='background-color:darkblue;color:white;font-weight:bold;'><a href='javascript:window.close();' style='color:white;'>[�������]</a>
</tr>
</table>
</body>
</html>