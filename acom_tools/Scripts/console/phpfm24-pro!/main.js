var theFile_global;
var menu_opened = false;
var thelast_el;
var filename;
var typing;
var bl=false;
var KEY_UP=38;
var KEY_DOWN=40;
var KEY_ENTER=13;
var KEY_BACKSPACE=8;
var KEY_DELETE=46;
var KEY_F2=113;
var elems=new Array();
var filenames=new Array();

function cm(el,event,type)
{
	if(el) cl(el,event);
	if(event.button==3 || event.button==2)
	{
		eval("OpenMenu"+type+"(filename,event)");
		return false;
	}
}

function cl(el,event)
{
	//alert(event.ctrlKey);
	if(thelast_el && !event.ctrlKey && !elems[el.id]) uncl(event);
	//else event.returnValue=false;
	el.style.background="#00126C";el.style.color="white";el.hideFocus=true;el.focus();
	thelast_el=el;
	//**
	if(el.title.length<6)
	{
		if(el.innerText) filename=el.innerText.substr(1);
		else filename=el.innerHTML.substr(el.innerHTML.indexOf('&nbsp;')+6);
	}
	else filename=el.title;
	//**
	elems[el.id]=el;
	filenames[el.id]=filename;
	//alert(filename);
	el2=document.getElementById("info");
	setTimeout('el2.src="index.php?act=info&file="+filename',200);
}

function uncl(event)
{
	//alert('called');
	el=thelast_el;
	if(event.ctrlKey || event.shiftKey)
	{
		event.returnValue=false;
		return false;
	}
	if(!el) return false;
	el.style.background="white";el.style.color="black";
	for (k in elems)
	{
		el=elems[k];
		el.style.background="white";el.style.color="black";
		delete elems[k];
		delete filenames[k];
	}
}

function kd(el,event)
{
	if(typing) return false;
	//alert(event.keyCode);
	//**
	if(event.keyCode==KEY_UP)
	{
		event.returnValue=false;
		if(!el) id=2;
		else id=el.id;
		if(id==1) return false;
		cl(document.getElementById(id-1),event);
		return false;
	}
	
	if(event.keyCode==KEY_DOWN)
	{
		event.returnValue=false;
		if(!el) id=0;
		else id=el.id;
		el2=document.getElementById(id-(-1));
		if(!el2) return false;
		cl(el2,event);
		return false;
	}
	
	if(event.keyCode==KEY_ENTER && !event.altKey)
	{
		event.returnValue=false;
		el.ondblclick();
		return false;
	}
	
	if(event.keyCode==KEY_ENTER && event.altKey)
	{
		event.returnValue=false;
		if(thelast_el.title!='�����') window.open('index.php?file=' + filename + '&act=properties','properties','width=400,height=480,resizeable=0,menubar=0,location=0,scrollbars=0,toolbar=0,status=0');
		else  window.open('index.php?dir=' + filename + '&act=properties','properties','width=400,height=480,resizeable=0,menubar=0,location=0,scrollbars=0,toolbar=0,status=0');
		return false;
	}
	
	if(event.keyCode==KEY_BACKSPACE)
	{
		event.returnValue=false;
		window.location.href='index.php?act=open&dir=..';
		return false;
	}
	
	if(event.keyCode==KEY_DELETE)
	{
		event.returnValue=false;
		theFile_global=filename;
		//**
		filename1='';
		i=0;
		for(k in filenames)
		{
			i++;
			filename1+=filenames[k]+':';
		}
		//**
		if(i>1) delete_all(i,filename1);
		else
		{
			if(thelast_el.title!='�����') delete_file();
			else delete_folder();
		}
		return false;
	}
	
	if(event.keyCode==KEY_F2)
	{
		event.returnValue=false;
		theFile_global=filename;
		if(thelast_el.title!='�����') rename_file();
		else rename_folder();
		return false;
	}
	
	return true;
}

function OpenMenu(theFile,event)
{
	i=0;
	for(k in filenames) i++;
	if(i>1)
	{
		OpenMenuBig(event);
		return false;
	}
	closeMenu();
	theFile_global=theFile;
	menu_opened = true;
	var el, x, y, login, login2;
	el = document.getElementById("oMenu");
	x = event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft - 3;
	y = event.clientY + document.documentElement.scrollTop + document.body.scrollTop;

	if (event.clientY + 104 > document.body.clientHeight) { y-=100 } else { y-=2 }
	event.returnValue=false;

	el.innerHTML = 
	'<a href="index.php?dir=' + theFile + '&act=open" class=black title="������� ����� \'' + theFile + '\'" onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;<b>�������</b></a>'+
	'<a href="javascript:rename_folder();" class=black title="������������� ����� \'' + theFile + '\'" onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;�������������</a>'+
	'<a href="index.php?dir=' + theFile + '&act=copy" class=black title="���������� ����� \'' + theFile + '\'" onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;����������</a>'+
	'<a href="javascript:delete_folder();" class=black title="������� ����� \'' + theFile + '\'" onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;�������</a>'+
	'<hr size=1>'+
	'<a href="#" onclick="window.open(\'index.php?dir=' + theFile + '&act=properties\',\'properties\',\'width=400,height=480,resizeable=0,menubar=0,location=0,scrollbars=0,toolbar=0,status=0\');return false;" class=black title="���������� �������� ����� \'' + theFile + '\'" onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;��������</a>'+
	'';

	el.style.left = x + "px";
	el.style.top  = y + "px";
	if(el.filters) el.filters["BlendTrans"].Apply();
	el.style.visibility = "visible";
	if(el.filters) el.filters["BlendTrans"].Play();
}

function OpenMenu2(theFile,event)
{
	i=0;
	for(k in filenames) i++;
	if(i>1)
	{
		OpenMenuBig(event);
		return false;
	}
	closeMenu();
	menu_opened = true;
	var el, x, y, login, login2;
	el = document.getElementById("oMenu");
	var o = event.srcElement;
	x = event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft - 3;
	y = event.clientY + document.documentElement.scrollTop + document.body.scrollTop;
	theFile_global=theFile;
	
	if (event.clientY + 100 > document.body.clientHeight) { y-=114 } else { y-=2 }
	event.returnValue=false;

	el.innerHTML =
	'<a onclick="window.open(\'index.php?file=' + theFile + '&act=edit\',\'edit\',\'width=640,height=480,resizeable=0,menubar=0,location=0,scrollbars=0,toolbar=0,status=0\');return false;"  href="#" onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" class=black title="������������� ���� \'' + theFile + '\'">&nbsp;&nbsp;&nbsp;&nbsp;<b>�������������</b></a>'+
	'<a href="javascript:rename_file();" class=black title="������������� ���� \'' + theFile + '\'"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;�������������</a>'+
	'<a href="index.php?file=' + theFile + '&act=copy" class=black title="���������� ���� \'' + theFile + '\'"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;����������</a>'+
	'<a href="javascript:delete_file();" class=black title="������� ���� \'' + theFile + '\'"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;�������</a>'+
	'<a href="index.php?file=' + theFile + '&act=download" class=black target=_blank title="������� ���� \'' + theFile + '\' ����� phpFM"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;�������</a>'+
	'<hr size=1>'+
	'<a href="#" onclick="window.open(\'index.php?file=' + theFile + '&act=properties\',\'properties\',\'width=400,height=480,resizeable=0,menubar=0,location=0,scrollbars=0,toolbar=0,status=0\');return false;" class=black title="���������� �������� ����� \'' + theFile + '\'"  onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;��������</a>'+
	'';

	el.style.left = x + "px";
	el.style.top  = y + "px";
	if(el.filters) el.filters["BlendTrans"].Apply();
	el.style.visibility = "visible";
	if(el.filters)  el.filters["BlendTrans"].Play();
}

function OpenMenuBig(event)
{
	filename1='';
	i=0;
	for(k in filenames)
	{
		i++;
		filename1+=filenames[k]+':';
	}
	closeMenu();
	menu_opened = true;
	var el, x, y, login, login2;
	el = document.getElementById("oMenu");
	x = event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft - 3;
	y = event.clientY + document.documentElement.scrollTop + document.body.scrollTop;

	if (event.clientY + 34 > document.body.clientHeight) { y-=30 } else { y-=2 }
	event.returnValue=false;

	el.innerHTML = 
	'<a href="index.php?all=' + filename1 + '&act=copy" class=black title="���������� ��������" onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;<b>����������</b></a>'+
	'<a href="javascript:delete_all('+i+',&quot;'+filename1+'&quot;);" class=black title="������� ��������" onmouseover="window.status=this.title;return true;" onmouseout="window.status=\'\';return true;" >&nbsp;&nbsp;&nbsp;&nbsp;�������</a>'+
	'';

	el.style.left = x + "px";
	el.style.top  = y + "px";
	if(el.filters) el.filters["BlendTrans"].Apply();
	el.style.visibility = "visible";
	if(el.filters) el.filters["BlendTrans"].Play();
}

function black(el)
{
	el.style.bgcolor='blue';
}

function cMenu() {
  document.getElementById("oMenu").style.visibility = "hidden";
  document.getElementById("oMenu").style.top="0px";
  top.frames['bottom'].window.document.F1.text.focus();
}

function closeMenu(event) {
  if (window.event && window.event.toElement) {
    var cls = window.event.toElement.className;
    if (cls=='menuItem' || cls=='menu') return;
  }
  document.getElementById("oMenu").style.visibility = "hidden";
  document.getElementById("oMenu").style.top="0px";
  return false;
}

function rename_folder()
{
	var theFile2=prompt("������� ����� ��� �����",theFile_global);
	if(theFile2) window.location.href="index.php?file=" + theFile2 + "&file2=" + theFile_global + "&act=rename";
}

function make_folder()
{
	var theFile2=prompt("������� ����� ��� �����","����� �����");
	if(theFile2) window.location.href="index.php?act=formenu&subact=mkdir&dir=" + theFile2;
}

function make_file()
{
	var theFile2=prompt("������� ����� ��� �����","����� ����");
	if(theFile2) window.location.href="index.php?act=formenu&subact=mkfile&file=" + theFile2;
}

function rename_file()
{
	var theFile2=prompt("������� ����� ��� �����",theFile_global);
	if(theFile2) window.location.href="index.php?file=" + theFile2 + "&file2=" + theFile_global + "&act=rename";
}

function delete_file()
{
	
	if(confirm("�� �������, ��� ������ ������� ���� '" + theFile_global + "' ?\n���� �� ���������� � �������, ������� ������ ������������ ����� ������ ������.\n��� ����� ���������� ?"))
	{
		window.location.href="index.php?file=" + theFile_global + "&act=delete";
	}
}

function delete_all(i,fnames)
{
	
	if(confirm("�� �������, ��� ������ ������� ��� ��� �������� ("+i+" ����) ?\n��� �� ���������� � �������, ������� ������ ������������ ����� ������ ������.\n��� ����� ���������� ?"))
	{
		window.location.href="index.php?all=" + fnames + "&act=delete";
	}
}

function delete_folder()
{
	if(confirm("�� �������, ��� ������ ������� ����� '" + theFile_global + "' ?\n����� �� ���������� � �������, ������� ������ ������������ ����� ������ ������.\n��� ����� ���������� ?"))
	{
		window.location.href="index.php?dir=" + theFile_global + "&act=delete";
	}
}

function window_open(src, name, width, height)
{
	window.open(src, name, 'width=' + width + ',height= ' + height + ',resizeable=0,menubar=0,location=0,scrollbars=1,toolbar=0,status=0');
}