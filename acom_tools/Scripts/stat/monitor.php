<?
 $monitor="/Games/Allods/PlScript/monitor.txt";
?>
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
