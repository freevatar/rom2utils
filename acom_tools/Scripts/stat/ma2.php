<?
 $monitor="/Games/Allods/PlScript/monitoradm.txt";
?>
<table cellspacing=0 cellpadding=3 border=1 width =100%>
<header><br></header>
<tr align=center valign=middle>
 <td>�</td><td>��� �������</td><td>�����</td><td>���������</td><td>������</td><td>��� ����</td><td>���������� �������</td>
</tr>
<tr></tr>
<?
 if ($fp = @fopen($monitor, "r"))
   {
    fpassthru($fp);
   }
   else
   {
     print("������ ����������� ��������� ����");
   }

?>
</table>
