<?php
#
#
#	������ ��� ������������ ������� (lgn)
#
#
#
#
#

#------------ ������������ -------------------
$root="C:\Allods2\Chr\\";          // ���� � ����� Chr
#---------------------------------------------
error_reporting(E_ALL);
$tot='0';
$error='';



if ($FV=@opendir("$root")){     //��������� �������
while(($nfile=readdir($FV)) !==false){          //����� ������� ��� �����
	if ($nfile=="." or $nfile==".."){continue;}  //���������� ���, ���� �����
	if (is_dir($root.'\\'.$nfile) !==false) {   //���� �������, ��
	//	echo 'DIR ';                         //�������� ��� ��������
     //   echo "$nfile<br>\n";

 if ($FX=@opendir("$root".'\\'.$nfile)){   //��������� ���

    		while(($pfile=readdir($FX)) !==false){  //����� ������� ��� ����� �� ��������
				if ($pfile=="." or $pfile==".."){continue;}  //���������� ���, ���� �����
				$pathf=$root.'\\'.$nfile.'\\'.$pfile;
				if (is_dir($pathf) !==true){ //���� �� �������, �� ������� ���������� �����
     				if (stripos($pfile,".lgn") !==false){
    // 					echo "$pfile<br>";   //���� ���������� lgn �� ������

  #------------����-----------
 $error='';

$path=$pathf;  //���� � ������ �� �����



if(empty($error)){					//���� ������ ���, ��
if(!file_exists($path)){            //���� ����� �� ����������
	$error = "lgn_file_not_exists"; // �� ������
}else{                              //�����
    $fp = fopen($path,"r");         //��������� ���� ��� ������
    if($fp){                        //���� ���������� �������, ��
        $contentfile=fread($fp,filesize($path));  //������ ���������� ����� � �������� ������

    	fclose($fp);                //��������� ����


		if(ord($contentfile[456])<>0){      //���� ����� ������������ , ��
		$errloc='was locked!';

		$contentfile[456] = chr (0);  //�������� ���� ������������ ������ (���� �� ��������� 456=0).

	      $fd = fopen($path,"w");   //��������� ���� �� ������
      if($fd){
    	fwrite($fd,$contentfile);       //����� ����
   	fclose($fd);
   	          }else{
  $error = "cannot_open_file";
   }
        					echo "$pfile<br>";   //������
		 $tot=($tot+1);
		}else{
			$errloc='was unlock';
		}

}
}
}
if(empty($error)){
//	echo "ok";
//	echo ($errloc);
}else{
	echo $error;
   echo 'dddddd';
	die();
}

#----------------------------




     					}
     			}
     		}
		closedir ($FX);
		}else{
		echo 'unable to open cat';
		}


    }
	}
	closedir ($FV);
	}else{
		echo 'unable to open cat';
		}

echo "total $tot";

die();



?>