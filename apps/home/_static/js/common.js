
//效果展现部分
//---------------------------------------------------------------------------------------------------------------------------------------------------------
//个人中心管理
function omanage_all(oManage_all)
{
	
	var oManage=getByClass(oManage_all,'Manage')[0];
	var oBoxGld=getByClass(oManage_all,'box_gl_d');
	//.box_gl_d:hover .box_gl{display:block;}
	
	if(oManage.innerHTML=='管理')
	{
		for(var i=0; i<oBoxGld.length; i++)
		{
			var oBoxGl=getByClass(oBoxGld[i],'box_gl')[0]; 
			
			var oCloseCent=getByClass(oBoxGld[i],'close_cent')[0];
			oCloseCent.style.display='none';
			if(oBoxGl)
			{
				oBoxGl.style.display='none';
				oBoxGld[i].onmouseover=function()
				{
					var oBoxGl=getByClass(this,'box_gl')[0]; 
					oBoxGl.style.display='block';
				}
				oBoxGld[i].onmouseout=function()
				{
					var oBoxGl=getByClass(this,'box_gl')[0]; 
					oBoxGl.style.display='none';
				}
			}
		}
	}
	oManage.onclick=function()
	{
		
		if(this.innerHTML=='完成')
		{
			this.innerHTML='管理';
			for(var i=0; i<oBoxGld.length; i++)
			{
			    oBoxGld[i].onmouseover=null;
		        oBoxGld[i].onmouseout=null;
				var oBoxGl=getByClass(oBoxGld[i],'box_gl')[0]; 
				
				var oCloseCent=getByClass(oBoxGld[i],'close_cent')[0]; 
				oCloseCent.style.display='none';
				if(oBoxGl)
				{
					oBoxGl.style.display='none';
					oBoxGld[i].onmouseover=function()
					{
						var oBoxGl=getByClass(this,'box_gl')[0]; 
						oBoxGl.style.display='block';
					}
					oBoxGld[i].onmouseout=function()
					{
						var oBoxGl=getByClass(this,'box_gl')[0]; 
						oBoxGl.style.display='none';
					}
				}
			}
		}
		else
		{
			this.innerHTML='完成';
			for(var i=0; i<oBoxGld.length; i++)
			{
				oBoxGld[i].onmouseover=null;
		        oBoxGld[i].onmouseout=null;
				//var oBoxGl=getByClass(oBoxGld[i],'box_gl')[0]; 
				
				var oCloseCent=getByClass(oBoxGld[i],'close_cent')[0]; 
				//oBoxGl.style.display='block';
				oCloseCent.style.display='block';
				oCloseCent.onclick=function()
				{
					alert('terteterte');
					this.parentNode.style.display='none';
				}
			}
		}
   }
}
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
//个人心里的 textarea
function home_textarea()
{
	var oTextA=getByClass(document,'text_A')[0];
	var oTextarea=getByTagName(oTextA,'textarea')[0];
	var oLabel=getByTagName(oTextA,'label')[0];
	oTextarea.onfocus=function()
	{
		oLabel.style.display='none';
		oTextA.style.border='solid 1px #85C155';
	}
	oTextarea.onblur=function()
	{
		if(oTextarea.value=='')
		{ 
		  oLabel.style.display='block';
		  oTextA.style.border='solid 1px #CECECE';
		}
	}
	oLabel.onclick=function()
	{
		oTextarea.focus();
	}
}
//支付宝
 function zhifubao()
 {
	 var OZfb_zxzf=getByClass(document,'zfb_zxzf');
	 for(var i=0; i<OZfb_zxzf.length; i++)
	 {
		 Recharge_bar(OZfb_zxzf[i]);
	 }
	function Recharge_bar(OZfb_zxzf)
	{
		 var aA=getByTagName(OZfb_zxzf,'a');
		 for(var i=0; i<aA.length; i++)
		 {
			 aA[i].index=i;
			 aA[i].onclick=function()
			 {
				 for(var i=0; i<aA.length; i++)
				 {
					var oEm=aA[i].children[0];
					oEm.className='';
				 }
				   var oEm=this.children[0];
				   oEm.className='ch_z';
			 }
		 }
	}
 }
 function opening()
 {
	 var oNfBgDiv=getByClass(document,'nf_bg_div')[0];
	 var oNfBg=getByClass(oNfBgDiv,'nf_bg');
	 for(var i=0; i<oNfBg.length; i++)
	 {
		  oNfBg[i].index=i;
		  oNfBg[i].onclick=function()
		  {
			 for(var i=0; i<oNfBg.length; i++)
			 {
				oNfBg[i].className='nf_bg fl';
				var ob=getByTagName(oNfBg[i],'b')[0];
				ob.className='';
				 var ofp=getByClass(oNfBg[i],'f_p')[0];
				// alert(ofp);
				 if(ofp)
				 {
					var chid=getByTagName(ofp,'input')[0];
					
					chid.value='';
				 }
			 }
			 this.className='nf_bg border_bg fl';
			 var ob=getByTagName(this,'b')[0];
			 ob.className='nf_bg_em';
			 var ofp=getByClass(this,'f_p')[0];
			// alert(ofp);
			 if(ofp)
			 {
				var chid=getByTagName(ofp,'input')[0];
				
				chid.value='30元';
			 }
			 
			 
		  }
	 }
 }
function osjl()
{
	var oNavBar=getByClass(document,'Nav_bar')[0];
	var aBtn=getByTagName(oNavBar,'li');
	var oManageAll=getByClass(document, 'Manage_all'); 
	var oYsjl=getByClass(document,'ysjl')[0];
	 
	oYsjl.onclick=function()
	{
		for(var i=0; i<aBtn.length; i++)
		{
			aBtn[i].className='';
			oManageAll[i].style.display='none';
		}
		aBtn[1].className='sel_cent_r';
		oManageAll[1].style.display='block';
	}
}

  //提交表单验证
function  upload_yz()
{
	var oSave=getByClass(document,'Save')[0];	
	var oSaveUpload=getByClass(document, 'Save_Upload')[0];
	var oHeadSearch=getByClass(oSaveUpload, 'upload_input_a');
	var oUploadTextareaA=getByTagName(oSaveUpload, 'textarea')[0];
	var oLearn=getByClass(document,'Learn')[0];
	
	var upload_input_a_next=oHeadSearch[0].nextElementSibling || oHeadSearch[0].nextSibling ;
	var upload_input_b_next=oHeadSearch[1].nextElementSibling || oHeadSearch[1].nextSibling ;
	var oUploadTextareaA_next=oUploadTextareaA.nextElementSibling || oUploadTextareaA.nextSibling ;
	//alert(oUploadTextareaA.value);
	//oLearn.value = oLearn.value.replace(/^(0|[1-9]\d\d|1000)$/g,''); //去除HTML tag
	var oLea_value=/^(0|[1-9]\d\d|1000)$/g;
	var oTopicsUp=getByClass(document,'topics_up')[0];
	var oB=getByTagName(oTopicsUp,'b')[0];
	
	oSave.onclick=function()
	{
		removal_value(oHeadSearch[0]);
		removal_value(oHeadSearch[1]);
		removal_value(oUploadTextareaA);
		removal_value(oLearn);
		
		var arr=['他妈的', '你妈的', '傻×', '衮蛋', '你妹儿'];
		
		for(var i=0; i<arr.length; i++)
		{
			if(oHeadSearch[0].value.indexOf(arr[i])>-1)
			{
				tag('专辑标题内容不得包含敏感词','transparent');
				return false;
			}
			if(oHeadSearch[1].value.indexOf(arr[i])>-1)
			{
				tag('标签内容不得包含敏感词','transparent');
				return false;
			}
			if(oUploadTextareaA.value.indexOf(arr[i])>-1)
			{
				tag('简介内容不得包含敏感词','transparent');
				return false;
			}
			if(oLearn.value.indexOf(arr[i])>-1)
			{
				tag('价格内容不得包含敏感词','transparent');
				return false;
			}
		}
		if(oHeadSearch[0].value=='')
		{
			tag('专辑标题内容不得为空','transparent');
			return false;
		}
		else if(oUploadTextareaA.value=='')
		{
			
			tag('简介内容不得为空','transparent');
			return false;
		}
		else if(oLearn.value=='')
		{
			tag('请填写价格','transparent');
			return false;
		}
		else if(!oLea_value.test(oLearn.value))
		{
			tag('请输入0-1000的整数','transparent');
			return false;
		}
		else if(oB.className=='Text_error')
		{
			tag('请勾选高教网课程分成协议','transparent');
			return false;
		}
		else if(oHeadSearch[1].value=='')
		{
			tag('标签内容不得为空','transparent');
			return false;
		}
		else
		{
			oHeadSearch[0].value='';
			oHeadSearch[1].value='';
			oUploadTextareaA.value='';
			upload_input_a_next.style.display='block';
			upload_input_b_next.style.display='block';
			oUploadTextareaA_next.style.display='block';
			tag1('保存完成','transparent');
			return false;
		}
	}	   
}
//去除空格 空白 多余行
function removal_value(obj)
{
	obj.value = obj.value.replace(/<\[^>]*>/g,''); //去除HTML tag
	obj.value = obj.value.replace(/[ | ]*\n/g,'\n'); //去除行尾空白
	//str = str.replace(/\n[\s| | ]*\r/g,'\n'); //去除多余空行
	obj.value=obj.value.replace(/&nbsp;/ig, "");
	obj.value=obj.value.replace(/ /ig,'');//去掉 
}
//树形菜单
 function Select()
 {
	 var oProposalBoxA=getByClass(document,'proposal_box_a')[0];
	 $('.proposal_box_a').find('a').click(function(){
		 var ul = $('.selected_con').find('ul').html();
		 ul+='<li><a href="javascript:;"><span>'+$(this).html()+'</span></a><em class="vl5"></em></li>';
		 $('.selected_con').find('ul').html(ul);
	  });
 }