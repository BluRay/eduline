// JavaScript Document
//公用小函数
//---------------------------------------------------------------------------------------------------------
function getById(id)
{
	return document.getElementById(id);
};
function getByTagName(oParent,tagName)
{
	return 	oParent.getElementsByTagName(tagName);
};
function getByClass(oParent, sClass)
{
	if(oParent.getElementsByClassName)
	{
		return oParent.getElementsByClassName(sClass);
	}
	
	var aEle=oParent.getElementsByTagName('*');
	var aResult=[];
	
	var re=new RegExp('\\b'+sClass+'\\b');
	
	for(var i=0;i<aEle.length;i++)
	{
		if(re.test(aEle[i].className))
		{
			aResult.push(aEle[i]);
		}
	}
	return aResult;
};
function getPos(obj)
{
	var l=0;
	var t=0;
	while(obj)
	{
		l+=obj.offsetLeft;
		t+=obj.offsetTop;
		obj=obj.offsetParent;
	}
	return {left:l, top:t};
}
//删除绑定
function removeEvent(obj,sEv,fn)
{
	if(obj.detachEvent){
		obj.detachEvent('on'+sEv,fn);
	}else{
		obj.removeEventListener(sEv,fn,false);
	}
}
//事件绑定的小函数
function addEvent(obj,sEv,fn)
{
	if(obj.attachEvent){
		obj.attachEvent('on'+sEv,fn);
	}else{
		obj.addEventListener(sEv,fn,false);	
	}
}
//可以通过getStyle 获取到width,height,backgroud-color等值
function getStyle(obj,attr){
	if(obj.currentStyle){  //IE
		return obj.currentStyle[attr];
		}else{  //FF,Chrome
		return getComputedStyle(obj,false)[attr];
	}
};

//--------------------------------------------------------------------------------------------------------------------------------
                //效果展示部分
//--------------------------------------------------------------------------------------------------------------------------------

//点击登录注册显示隐藏
function login_up()
{
	var oGolognIn1=document.getElementById('go_logn_in1');
	
	var oGolognIn2=document.getElementById('go_logn_in2');
	var oXxguan=getByClass(document,'xxguan')[0];
	var oSignIn=getByClass(document,'Sign_In')[0];

	oGolognIn2.onclick=function()
	{
		
		oSignIn.style.display='block';
	} 
	oXxguan.onclick=function()
	{
		oSignIn.style.display='none';
	}
}
//邮箱注册和手机号注册切换
function login_show()
{
	var oLonginCom=getByClass(document,'longin_com')[0];
	var oLonginDiv=getByClass(oLonginCom,'longin_div')[0];
	var aBtn=getByTagName(oLonginDiv,'li');
	var oLoginBox=getByClass(oLonginCom,'login_box');
	for(var i=0; i<aBtn.length; i++)
	{
		aBtn[i].index=i;
		aBtn[i].onclick=function()
		{
			for(var i=0; i<aBtn.length; i++)
			{
				aBtn[i].className='boder_r fl';
				oLoginBox[i].style.display='none';
			}
			this.className='boder_r fl email';
			oLoginBox[this.index].style.display='block';
		}
	}
}
//登录注册的每个输入框的模拟
function login_focus()
{
	var aInputDiv=getByClass(document,'input_div');
	for(var i=0; i<aInputDiv.length;i++)
	{
		login(aInputDiv[i]);
	}
	function login(aInputDiv)
	{
		//var oNext=aInputDiv.nextElementSibling || aInputDiv.nextSibling;
		
		var aInput=getByTagName(aInputDiv,'input')[0];
		
		var oSpan=getByTagName(aInputDiv,'span')[0];
		aInput.onfocus=function()
		{
			oSpan.style.display='none';
			aInputDiv.style.border='solid 1px #85C155';
			//oNext.style.display='block';
			
		}
		aInput.onblur=function()
		{
			if(aInput.value=='')
			{
				oSpan.style.display='block';
				aInputDiv.style.border='solid 1px #999';
				//oNext.style.display='none';
			}
		}
		oSpan.onclick=function()
		{
			aInput.focus();
		}
		
	}
}
//登录  单选 忘记我 
function danxuan_bar()
{
	var oTopics=getByClass(document,'topics1')[0]; 
	 oTopics.onclick=function()
	 {
		var oSpan=this.children[0];
		 if(oSpan.className=='Test_True')
		 {
			oSpan.className='Text_error';
		 }
		 else
		 {
			 oSpan.className='Test_True';
		 }
	 }
}
//登录注册 表单验证
function check_from()
{
	$(function(){
   /*
    * 错误消息函数(输入错误信息需要添加span)
    * id 文本框的id msg为提示消息
	*/

   //function errorInfo(id,msg)
   function errorInfo(obj,msg)
   {
	   //判断当前文本框后边是否有span 如果为零 没有span 需要创建span 否则直接修改span里的内容
	
	   if(obj.parents('li').find('div:first').siblings().length==0)
	   {
		   obj.parents('li').find('div:first').after('<span class="fl Prompt m_l_10 m_t_10 color_wen9"><em class="Error fl"></em><em class="fl mt_4l5">'+msg+'</em></span>');   
	   }
	   else
	   {
		   if(obj.parents('li').find('div:first').next('span').find('em').length>0)
		   {
			  obj.parents('li').find('div:first').next('span').find('em:first').removeClass('success').addClass('Error'); 
		      obj.parents('li').find('div:first').next('span').find('em:last').html(msg);
		   }
		   else
		   {
			  obj.parents('li').find('div:first').next('span').html('<em class="Error fl"></em><em class="fl mt_4l5">'+msg+'</em>'); 
		   }   
	   }
   }
   //正确消息函数(正确内容需要删除input后边的span标签)
   //function rightInfo(id)
   function rightInfo(obj)
   {
	   if(obj.parents('li').find('div:first').siblings().length==1)
	   {
		   if(obj.parents('li').find('div:first').next('span').find('em').length>0)
		   {
			   obj.parents('li').find('div:first').next('span').find('em:last').html('');    
			   obj.parents('li').find('div:first').next('span').find('em:first').removeClass('Error').addClass('success');
		   }
		   else
		   {
			   obj.parents('li').find('div:first').next('span').html('<em class="success fl"></em><em class="fl mt_4l5"></em>'); 
		   }
	   }  
   }
   
   /*
    * 验证邮箱
	*/
   function checkEmail(obj)
   {
	   var reg=/^[a-zA-Z0-9_\-]{1,}@[a-zA-Z0-9_\-]{1,}\.[a-zA-Z0-9_\-.]{1,}$/;
	   var email = $.trim(obj.val());
	   if(!checkEmpty(obj,'请输入邮箱')) {
	      return false; 
	   } else if(!reg.test(email)) {
		   errorInfo(obj,'邮箱格式错误');
		   return false;
	   } else {
		   rightInfo(obj);
		   return true;
	   }
   }
   
   //验证密码
   function checkPassword(obj)
   {
	  var val = $.trim(obj.val());
	  if(!checkEmpty(obj,'请输入密码')) {
		  return false; 
	  } else if( val.length < 8 || val.length > 16) {
		  errorInfo(obj,'密码长度为8~16位');
		  return false;
	  } else if(/[^\x00-\x7f]/.test(val)) {
		  errorInfo(obj,'密码长度8~16位，数字、字母、字符至少包含两种');
		  return false;
	  } else if(/^\d+$/.test(val)) {
		  errorInfo(obj,'密码不能全为数字');
		  return false;
	  } else if(/^[A-Za-z]+$/.test(val)) {
		  errorInfo(obj,'密码不能全为字母');
		  return false;
	  } else if( /^[^A-Za-z0-9]+$/.test(val)) {
		  errorInfo(obj,'密码不能全为字符');
		  return false;
	  } else {
		  rightInfo(obj);
		  return true;
	  }
   }
   //验证确认密码
   function checkconfirmPwd(obj,id)
   {
	  var reg_confirmPwd = $.trim(obj.val());
	  if(!checkEmpty(obj,'请输入新密码')) {
		return false;
	  }
	  
	  if(checkPassword(obj))
	  { 
		 var reg_pwd        = $.trim($("#"+id).val()); 
		 if(reg_confirmPwd!=reg_pwd) {
			errorInfo(obj,'密码输入不一致');
			return false;
		 } else {
			rightInfo(obj);
			return true;
	     }
      }
   }
   //验证是否为空
   function checkEmpty(obj,msg)
   {
	   var val = $.trim(obj.val());   //$.trim(val)去调前后空格  val()就是当前这个input的值
	   if(val=='')
	   {
		 errorInfo(obj,msg);
		 return false;
	   }
	   else
	   {
		 rightInfo(obj); 
		 return true;
	   }
   }
   //验证手机号
   function checkPhone(obj)
   {
	   var reg = /^1[3|4|5|8]\d{9}$/;
	   var email = $.trim(obj.val());
	   if(!checkEmpty(obj,'请输入手机号')) {
	      return false; 
	   } else if(!reg.test(email)) {
		   errorInfo(obj,'手机号格式错误');
		   return false;
	   } else {
		   rightInfo(obj);
		   return true;
	   }
   }
   
   <!--邮箱注册 start-->
   //验证邮箱
   $('#emil').blur(function(){
	  checkEmail($(this));
   });
    //姓名框添加鼠标离开文本框事件
   $('#user_name').blur(function(){
	   checkEmpty($(this),'请输入姓名');
   });
   //密码
   $("#reg_pwd").blur(function(){
      checkPassword($(this));
   });
   //确认密码
   $("#reg_confirmPwd").blur(function(){
      checkconfirmPwd($(this),'reg_pwd');
   });
   //验证码
   $("#reg_captcha").blur(function(){
       checkEmpty($(this),'请输入验证码');
   });
   
   //邮箱注册提交
   $("#email_register").click(function(){
	  var flag = false;
      if(!checkEmpty($("#emil"),'请输入姓名')) {
	      flag = true;
      } if(!checkEmpty($('#user_name'),'请输入姓名')) {
	      flag = true;
	  } if(!checkPassword($("#reg_pwd"))) {
	      flag = true;
	  } if(!checkconfirmPwd($("#reg_confirmPwd"),'reg_pwd')) {
	      flag = true;
	  } if(!checkEmpty($("#reg_captcha"),'请输入验证码')) {
	      flag = true;
	  } 
	  
	  if(!flag)
	  {
		 //这里写ajax
		 return true;
	  }
	  else
	  {
		 return false;
	  }
	  
   });
   
   <!--邮箱注册 end-->
   
   <!--手机注册 start-->
    //验证邮箱
   $('#Mobile').blur(function(){
	  checkPhone($(this));
   });
    
   //密码
   $("#Mob_pwd").blur(function(){
      checkPassword($(this));
   });
   //确认密码
   $("#Mob_confirmPwd").blur(function(){
      checkconfirmPwd($(this),'Mob_pwd');
   });
   //验证码
   $("#Mob_captcha").blur(function(){
       checkEmpty($(this),'请输入验证码');
   });
   
   //邮箱注册提交
   $("#Mobile_register").click(function(){
	  var flag = false;
      if(!checkPhone($("#Mobile"))) {
	      flag = true;
      } if(!checkPassword($("#Mob_pwd"))) {
	      flag = true;
	  } if(!checkconfirmPwd($("#Mob_confirmPwd"),'Mob_pwd')) {
	      flag = true;
	  } if(!checkEmpty($("#Mob_captcha"),'请输入验证码')) {
	      flag = true;
	  } 
	  
	  if(!flag)
	  {
		 //这里写ajax
		 return true;
	  }
	  else
	  {
		 return false;
	  }
	  
   });
   <!--手机注册 end-->
   
   <!--登录 start-->
   $("#login_uname").blur(function(){
       checkEmpty($(this),'请输入邮箱，手机号或者用户名');
   });
   
   $("#login_pwd").blur(function(){
      checkPassword($(this));
   });
   
   $("#login_captcha").blur(function(){
       checkEmpty($(this),'请输入验证码');
   });
   
   //登录
   $("#login").click(function(){
      var flag = false;
      if(!checkEmpty($('#login_uname'),'请输入邮箱，手机号或者用户名')) {
	      flag = true;
      } if(!checkPassword($("#login_pwd"))) {
	      flag = true;
	  } if(!checkEmpty($("#login_captcha"),'请输入验证码')) {
	      flag = true;
	  } 
	  if(!flag)
	  {
		 //这里写ajax
		 return true;
	  }
	  else
	  {
		 return false;
	  }
   });
  
   <!--登录 end-->
});
}
//--------------------------------------------------------------------------------------------------------
//搜索
function mysearch(id)
{
	var oMyInput=getById(id);
	var oHeadSearch=oMyInput.getElementsByTagName('input')[0];
	var oSpan=oMyInput.getElementsByTagName('span')[0];
	
	oHeadSearch.onfocus=function()
	{
		oSpan.style.display='none';
		oMyInput.style.border='solid 1px #85C155';
		
	}
	oHeadSearch.onblur=function()
	{
		if(oHeadSearch.value=='')
		{
			oSpan.style.display='block';
			oMyInput.style.border='solid 1px rgb(223, 223, 223)';
		}
	}
	oSpan.onclick=function()
	{
		oHeadSearch.focus();
	}		  
}
//意见反馈 提交回复部分
function textproposal()
{
	var oProposal=getByClass(document,'proposal')[0];	
	var oProposa2=getByClass(document,'proposal2')[0];
	var oProposalBox=getByClass(document,'proposal_box')[0];
	var oEditwrap=getByClass(oProposalBox,'editwrap')[0];	
	var oTransparent=getByClass(document,'transparent')[0];	
	var oTextarea=getById('wenti_content');
	var arr=['他妈的', '你妈的', '傻×', '衮蛋', '你妹儿'];
	var oTiji=getByClass(document, 'tiji')[0];
	var oClose=getByClass(oProposa2,'close')[0];	
	var oTjiao=getByClass(oProposa2,'tiji')[0];
    oTextarea.onfocus=function()
	{
		oEditwrap.style.border='solid 1px #85C155';
	}
	oTextarea.onblur=function()
	{
		if(oTextarea.value=='')
		{
			oEditwrap.style.border='solid 1px rgb(223, 223, 223)';
		}
	}
	oTiji.onclick=function()
	{
		oTextarea.value = oTextarea.value.replace(/<\[^>]*>/g,''); //去除HTML tag
		oTextarea.value = oTextarea.value.replace(/[ | ]*\n/g,'\n'); //去除行尾空白
		//oTextarea.value = oTextarea.value.replace(/\n[\s| | ]*\r/g,'\n'); //去除多余空行
		oTextarea.value=oTextarea.value.replace(/&nbsp;/ig, "");
		oTextarea.value=oTextarea.value.replace(/ /ig,'');//去掉 
		for(var i=0; i<arr.length; i++)
		{
			if(oTextarea.value.indexOf(arr[i])>-1)
			{
				tag('内容不得包含敏感词','transparent2');
				return false;
			}
		}
		if(oTextarea.value=='')
		{
			tag('请输入非空值','transparent2');
			return false;
		}
		else
		{
			oTextarea.value='';
			oProposal.style.display='none';
			oProposa2.style.display='block';
			var scrollTop=document.body.scrollTop || document.documentElement.scrollTop;
			var scrollLeft=document.body.scrollLeft || document.documentElement.scrollLeft;
			oProposa2.style.display='block';
			oProposa2.style.left=(document.documentElement.clientWidth- oProposa2.offsetWidth)/2+scrollLeft+'px';
			oProposa2.style.top=(document.documentElement.clientHeight- oProposa2.offsetHeight)/2+scrollTop+'px';
			oProposa2.style.zIndex='999';
			
			function addE()
			{
				var scrollTop=document.body.scrollTop || document.documentElement.scrollTop;
				var scrollLeft=document.body.scrollLeft || document.documentElement.scrollLeft;
				
				//move(oProposa2, {top: parseInt((document.documentElement.clientHeight- oProposa2.offsetHeight)/2)+scrollTop},'buffer',null);
				var h=parseInt((document.documentElement.clientHeight- oProposa2.offsetHeight)/2)+scrollTop;
		        $(oProposa2).stop().animate({top:h});
			}
			addEvent(window,'resize',addE);
			addEvent(window,'scroll',addE);
			oTjiao.onclick=oClose.onclick=function()
			{
				oProposa2.style.display='none';
				if(oTransparent)
				{
					oTransparent.style.display='none';
				}
			}
			return false;
		}
	}
}
//回到顶部
function gottop()
{
	window.onscroll=function(){
		var oGoTop=getById('goTop');
		var scrollTop=document.documentElement.scrollTop || document.body.scrollTop;
		if(scrollTop>400){
			oGoTop.style.display='block';
		}else {
			oGoTop.style.display='none';
		}
	 };
}
//回到顶部
function gotoTop(acceleration, stime) {
   acceleration = acceleration || 0.1;
   stime = stime || 10;
   var x1 = 0;
   var y1 = 0;
   var x2 = 0;
   var y2 = 0;
   var x3 = 0;
   var y3 = 0;
 
   if (document.documentElement) {
       x1 = document.documentElement.scrollLeft || 0;
       y1 = document.documentElement.scrollTop || 0;
   }
   if (document.body) {
       x2 = document.body.scrollLeft || 0;
       y2 = document.body.scrollTop || 0;
   }
   var x3 = window.scrollX || 0;
   var y3 = window.scrollY || 0;
 
   // 滚动条到页面顶部的水平距离
   var x = Math.max(x1, Math.max(x2, x3));
   // 滚动条到页面顶部的垂直距离
   var y = Math.max(y1, Math.max(y2, y3));
 
   // 滚动距离 = 目前距离 / 速度, 因为距离原来越小, 速度是大于 1 的数, 所以滚动距离会越来越小
   var speeding = 1 + acceleration;
   window.scrollTo(Math.floor(x / speeding), Math.floor(y / speeding));
 
   // 如果距离不为零, 继续调用函数
   if(x > 0 || y > 0) {
       var run = "gotoTop(" + acceleration + ", " + stime + ")";
       window.setTimeout(run, stime);
   }
}

//弹层 及拖拽 鼠标滚动控制弹出在中
function objshow(btnid,objid,closeid,bg)
{
	var aBtn=document.getElementById(btnid);

	var aDiv=document.getElementById(objid);
	var oClose=document.getElementById(closeid);
	var oBg_transparent=document.getElementById(bg);
	aBtn.onclick=function()
	{
		var scrollTop=document.body.scrollTop || document.documentElement.scrollTop;
		var scrollLeft=document.body.scrollLeft || document.documentElement.scrollLeft;
		aDiv.style.display='block';
		aDiv.style.left=(document.documentElement.clientWidth- aDiv.offsetWidth)/2+scrollLeft+'px';
		aDiv.style.top=(document.documentElement.clientHeight- aDiv.offsetHeight)/2+scrollTop+'px';
		aDiv.style.zIndex='999';
		if(oBg_transparent)
		{
			oBg_transparent.style.display='block';
		    oBg_transparent.style.zIndex='888';
		}
	}
	function addE()
	{
		var scrollTop=document.body.scrollTop || document.documentElement.scrollTop;
		var scrollLeft=document.body.scrollLeft || document.documentElement.scrollLeft;
		
		move(aDiv, {top: parseInt((document.documentElement.clientHeight- aDiv.offsetHeight)/2)+scrollTop},'buffer',null);
	}
	addEvent(window,'resize',addE);
	addEvent(window,'scroll',addE);
	oClose.onclick=function()
	{
		aDiv.style.display='none';
		if(oBg_transparent)
		{
			oBg_transparent.style.display='none';
		    oBg_transparent.style.zIndex='';
		}
	}
}

//此函数在首页和点播里可共用
//三小图标的函数
function iconall(oGo_on)
{
	icon('book','book1');
	icon('light','light1');
	icon('discuss','discuss1');
	function icon(sclass,aclass)
	{
		var oBook=getByClass(oGo_on,sclass)[0];
		oBook.onmouseover=function()
		{
			this.className=aclass;
			this.getElementsByTagName('span')[0].style.display='block';
		}
		oBook.onmouseout=function()
		{
			this.className=sclass;
			this.getElementsByTagName('span')[0].style.display='none';
		}
	}
}


//点播和组合详情页————场景切换
function Scene_change()
{
	$(function(){
		var aBtn=$('.pic_bg_btn li');
		var oLi=$('.pic_bg_box li');
		aBtn.mouseover(function(){
			aBtn.removeClass('active');
			$(this).addClass('active');
			oLi.stop().animate({opacity:0,zIndex:0})
			oLi.eq($(this).index()).stop().animate({opacity:1,zIndex:10});
		});
	});
}

//透明弹出——红色-------------------------------------------------------------------
function tag(text,id)
{
	var oBgTransparent=getById(id);
	var oTag=getById('Tag');
	var oP=getByClass(oTag,'Tag_p')[0];
	oP.innerHTML=text;
	var timer=null;
	
	//move(oBgTransparent, {opacity:50}, 'buffer',4);
	$(oBgTransparent).stop().animate({opacity: '0.5'});
	oBgTransparent.style.display='block';
	oTag.style.display='block';
	oTag.style.zIndex=20001;
	oBgTransparent.style.zIndex=20000;
		timer=setInterval(function(){
		 oBgTransparent.style.display='none';
		 oTag.style.display='none';	
		 $(oBgTransparent).stop().animate({opacity: '0'});
		
		 clearInterval(timer);
   },1000);
};
//透明弹出—绿色-------------------------------------------------------------------
function tag1(text,id)
{
	var oBgTransparent=getById(id);
	var oTag1=getById('Tag1');
	var oP=getByClass(oTag1,'Tag_p')[0];
	oP.innerHTML=text;
	var timer=null;
	
	//move(oBgTransparent, {opacity:50}, 'buffer',4);
	$(oBgTransparent).stop().animate({opacity: '0.5'});
	oBgTransparent.style.display='block';
	oTag1.style.display='block';
	oP.style.color='#85C155';
	oTag1.style.zIndex=20001;
	oBgTransparent.style.zIndex=20000;
		timer=setInterval(function(){
		 oBgTransparent.style.display='none';
		 oTag1.style.display='none';	
		 $(oBgTransparent).stop().animate({opacity: '0'});
		 clearInterval(timer);
   },1000);
};






