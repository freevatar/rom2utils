<?php
/*
(C)
Map List Auto Update Done By:
 _____
|  ___| __ ___  __ _ _ __ ___   ___ _ __
| |_ | '__/ _ \/ _` | '_ ` _ \ / _ \ '__|
|  _|| | |  __/ (_| | | | | | |  __/ |
|_|  |_|  \___|\__,_|_| |_| |_|\___|_|

�������� ������� ������ aka Installero �� ������ � ����.
*/
/*
��� 2007 - ���������� ���������:
1) ������� ��������� �� ������ ������������ ���� hat2.cfg
2) ����������� ���������� �������� � ��������� �������
	(��� �������� ��������� �� ������� ������������)
3) ���� � ������ ������������ �������� ���������� � ���� �������
*/

//-------->

//----------------------------
// ����������� ����������:
//----------------------------

//����� ���������� �������� ��� ��������:
$ServerCount = 15;
//���� � ����� � �������� ���� (��� ������� � URL):
$pathto = "http://forum.allods.net/a2/maps/";

//*		///////(������ ������ ������������)
		/////// ��� ���� ANet:
//������ ��� ����� ������������ ���� (��� ���������� ���� ��������):
$HatCfgFileName = "C:\Games\Allods\hat.cfg";
$HatCfgFileName = "C:\Games\Allods\hat2.cfg";
//������ ��� ���������� ����� ����� ������������ �������
// (��� �������� "{%s}" ����� �������� �� ����� �������):
$SrvCfgFileNamePattern = "C:\Games\Allods\server{%s}\server.cfg";
/*/		/////// ��� �������� ������������ ��������:
//������ ��� ����� ������������ ���� (��� ���������� ���� ��������):
//$HatCfgFileName = "W:\A2\HatAnet\Hat\Hat2.cfg";
//������ ��� ���������� ����� ����� ������������ �������
// (��� �������� "{%s}" ����� �������� �� ����� �������):
//$SrvCfgFileNamePattern = "W:\A2\HatAnet\Srv\Srv{%s}\srv{%s}.cfg";
//*/	///////(����� ������ ������������)

//----------------------------
//��������� ������������ ������� ��������:
//----------------------------
//�� ����� ������������ ���� (������� �� ���������� ������ ��� �������):
//$Servers[<ServerNum>]['id']							- ������������� �������
//$Servers[<ServerNum>]['ip']							- IP-����� �������
//$Servers[<ServerNum>]['name']							- ��� �������
//$Servers[<ServerNum>]['comment']						- ��� �������
//�� ������ ������������ ��������:
//$Servers[<ServerNum>]['maps'][<MapFileName>]['name']	- ��� �����
//$Servers[<ServerNum>]['maps'][<MapFileName>]['time']	- ����� ������� ����� � �����
//$Servers[<ServerNum>]['maps'][<MapFileName>]['alm']	- ��� ����� ����� ��� ����������

//*******************************************************************
//������� ��������� ����� �����:
//*******************************************************************

function getmapname($map, $servernum)
{
        $mapspath = 'C:\Games\Allods\server'."$servernum".'\maps\\';
        $maps = "$mapspath"."$map";
        $name = '';
        global  $name;
        @$content = file_get_contents("$maps");
        $dirty = substr("$content", '108', '35');
        $symb = chr('0');
        $TokenArray = explode($symb, "$dirty"); //type 1
        $name = $TokenArray[0];
        $name = trim($name);
        $b = strlen($name);
        $len = $b - 1;
        for($ServerNum = 0; $b > $ServerNum; $ServerNum++)
        {
                $ord = ord($name[$ServerNum]);
                //
                if ($ord == '32' || $ord == '46') { } //����� ��� ������
                elseif ($ord > '32' && $ord < '63') { } //����� � ����� ���� ; ...
                elseif ($ord > '96' && $ord < '123') { } //a-z
                elseif ($ord > '64' && $ord < '91') { } //A-Z
                else {
                        $name[$ServerNum] = '';
                        }
//              elseif(ord($name[$ServerNum]) < '65' || ord($name[$ServerNum]) > '90' || ord($name[$ServerNum]) < '97' || ord($name[$ServerNum]) > '122'
        }
        if($name != '') { }
        else { $name = $map; if(!$map) { $name = 'Empty map requested.'; } }
        return $name;
}
/**///getmapname("map.alm", "1");
/**///print $Servers['server1']['map'];


//*******************************************************************
//�������� ���������:
//*******************************************************************

if( !file_exists( $HatCfgFileName ) ) {
	die( "File '$HatCfgFileName' does not exists!\n" );
}

$TimeBeg = microtime();

//==============================================================
//������� ���������� � �������� �� ����������������� ����� ����:
//==============================================================

$HatCfgFile = file( "$HatCfgFileName" ) OR die( "Failed to open file " . $HatCfgFileName );

$ServerNum = 0;
foreach( $HatCfgFile as $CfgLine ) {
	$Pos = strpos( $CfgLine, 'server=' );
	if( $Pos !== false ) {
		//������� ������ ��� �������, ���������� ��:
		$ServerNum++;
		//������ ����� ��� 'server=127.0.0.1:8004,2,1.1 Allods.NET Easy Server 01 ;Former Allods.NET 02'
		$TokenArray = split( "[=,;]", $CfgLine );
		//$TokenArray[0] �������� ������ 'server'
		//$TokenArray[1] �������� ����� ������� (�������� '127.0.0.1:8002')
		$Servers['server'.$ServerNum]['ip'] = $TokenArray[1];
		//$TokenArray[2] �������� ����� ������� ('2')
		$Servers['server'.$ServerNum]['id'] = $TokenArray[2];
		//$TokenArray[3] �������� ��� ������� ('1.1 Allods.NET Easy Server 01 ')
		$Servers['server'.$ServerNum]['name'] = trim( $TokenArray[3] );
		//$TokenArray[4] �������� �������������� ����������� ('Former Allods.NET 02')
		$Servers['server'.$ServerNum]['comment'] = chop( $TokenArray[4] );

		if( $ServerNum >= $ServerCount ) {
			break;	//������������ �������� $ServerCount ��������
		}
	}
}
$ServerCount = $ServerNum;

/************************(��� ���������� ����)
for( $ServerNum = 1; $ServerNum <= $ServerCount; $ServerNum++ ) {
	$SrvCfgFileName = str_replace( "{%s}", $ServerNum, $SrvCfgFileNamePattern );
	print $ServerNum . ":\tid =}" . $Servers['server'.$ServerNum]['id'] . "{=\n"
		. "\tname =}" . $Servers['server'.$ServerNum]['name'] . "{=\n"
		. "\tcomment =}" . $Servers['server'.$ServerNum]['comment'] . "{=\n"
		. "\tSrvCfgFileName =}" . $SrvCfgFileName . "{=\n"
	;
}
print_r( $Servers );
exit;
/******************(����� ����������� �����)*/

//================================================================
//������� ���������� � ������ �� ���������������� ������ ��������:
//================================================================

for( $ServerNum = 1; $ServerNum <= $ServerCount ; $ServerNum++ ) {
	$SrvCfgFileName = str_replace( "{%s}", $ServerNum, $SrvCfgFileNamePattern );
	$SrvCfgFile = file( "$SrvCfgFileName" ) OR die( "Failed to open file " . $SrvCfgFileName );
	foreach( $SrvCfgFile as $CfgLine ) {
		//��� ���������� ������ ������, ���������� ���� � ������ ����:
		if( strpos( $CfgLine, 'maps' ) === false ) {
			continue;
		}
		//������ ����� ��� ����: "maps\anq_rev_qg_4_5.alm=1"
		$TokenArray = explode( '\\', $CfgLine );
		//���� � ������ ������ ���������� ����������� - ��������� ��� ������:
		if( strpos( $TokenArray[0], ';' ) !== false ) {
			continue;
		}
		$TokenArray[1] = trim( $TokenArray[1] );	//��� ������ c ���������� ����� ("anq_rev_qg_4_5.alm=1")
		$MapArray = explode( '=', $TokenArray[1] );
		//$MapArray[0] �������� ��� ����� ����� ("anq_rev_qg_4_5.alm"):
		$MapFileName = trim( $MapArray[0] );
		$Servers['server'.$ServerNum]['maps'][$MapFileName]['name'] = getmapname( $MapFileName, $ServerNum );
		//$MapArray[1] �������� ����� ������� ����� � ����� ("1"):
		$Servers['server'.$ServerNum]['maps'][$MapFileName]['time'] = trim( $MapArray[1] );
		//��� ����� ������������ ����� ������ � ������ ��� ����� ��� ����� ��� ����������:
		$NameArray = explode( '.', $MapFileName );
		$Servers['server'.$ServerNum]['maps'][$MapFileName]['alm'] = $NameArray[0];
	}
}
/************************(��� ���������� ����)
print_r( $Servers );
exit;
/******************(����� ����������� �����)*/

$ServerNum = 1;
$fp = fopen( 'mapstable.xml', 'w' );
$output =
"<?xml version=\"1.0\" encoding=\"Windows-1251\"?>
<maplist>
";
fwrite( $fp, $output );

for( $ServerNum = 1; $ServerNum <= $ServerCount ; $ServerNum++ ) {

	fwrite( $fp,"<server><mapcount>");

	//Count how many maps...
	$keyz = array_keys( $Servers['server'.$ServerNum]['maps']);
	$manymaps = count($keyz);
	$manyrows = $manymaps;
	$output = "$manyrows";
	fwrite($fp, $output);
	//
	$output = '</mapcount><servername>';
	fwrite($fp, $output);
	$output = $Servers['server'.$ServerNum]['name'];
	fwrite($fp, $output);
	$output = '</servername>';
	fwrite($fp, $output);
	$mapi = 0;

	//print "$manymaps - manymaps <br>"; //Needed only for debugging...
	for( $ii = 0; $manymaps > $ii; $ii++) {
		$output = '<map><mapurl>';
		fwrite($fp, $output);
		$output = $pathto.$Servers['server'.$ServerNum]['maps'][$keyz[$mapi]]['alm'].".rar'";
		if($output == '') {			 $output = '-'."$manymaps $ServerNum $ii";
			 }
		fwrite($fp, $output);
		$output = '</mapurl><mapname>';
		fwrite($fp, $output);


		$output = $Servers['server'.$ServerNum]['maps'][$keyz[$mapi]]['name']."";
		if($output == '') {
			 $output = '-'."$manymaps $ServerNum $ii";
			 }
		fwrite($fp, $output);


		$output = '</mapname><maptime>';
		fwrite($fp, $output);
		$output = $Servers['server'.$ServerNum]['maps'][$keyz[$mapi]]['time'];
		if($output == '') { $output = '-'; }
		fwrite($fp, $output);
		$output = '</maptime></map>';
		fwrite($fp, $output);
		$mapi++;
	}

	fwrite( $fp,"</server>");
}

fwrite( $fp, "</maplist>" );
fclose( $fp );


//���������� ����� ���������� �������:
$TimeEnd = microtime();
$TimeBeg = explode( ' ', $TimeBeg );
$TimeEnd = explode( ' ', $TimeEnd );
print '<CENTER>Page generated in ' . ( $TimeEnd[0]  - $ts[0] + $TimeEnd[1] - $ts[1] ) . ' sec</CENTER>';

?>