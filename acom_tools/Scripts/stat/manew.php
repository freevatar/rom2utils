<?php
$tempo = '';
for($i = '0'; $i != '16'; $i++)
{
       if(file_exists('C:\\Games\\Allods\\PlScript\\test\\monitoradm-'.$i.'.txt')) $tempo .= file_get_contents('C:\\Games\\Allods\\PlScript\\test\\monitoradm-'.$i.'.txt');
}
print '<table cellspacing=0 cellpadding=3 border=1 width =100%>
<header><br></header>
<tr align=center valign=middle>
<td>�</td><td>��� �������</td><td>�����</td><td>���������</td><td>������</td><td>��� ����</td><td>���������� �������</td>
</tr>
<tr></tr>'.$tempo.'</table>';
?>   
 
