<?
$login="login"; //�����
$password="wbef663jp"; //������
//**
//�����, ������� ���� ������������ ������� �� �������
//**
$phpfm['ip']=empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? (@$_SERVER['REMOTE_ADDR']) : ($_SERVER['HTTP_X_FORWARDED_FOR']);
//**
if(isset($_POST['login']) && isset($_POST['password']))
{
	if($_POST['login']==$login && $_POST['password']==$password)
	{
		//�������� ������ ������ ��������������
		//**
		$_SESSION['logined']=true;
		$_SESSION['ip']=$phpfm['ip'];
		//**
		setcookie("login",$login);
		setcookie("pass",md5($password));
	}
	
	if(UserPassIsValid($_POST['login'], $_POST['password']))
	{
		$_SESSION['logined_as_user']=true;
		$_SESSION['user_name']=$_POST['login'];
		CreateCounter($_POST['login']);
	}
	
	//**
	Header("Location: ".$phpfm['php_self']."?".SID);
}
//**
if(!isset($_SESSION['logined']) && !isset($_SESSION['logined_as_user']))
{
	?><form action='<?=$phpfm['php_self']?>' method=post>
	<input type=text name=login value='login'>
	<br><input type=password name=password value='password'>
	<br><br><input type=submit value='�����'>
	</form>
<?	die();
	exit;
}else if(!isset($_SESSION['logined_as_user']))
{
	if($phpfm['ip'] != $_SESSION['ip'] || @$_COOKIE['login'] != $login || @$_COOKIE['pass'] != md5($password))
	{
		echo "��������� ������ �� �������������������� �������. ���������� ����� ��� ���";
		//**
		session_destroy();
		session_write_close();
		//**
		die();
		exit;
	}
}
//**
unset($login); #�� �������� ����� �������������
unset($password);
?>