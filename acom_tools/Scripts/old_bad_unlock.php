<?
if($REQUEST_METHOD=="POST" && $login)
{
        $login = $_POST['login'];  //� ��� ��� ������� ���� ������? ��
	$a = "/0";
//	$file = "C:/Allods2/Hat/Chr/".$login[0]."/".$login.".lgn"; 
//	print $file;
	$file = "D:/Games/Allods/Chr/".$login[0]."/".$login.".lgn" ;
        $fp = fopen($file, "rb+");
        if ($fp == 0)
        {
         echo "�� ���� ������� ���� ".$file;
         exit;
        }
        print $file;
        //fpassthru ($fp);
	//print "<br>";
	fseek($fp, 456);
	fwrite($fp, $a, 1);

	fclose($fp);
}
?>
<form method="post">
�����<br>
<input name="login"><br>
<input name="submit" type="submit" value="��������������">
</form>