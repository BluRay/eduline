
//效果展现部分
//---------------------------------------------------------------------------------------------------------------------------------------------------------
//个人中心管理

function nav_bar_tab(Nav_bar, Manage_all,sel_cent_r)
{
	var oNavBar=getByClass(document,Nav_bar)[0];
	var aBtn=getByTagName(oNavBar,'li');
	var oManageAll=getByClass(document, Manage_all); 
	for(var i=0; i<aBtn.length; i++)
	{
		aBtn[i].index=i;
		aBtn[i].onclick=function()
		{
			for(var i=0; i<aBtn.length; i++)
			{
				aBtn[i].className='';
				oManageAll[i].style.display='none';
			}
			this.className=sel_cent_r;
			oManageAll[this.index].style.display='block';
		}
	}
}
//个人中心左侧菜单
function home_titlista()
{
	  var oCent_l_box_a=getByClass(document,'cent_l_box_a')[0];	
	 
	  var aTitlista=getByClass(oCent_l_box_a,'tit_list_a');
	  for(var i=0; i<aTitlista.length; i++)
	  {
		   titlista(aTitlista[i]);
	  }
	  function  titlista(aTitlista)
	  {
		   aTitlista.onclick=function ()
			{
				
				var oNext=this.nextElementSibling || this.nextSibling ;
				
				var aLi=oNext.children;
				var h=aLi[0].offsetHeight*aLi.length;
				
				if(oNext.style.height=='0px')
				{
					$(oNext).stop().animate({height:h});
				}
				else
				{
					$(oNext).stop().animate({height:0});
				}
			}
	  }
}
//绑定帐号下的 显示 隐藏
function oIntrodu_a(aLi)
{
	aLi.onmouseover=function()
	{
		var oIntroduction=getByClass(this,'Introduction')[0];
		
		oIntroduction.style.display='block';
	}
	aLi.onmouseout=function()
	{
		var oIntroduction=getByClass(this,'Introduction')[0];
		
		oIntroduction.style.display='none';
	}
	aLi.onmousedown=function()
	{
		return false;
	}
}
//问答 笔记 显示 隐藏
function oA_none(oAttention)
{
	oAttention.onclick=function()
	{
		this.parentNode.parentNode.style.display='none';
	}
}
