
//首页选项卡
function zubox(oXueba)
{
	var oCourseSortIn=getByClass(oXueba,'course_sort_in')[0];
	var aBtn=oCourseSortIn.children;
	
	var oNext=oXueba.nextElementSibling || oXueba.nextSibling ;
	var aZuBox=getByClass(oNext,'zu_box');
	for(var m=0; m<aBtn.length;m++)
	{
		aBtn[m].index=m;
		aBtn[m].onclick=function()
		{
			for(var m=0; m<aBtn.length;m++)
			{
				aBtn[m].className='';
				aZuBox[m].style.display='none';
			}
			this.className='course_ch_in';
			aZuBox[this.index].style.display='block';
		}
	} 
}













//手动提交意见反馈到后台
function ajaxSubmitSuggest($value,$code){
	var status = true;
	var url = U('classroom/Public/dosuggest');
	$.ajax({
		type: "POST",
		url: url,
		data: "value="+$value+'&code='+$code,
		async:false,
		dataType:"JSON",
		success: function(xMLHttpRequest, textStatus, errorThrown){
			mzgaojiaowang.ajaxDone(xMLHttpRequest, textStatus, errorThrown);
			//返回一个状态
			if(xMLHttpRequest.status == 1){
				//成功!
				status = true;
			}else if(xMLHttpRequest.status == 2){
				//失败!
				status = false;
			}else if(xMLHttpRequest.status == 0){
				//错误!
				status = false;
			}
		},
		error:function(xhr, ajaxOptions, thrownError){
			mzgaojiaowang.ajaxError(xhr, ajaxOptions, thrownError);
			status = false;
		}
	});
	return status;
}




//效果展现部分
//---------------------------------------------------------------------------------------------------------------------------------------------------------

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


