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
	
	//var re=new RegExp('\\b'+sClass+'\\b');
	var re=new RegExp('(?:[^\\-]\\b)'+sClass+'(?:\\b[^\\-])');
	
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
                //效果展示部分1----所以模块公用
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
		var aInput=getByTagName(aInputDiv,'input')[0];
		var oSpan=getByTagName(aInputDiv,'span')[0];
		//var oNext=aInputDiv.nextElementSibling || aInputDiv.nextSibling;
		aInput.onfocus=function()
		{
			oSpan.style.display='none';
			aInputDiv.style.border='solid 1px #CCC';
			
			$(this).parent().next(".Prompt0").fadeIn();
		}
		aInput.onblur=function()
		{
			if(aInput.value=='')
			{
				oSpan.style.display='block';
				aInputDiv.style.border='solid 1px #F00';
				
				$(this).parent().next(".Prompt0").fadeOut();
			}
		}
		oSpan.onclick=function()
		{
			aInput.focus();
		}
	}
}
//登录  单选 忘记我 以及上传课程页也有！
function oTopics_a(oTopics)
{
	 oTopics.onclick=function()
	 {
		var oSpan=this.children[0];
		var oInput=oSpan.children[0];
		
		 if(oSpan.className=='Test_True')
		 {
			oSpan.className='Text_error';
			if(oInput)
			{
				oInput.value=0;
			}
		 }
		 else
		 {
			 oSpan.className='Test_True';
			 if(oInput)
			{
				oInput.value=1;
			}
		 }
	 }
}
//登录注册关掉的时候 回到初始
 function Sign_Up()
 {
	 var oSign_In=getByClass(document,'Sign_In')[0];
	 var oPrompt1=getByClass(oSign_In,'Prompt1')[0];
	 var otransparent=getByClass(document,'transparent')[0];
	 var oXxguan=getByClass(oSign_In,'xxguan')[0];
	
	 var Sign_In_ul=getByClass(oSign_In,'Sign_In_ul')[1];
	 var oTopics1=getByClass(Sign_In_ul,'topics1')[0];
	 var oB=getByTagName(oTopics1,'b')[0];
	  
	 oXxguan.onclick=function()
	 {
		 
		  var oInput_div=getByClass(oSign_In,'input_div');
		  var next_div=oInput_div[0].nextElementSibling || oInput_div[0].nextSibling;
		  var val=next_div.value;
		
		 for(var i=0; i<oInput_div.length; i++)
		 {
			var aInput_s=getByTagName(oInput_div[i],'input')[0];
			aInput_s.value='';
			oInput_div[i].style.border='solid #999 1px';
			var aSpan=getByTagName(oInput_div[i],'span')[0];
			
			aSpan.style.display='block';
			oPrompt1.style.display='none';
			
		 }
		 if(oB.className=='Test_True')
		 {
			 oB.className='Text_error';
		 }
		 
		
		  oSign_In.style.display='none';
		  otransparent.style.display='none';
	 }
 }

 
//关注提交注册完成
 function guanzhu(){
 	var value="";
 	var checklist = document.getElementsByName ("selected");
 	   for (var i=0;i<box.length;i++ ){
 	     if(box[i].checked){ //判断复选框是否选中
 	    	 value=value+box[i].value + " "; //值的拼凑 .. 具体处理看你的需要,
 	     }
 	   }
 	//异步提交
 	   var url = U('home/Register/guanzhu');
 	   $.post(url, {guanzhu:value}, function(res){
 		   var url ='';
 			 if(res == 1){
 				 url = U('index/Index/index');
 				 window.location.href = url;
 			 }
 			 if(res == 2){
 				 url = U('home/Register/index');
 				 window.location.href = url;
 			 }
 	 
 		});
 	   
 	   
 }
 
//换一换相同阶段
 function change_tui(){
 	
 	var url = U('home/Register/getmore');
 	$.post(url, {}, function(res){
 		document.getElementById("guanzhu").innerHTML= res;
  
 	});
 }
 
 //换一换吐槽
 
 function change_tu(){
	 var url = U('home/Register/gettu');
	 	$.post(url, {}, function(res){
	 		document.getElementById("tu").innerHTML= res;
	  
	 	});
	 
 }
 
//退出登录
 function logout(){
 	var url = U('home/Passport/logout');
 	$.post(url, {}, function(res){
 		if(res == 1){
 			window.location.href = U('classroom/Index/index');
 		}
  
 	});
 }
 //错误的消息函数 
function errorInfo(obj,msg,type)
{
  
   switch(type)
   {
	   case 'register':
		  $('.Prompt1').show().find('em').addClass('Error').html(msg);
		  if(obj.parent('.input_div'))
		  {
			 obj.parent('.input_div').show().css({"border":"1px solid #F00"});  
		  }
		  if(obj.parent('.Provinces'))
		  {
			 obj.parent('.Provinces').show().css({"border":"1px solid #F00"});  
		  }
		 
		  break;
		   case 'login':
		   if(obj.parent('.input_div'))
		   {
				obj.parent('.input_div').css({"border":"1px solid #F00"});
		   }
		   if(obj.parent('.Provinces'))
		   {
				obj.parent('.Provinces').css({"border":"1px solid #F00"});
		   }
		 
		  break;
		 default:
		  break;
   }
  
}
//正确消息函数(正确内容需要删除input后边的span标签)
  
function rightInfo(obj,type)
{
   
   switch(type)
   {
	   case 'register':
		  $('.Prompt1').hide().find('em').removeClass('Error').html('');
		  if(obj.parent('.input_div'))
		  {
			  obj.parent('.input_div').show().css({"border":"1px solid #85C155"});
		  }
		   if(obj.parent('.Provinces'))
		  {
			  obj.parent('.Provinces').show().css({"border":"1px solid #85C155"});
		  }
		  $(obj).parent().next(".Prompt0").fadeOut();
		  break;
	   case 'login':
		  if(obj.parent('.input_div'))
		  {
			 obj.parent('.input_div').css({"border":"1px solid #85C155"}); 
		  }
		  if(obj.parent('.Provinces'))
		  {
			 obj.parent('.Provinces').css({"border":"1px solid #85C155"}); 
		  }
		  
		  $(obj).parent().next(".Prompt0").fadeOut();
		  break;
	   default:
		  break;
   }
}
//登录注册 表单验证
function check_from()
{
	$(function(){
		
  
   
   /*
    * 验证邮箱
	*/
   function checkEmail(obj,type)
   {
	   var reg=/^[a-zA-Z0-9_\-]{1,}@[a-zA-Z0-9_\-]{1,}\.[a-zA-Z0-9_\-.]{1,}$/;
	   var email = $.trim(obj.val());
	   if(!checkEmpty(obj,'请输入邮箱',type)){
	      return false; 
	   } else if(!reg.test(email)) {
		   errorInfo(obj,'邮箱格式错误',type);
		   return false;
	   } else {
		   rightInfo(obj,type);
		   return true;
	   }
   }
   
   //验证密码
   function checkPassword(obj,type)
   {
	  var val = $.trim(obj.val());
	  if(!checkEmpty(obj,'密码长度至少为8位',type)) {
		  return false; 
	  } else if( val.length < 8 || val.length > 16) {
		  errorInfo(obj,'密码长度为8~16位',type);
		  return false;
	  } else if(/[^\x00-\x7f]/.test(val)) {
		  errorInfo(obj,'密码长度8~16位，数字、字母、字符至少包含两种',type);
		  return false;
	  } else if(/^\d+$/.test(val)) {
		  errorInfo(obj,'密码不能全为数字',type);
		  return false;
	  } else if(/^[A-Za-z]+$/.test(val)) {
		  errorInfo(obj,'密码不能全为字母',type);
		  return false;
	  } else if( /^[^A-Za-z0-9]+$/.test(val)) {
		  errorInfo(obj,'密码不能全为字符',type);
		  return false;
	  } else {
		  rightInfo(obj,type);
		  return true;
	  }
   }
   //验证确认密码
   function checkconfirmPwd(obj,id,type)
   {
	  var reg_confirmPwd = $.trim(obj.val());
	  if(!checkEmpty(obj,'密码长度至少为8位',type)) {
		return false;
	  }
	  
	  if(checkPassword(obj,type))
	  { 
		 var reg_pwd        = $.trim($("#"+id).val()); 
		 if(reg_confirmPwd!=reg_pwd) {
			errorInfo(obj,'密码输入不一致',type);
			return false;
		 } else {
			rightInfo(obj,type);
			return true;
	     }
      }
   }
   //验证是否为空
   function checkEmpty(obj,msg,type)
   {
	   var val = $.trim(obj.val());   //$.trim(val)去调前后空格  val()就是当前这个input的值
	   if(val=='')
	   {
		 obj.next().css({'display':"block"});
		 errorInfo(obj,msg,type);
		 return false;
	   }
	   else
	   {
		 rightInfo(obj,type); 
		 return true;
	   }
   }
   //验证手机号
   function checkPhone(obj,type)
   {
	   var reg = /^1[3|4|5|8]\d{9}$/;
	   var email = $.trim(obj.val());
	   if(!checkEmpty(obj,'请输入手机号',type)){
	      return false; 
	   } else if(!reg.test(email)) {
		   errorInfo(obj,'手机号格式错误',type);
		   return false;
	   } else {
		   rightInfo(obj,type);
		   return true;
	   }
   }
   
   /*
    * 验证登录名
	*/
   function checkUser(obj,msg,type)
   {
	  var val = $.trim(obj.val());

	  if(!checkEmpty(obj,msg,type)){
		  return false; 
	  } /*else if (/^\d+$/.test(val)){
		  if(checkPhone(obj,type))
		  {
			  return false;
		  }
	  } else if (checkEmail(obj,type)){
	      return false;
	  } */else {
	      rightInfo(obj,type);
		  return true;
	  }
   }	

   $('#Mobile').blur(function(){
	  checkPhone($(this),'register');
	  //$(this).parent().css({"border":"1px solid #F00"});
   });
   //验证邮箱
   $('#emil').blur(function(){
	  checkEmail($(this),'register');
   });

   //密码
   $("#reg_pwd").blur(function(){
      checkPassword($(this),'register');
   });
   //确认密码
   $("#reg_confirmPwd").blur(function(){
      checkconfirmPwd($(this),'reg_pwd','register');
   });
   //验证码
   $("#reg_captcha").blur(function(){
       checkEmpty($(this),'请输入验证码','register');
   });
   
    $('#getCaptcha').click(function () {
		if (checkPhone($("#Mobile"))) {
			var phone = $.trim($("#Mobile").val());
			var url_reg = U('home/Register/sendcode');
			var _this = $(this);
			
			var startcount = function(_this){
				var n = 60;
				var timer = null;
				_this.attr({"disabled": true}).css({"cursor": "wait",'color':"#999",'border':"#999 solid 1px"});
				_this.val('重新发送(' + n + ')');
				timer = setInterval(function () {
					n--;
					_this.val('重新发送(' + n + ')');
					if (n == 0) {
						clearInterval(timer);
						_this.removeAttr("disabled").css({"cursor": "pointer"}).val('获取验证码');
					}
	
				}, 1000);	
			};
			
			
			$.post(url_reg, {mobile:phone}, function(res){
		    	if(res == 'a'){
		    		notes("手机号已被占用!",'failure');
		    		document.getElementById("info").innerHTML = "手机号已被占用!";
		    	}
		    	else{
				if(res > 0 ){
		    		document.getElementById("info").innerHTML = "验证码发送成功!";
		    		notes("验证码发送成功!",'success');
					startcount(_this);
		    	}
		    	else{
		    		document.getElementById("info").innerHTML = "验证码发送失败!";
					notes("验证码发送失败!",'failure');
		    	}
				
		    	}
		 
			});
		 
			
		
		}
	});
   
   //邮箱注册提交
   $("#email_register").click(function(){
	  var flag = false;
     
	  if(!checkEmpty($("#Mobile"),'请输入手机号','register')){
	      flag = true;
	  } if(!checkPhone($("#Mobile"),'register')) {
	      flag = true;
	  } if(!checkEmpty($("#reg_captcha"),'请输入验证码','register')){
	      flag = true;
	  } if(!checkEmpty($("#emil"),'请输入邮箱','register')){
	      flag = true;
      } if(!checkEmail($("#emil"),'register')){
	      flag = true;
      } if(!checkPassword($("#reg_pwd"),'register')){
	      flag = true;
	  } if(!checkconfirmPwd($("#reg_confirmPwd"),'reg_pwd','register')){
	      flag = true;
	  } 
	  
	  if(!flag)
	  {
		  //异步提交注册
			 var phone = $.trim($("#Mobile").val());
			 var reg_captcha = $.trim($("#reg_captcha").val());
			 var email = $.trim($("#emil").val());
			 var reg_pwd = $.trim($("#reg_pwd").val());
			 var reg_confirmPwd = $.trim($("#reg_confirmPwd").val());
			 var url = U('home/Register/doStep1');
			 //邀请
			 var invate = $.trim($("#txtinvate").val());
			 var invate_key = $.trim($("#txtinvate_key").val());
			 
			 $.post(url, {mobile:phone,code:reg_captcha,email:email,reg_pwd:reg_pwd,reg_confirmPwd:reg_confirmPwd,invate:invate,invate_key:invate_key}, function(res){
					
				 	if(res == 6 ){
			    		document.getElementById("info").innerText = "验证码错误!";
			    	}
			    	else if(res == 1){
			    		document.getElementById("info").innerText = "密码不一致!";
			    	}
			    	else if(res == 3){
			    		document.getElementById("info").innerText = "手机号已被占用!";
			    	}
			    	else if(res == 4){
			    		document.getElementById("info").innerText = "邮箱已被占用!";
			    	}
			    	else if(res == 2){
			    		document.getElementById("info").innerText = "系统繁忙，稍后继续!";
			    	}
			    	else if(res == 5 ){
			    		 window.location.href = U('home/Register/reg');  
			    	}
			    	 
			 
				});
			 return true;
	  }
	  else
	  {
		 return false;
	  }
	  
   });
   
 
   $("#login_uname").blur(function(){
	   checkUser($(this),'请输入邮箱，手机号或者用户名','login');
   });
   
   $("#login_pwd").blur(function(){
      checkPassword($(this),'login');
   });
   
   /*$("#login_captcha").blur(function(){
       checkEmpty($(this),'请输入验证码');
   });*/
   
   //登录
   $("#login").click(function(){
      var flag = false;
     
	  if(!checkUser($("#login_uname"),'请输入邮箱，手机号或者用户名','login')) {
		  
	      flag = true;
      } if(!checkPassword($("#login_pwd"),'login')) {
    	   
	      flag = true;
	  } /*if(!checkEmpty($("#login_captcha"),'请输入验证码')) {
	      flag = true;
	  } */
       
	  if(!flag){
		//请求登录
		var login_name = $('#login_uname').val();
		var pwd = $('#login_pwd').val();
		var rem = $('#login_remember').val();
		var url = U('home/Passport/dologin');
		var _is = $(this).attr('data-args');
		$.post(url, {login_uname:login_name,login_pwd:pwd,login_remember:rem,login_args:_is}, function(res){
			if(res == 0 ){
				notes('登录失败!帐号或密码填写错误!','failure');
				//document.getElementById("js_login_input").innerText = "";
			}else if(res == 100001){
				notes('登录成功!','success');
				window.location.href = '/';
				//document.getElementById("js_login_input").innerText = "";
			}else{
				notes('登录成功!','success');
				//document.getElementById("js_login_input").innerText = "登录成功!";
				window.location.reload();
			}
		});
		 return true;
	  }
	  else
	  {
		 return false;
	  }
   });
   

		
		
		
	$('#Perfect_Nick_name').blur(function(){
	  checkEmpty($(this),'请输入昵称','register');
	  //$(this).parent().css({"border":"1px solid #F00"});
   });
   $('#Mobile').blur(function(){
	  checkPhone($(this),'register');
	  //$(this).parent().css({"border":"1px solid #F00"});
   });
   //验证邮箱
   $('#Perfect_emil').blur(function(){
	  checkEmail($(this),'register');
   });
   //密码
   $("#Perfect_reg_pwd").blur(function(){
      checkPassword($(this),'register');
   });
   //确认密码
   $("#Perfect_reg_confirmPwd").blur(function(){
      checkconfirmPwd($(this),'Perfect_reg_pwd','register');
   });
   //验证码
   $("#reg_captcha").blur(function(){
       checkEmpty($(this),'请输入验证码','register');
   });
   //省
   /*$("#Perfect_province").change(function(){
      var val = $(this).val();
	  if(!val){
		 errorInfo($(this),'请选择省份','register'); 
		 $(this).parent().css({"border":"1px solid #F00"});
	  } else {
	     rightInfo($(this),'register');
		 $(this).parent().css({"border":"1px solid #85C155"});
	  }
   })
   //市
    $("#Perfect_city").change(function(){
      var val = $(this).val();
	  if(!val){
		 errorInfo($(this),'请选择城市','register'); 
		 $(this).parent().css({"border":"1px solid #F00"});
	  } else {
	     rightInfo($(this),'register');
		 $(this).parent().css({"border":"1px solid #85C155"});
	  }
   })*/
   
    $('#Perfect_getCaptcha').click(function () {
		if (checkPhone($("#Perfect_Mobile"))) {
			var phone = $.trim($("#Perfect_Mobile").val());
			var n = 60;
			var timer = null;
			var _this = $(this);
			_this.attr({"disabled": true}).css({"cursor": "wait",'color':"#999",'border':"#999 solid 1px"});
			_this.val('重新发送(' + n + ')');
			timer = setInterval(function () {
				n--;
				_this.val('重新发送(' + n + ')');
				if (n == 0) {
					clearInterval(timer);
					_this.removeAttr("disabled").css({"cursor": "pointer"}).val('获取验证码?');
				}
			}, 1000);
		}
	});
   //完善信息-保存
   $("#Perfect").click(function(){
	  var flag = false;
	  if(!checkEmpty($("#Perfect_Nick_name"),'请输昵称','register')){
		  flag = true;
	  }if(!checkEmpty($("#Mobile"),'请输入手机号','register')){
		  flag = true;
	  } if(!checkPhone($("#Mobile"),'register')) {
		  flag = true;
	  } if(!checkEmpty($("#reg_captcha"),'请输入验证码','register')){
		  flag = true;
	  } if(!checkEmpty($("#Perfect_emil"),'请输入邮箱','register')){
		  flag = true;
	  } if(!checkEmail($("#Perfect_emil"),'register')){
		  flag = true;
	  } if(!checkPassword($("#Perfect_reg_pwd"),'register')){
		  flag = true;
	  } if(!checkconfirmPwd($("#Perfect_reg_confirmPwd"),'Perfect_reg_pwd','register')){
		  flag = true;
		}
	  if(!flag)
	  {
		 //这里写ajax
		  var other_type = $('.Sign_In_ul input[name="other_type"]').val();
		  var oauth_token = $('.Sign_In_ul input[name="oauth_token"]').val();
		  var oauth_token_secret = $('.Sign_In_ul input[name="oauth_token_secret"]').val();
		  var other_uid = $('.Sign_In_ul input[name="other_uid"]').val();
		  var other_face = $('.Sign_In_ul input[name="other_face"]').val();
		  var uname = $('.Sign_In_ul input[name="uname"]').val();
		  var email = $('.Sign_In_ul input[name="email"]').val();
		  var password = $('.Sign_In_ul input[name="password"]').val();
		  var Mobile = $('.Sign_In_ul input[name="Mobile"]').val();
		  var sex = $('.Sign_In_ul input[name="sex"]').val();
		  var reg_captcha = $('.Sign_In_ul input[name="reg_captcha"]').val();
		  var avatar = $('.Sign_In_ul input[name="avatar"]').val();
		  var repassword = $('.Sign_In_ul input[name="repassword"]').val();
		  var data = {
				  other_type:other_type,
				  oauth_token:oauth_token,
				  oauth_token_secret:oauth_token_secret,
				  other_uid:other_uid,
				  other_face:other_face,
				  uname:uname,
				  email:email,
				  password:password,
				  sex:sex,
				  avatar:avatar,
				  repassword:repassword,
				  Mobile:Mobile,
				  reg_captcha:reg_captcha
		  };
		  var url = U('public/Register/doOtherStep1');
		  $.post(url,data,function(txt){
				if(txt == 6 ){
					document.getElementById("info").html("验证码错误!");
				}
				else if(txt == 1){
					document.getElementById("info").html("密码不一致!");
				}
				else if(txt == 3){
					document.getElementById("info").html("手机号已被占用!");
				}
				else if(txt == 4){
					document.getElementById("info").html("邮箱已被占用!");
				}
				else if(txt == 2){
					document.getElementById("info").html("系统繁忙，稍后继续!");
				}
				else if(txt == 5 ){
					 window.location.href = U('home/Register/reg');  
				}
		  
		  },'json');
		  
		 return true;
	  }
	  else
	  {
		 return false;
	  }

  

   });	
		
		
		
		
		
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
		if(oSpan)
		{
			oSpan.style.display='none';
		}
		oMyInput.style.border='solid 1px #85C155';
	}
	oHeadSearch.onblur=function()
	{
		if(oHeadSearch.value=='')
		{
			if(oSpan)
			{
				oSpan.style.display='block';
			}
			oMyInput.style.border='solid 1px #CECECE';
		}
	}
	if(oSpan)
	{
		oSpan.onclick=function()
		{
			oHeadSearch.focus();
		}	
	}	  
}
//个人中心统一input输入
function Album_Title_input(oMyInput)
{
	var oHeadSearch=oMyInput.getElementsByTagName('input')[0];
	var oSpan=oMyInput.getElementsByTagName('span')[0];
	
	oHeadSearch.onfocus=function()
	{
		if(oSpan)
		{
			oSpan.style.display='none';
		}
		oMyInput.style.border='solid 1px #85C155';
		
	}
	oHeadSearch.onblur=function()
	{
		if(oHeadSearch.value=='')
		{
			if(oSpan)
			{
				oSpan.style.display='block';
			}
			oMyInput.style.border='solid 1px #CECECE';
		}
	}
	if(oSpan)
	{
		oSpan.onclick=function()
		{
			oHeadSearch.focus();
		}	
	}
		
}
//个人中心——个人简介
function Profile(oTextA)
{
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
//意见反馈 提交回复部分
function textproposal()
{
	var oProposal=getByClass(document,'proposal')[0];	
	var oProposa2=getByClass(document,'proposal2')[0];
	var oProposalBox=getByClass(document,'proposal_box')[0];
	var oEditwrap=getByClass(oProposalBox,'editwrap')[0];	
	var oTransparent=getByClass(document,'transparent')[0];	
	var oTextarea=getById('wenti_content');
	var oOriginator=getById('originator');
	
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
			//手动提交意见反馈到后台
			var isok = ajaxSubmitSuggest(oTextarea.value,oOriginator.value);
			if(!isok){
				return isok;
			}
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
//刷新的时候回到顶部
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

//客服
function service()
{
	$('#aside .a-0').hover(function (){
		var w=($('.Service_a a').outerWidth()*$('.Service_a a').length);
		$('.Service_a').css({display:'block'}).stop().animate({left:-w+'px',width:w+'px'});
	}, function (){
		$('.Service_a').css({display:'block'}).stop().animate({left:'0',width:'0'});
	});
}
//头部左侧导航
function tit_ll()
{
	$('.tit_ll a').click(function (){
		//$('#div1 input').attr('class', 'btn aaa');
		$('.tit_ll a').removeClass('green_txt');
		$(this).addClass('green_txt');
	});  
}
/*--function collect_guanzhu()
{
	$('.collect_a').hover(function (){
			$('.collect_span').removeClass('collect_span_a');							
			$('.collect_span').addClass('collect_span_b');
		    						
		$('.erw_n').stop().animate({height:'138px'});
	}, function (){
		$('.erw_n').stop().animate({height:'0px'});
		 $('.collect_span').removeClass('collect_span_b');							
		$('.collect_span').addClass('collect_span_a');
	});
}--*/
//透明弹出——红色-------------------------------------------------------------------
function tag(text,id)
{
	
	var oBgTransparent=getById(id);
	var oTag=getById('Tag');
	var oP=getByClass(oTag,'Tag_p')[0];
	$(oP).html(text);
	//oP.innerHTML=text;
	var timer=null;
	
	//move(oBgTransparent, {opacity:50}, 'buffer',4);
	//$(oBgTransparent).stop().animate({opacity: '0.5'});
	//oBgTransparent.style.display='block';
	oTag.style.display='block';
	oTag.style.zIndex=20001;
	//oBgTransparent.style.zIndex=20000;
		timer=setInterval(function(){
		 //oBgTransparent.style.display='none';
		 oTag.style.display='none';	
		// $(oBgTransparent).stop().animate({opacity: '0'});
		 clearInterval(timer);
   },1000);
};
//透明弹出—绿色-------------------------------------------------------------------
function tag1(text,id)
{
	var oBgTransparent=getById(id);
	var oTag1=getById('Tag1');
	var oP=getByClass(oTag1,'Tag_p')[0];
	
	//oP.innerHTML=text;
	
	$(oP).html(text);
	var timer=null;
	
	//move(oBgTransparent, {opacity:50}, 'buffer',4);
	//$(oBgTransparent).stop().animate({opacity: '0.5'});
	//oBgTransparent.style.display='block';
	oTag1.style.display='block';
	oP.style.color='#85C155';
	oTag1.style.zIndex=20001;
	//oBgTransparent.style.zIndex=20000;
		timer=setInterval(function(){
		 oBgTransparent.style.display='none';
		 oTag1.style.display='none';	
		 // $(oBgTransparent).stop().animate({opacity: '0'});
		 clearInterval(timer);
   },1000);
};
//--------------------------------------------------------------------------------
//notes('绿色字母','success') 绿色图标
//notes('红字母','failure')红色图标
function notes(text,result)
{
	var oNotes=document.createElement('div');
	var oBG=document.createElement('transparent_bg');
	oNotes.className='Notes';
	oBG.className='transparent_bg';
	//创建span
	var oSpan=document.createElement('span');
	//创建P
	var oP=document.createElement('p');
	
	if(result=='success')
	{
		oP.className='Notes_green_text';
		oSpan.className='Notes_green_Icon';
	}
	if(result=='failure')
	{
	   oP.className='Notes_red_text';
	   oSpan.className='Notes_red_Icon';	
	}
	oNotes.appendChild(oP);
	oNotes.appendChild(oSpan);
	var oBody=document.getElementsByTagName('body')[0];
	oBody.appendChild(oNotes);
	oBody.appendChild(oBG);
	
	oP.innerHTML=text;
	var timer=null;
	
	$(oBG).stop().animate({opacity:'0.5'});
	oBG.style.display='block';
	oNotes.style.display='block';
	oNotes.style.zIndex=20001;
	
		oBG.style.zIndex=20000;
		
		timer=setInterval(function(){
		 oNotes.style.display='none';	
		 $(oBG).stop().animate({opacity: '0'});
		 oBG.style.display='none';
		 clearInterval(timer);
   },1000);
}
	

//--------------------------------------------------------------------------------------------------------------------------------
                //效果展示部分2----可能其中有两个以上的共用函数
//--------------------------------------------------------------------------------------------------------------------------------

//***此函数在首页和点播里可共用
//三小图标的函数
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
//**点播和组合详情页————场景切换
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
//**点播和组合详情页————收藏关注
function collect_a(oCollect,n,m,n1,m1)
{
   oCollect.onclick=function()
   {
	  
	   var id   = $(this).attr('data-id');
	   var type = $(this).attr('data-type');
	   var _this = this;
	  if(this.className==n1)
	  {
		  mzgaojiaowang.mycollect(0,type,id,function(d){
			  if(d.status == 1){
				_this.className=m1;
		 		 _this.innerHTML=n; 
			 }
		  });
	  }
	  else
	  {
		  mzgaojiaowang.mycollect(1,type,id,function(d){
			 if(d.status == 1){
				_this.className=n1;
		  		_this.innerHTML=m;	 
			 }
		  });
	  }
   }	   
}
//立即支付弹出里面的验证码和收藏
function obuys_bar()
 {
	  var obuys1=getByClass(document,'Q-buy-btn')[0];
	  var oCaptcha=getByClass(document,'s_Captcha')[0];
	  var oBuyS1r=getByClass(document,'buy_s_2');
	 oBuyS1r[0].onclick=function()
	 {
		 var oSpan=this.children[0];
		 if(oSpan.className==''|| oCaptcha.style.display=='none' || oCaptcha.style.display=='')
		 {
			oSpan.className='Test_true';
			 oCaptcha.style.display='block';
		 }
		 else
		 {
			 oSpan.className='';
			 oCaptcha.style.display='none'; 
		 }
	 }
	 oBuyS1r[1].onclick=function()
	 {
		 var oSpan=this.children[0];
		 if(oSpan.className=='')
		 {
			oSpan.className='Test_true';
		 }
		 else
		 {
			 oSpan.className='';
		 }
	 }
 }

//最热  最新
function hottest(callback)
{
	var oPxbBoxA=getByClass(document,'px_box_a')[0];
	var aBtn=getByTagName(oPxbBoxA,'li');
	for(var i=0; i<aBtn.length; i++)
	{
		aBtn[i].onclick=function()
		{
			$('.txttop').hide();
			var dataid = $(this).attr('data-id');
			$('#'+dataid).show();
			
			
			for(var i=0; i<aBtn.length; i++)
			{
				aBtn[i].className='';
				var aA=getByTagName(aBtn[i],'a')[0];
				aA.className='';
			}
			this.className='selecttag';
			var aA1=getByTagName(this,'a')[0];
			aA1.className='green_txt';
		}
	}
}
//更多收起
function downup(aAttrValues)
{
	var oAvCollapse=getByClass(aAttrValues,'av-collapse')[0];
	oMoreZh.onclick=function()
	{
		if(oAvCollapse.style.height=='auto')
		{
			this.className='more_zh';
			oAvCollapse.style.height='34px';
			this.innerHTML='更多';
		}
		else
		{
			this.className='more_zh1';
			oAvCollapse.style.height='auto';
			this.innerHTML='收起';
		}
	}
	oMoreZh.onmousedown=function()
	{
		return false;
	}
}
//写答案
function answer()
{
	var reply_box=getByClass(document,'reply_box')[0];
	var mce_tinymce=getByClass(document,'mce_tinymce')[0];
	var oFormDo=getByClass(document,'form_do_a')[0];
	var oA=getByClass(oFormDo,'form_do_b')[0];
	reply_box.onclick=function()
	{
		this.style.display='none';
		mce_tinymce.style.display='block';
	}
	oA.onclick=function()
	{
		reply_box.style.display='block';
		mce_tinymce.style.display='none';
	}
}
//分享
function Share_r(sClass)
{
	var oCliShare=getByClass(document,sClass)[0];
	var oShare=getById('Share_r');
	oShare.onclick=function()
	{
		if(oCliShare.style.display=='none' || oCliShare.style.display=='')
		{
			oCliShare.style.display='block';
		}
		else
		{
			oCliShare.style.display='none';
		}
	}
};
//视频右侧 选项卡
function Tabs(sclass,id)
{
	var oTags=getByClass(document,sclass)[0];
	var aBtn=oTags.children;
	//alert(aBtn.length);
	var oTagcontent=getById('tagcontent_box');
	var aCon=getByClass(document,'tagcontent');
	for(var i=0; i<aBtn.length; i++){  //循环加事件
		aBtn[i].index=i;  //索引
		aBtn[i].onclick=function(){
			//1.清空所有
			for(var i=0; i<aBtn.length; i++){
				aBtn[i].className='';
				var aA=getByTagName(aBtn[i],'a')[0];
				aA.style.color='';
				aCon[i].style.display='none';
				
			}
			//2.当前的点亮
			this.className='selecttag';
			var aA=getByTagName(this,'a')[0];
			aA.style.color='#FFF';
			aCon[this.index].style.display='block';
			
			var mz_tag = $(this).attr('data-name');
			if(mz_tag == 'biji'){
				getdianboNote();
			}else if(mz_tag == 'review'){
				getdianboReview();
			}else if(mz_tag == 'question'){
				getdianboQst();
			}
			/////
			
		}
	}	  
}
//公用判断——点评内的
function tabtj(oEditwrap,txt1,txt2, txt3,bg)
{
	var oTextarea=getByTagName(oEditwrap,'textarea')[0];
	var arr=['他妈的', '你妈的', '傻×', '衮蛋', '你妹儿'];
	var oBgBtnGray=getByClass(oEditwrap, 'btn_tag')[0];
	var oLabel=getByTagName(oEditwrap,'label')[0];
	
	oBgBtnGray.onclick=function()
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
				tag(txt1,bg);
				return false;
			}
		}
		if(oTextarea.value=='')
		{
			tag(txt2,bg);
			return false;
		}
		else
		{
			mzSubmitHuiFu(oEditwrap,oTextarea.value);
			return;
			oTextarea.value='';
			oLabel.style.display='block';
			tag1(txt3,bg);
			return false;
		}
	}
}
//单独处理点评
function edix(id,cid)
{
	var oEedix3=getById(id);	
	var aBtn=getByClass(oEedix3, 'btn_tag')[0];
	var oTextarea1=getByTagName(oEedix3,'textarea')[0];
	var oLabel=getByTagName(oEedix3,'label')[0];
	var oHowScore=getById('how-score');
	aBtn.onclick=function()
	{
		oTextarea1.value = oTextarea1.value.replace(/<\[^>]*>/g,''); //去除HTML tag
		oTextarea1.value = oTextarea1.value.replace(/[ | ]*\n/g,'\n'); //去除行尾空白
		//str = str.replace(/\n[\s| | ]*\r/g,'\n'); //去除多余空行
		oTextarea1.value=oTextarea1.value.replace(/&nbsp;/ig, "");
		oTextarea1.value=oTextarea1.value.replace(/ /ig,'');//去掉 
		
		var arr1=['他妈的', '你妈的', '傻×', '衮蛋', '你妹儿'];
		for(var i=0; i<arr1.length; i++)
		{
			oTextarea1.value = oTextarea1.value.replace(/<\[^>]*>/g,''); //去除HTML tag
			oTextarea1.value = oTextarea1.value.replace(/[ | ]*\n/g,'\n'); //去除行尾空白
			oTextarea1.value=oTextarea1.value.replace(/&nbsp;/ig, "");
			oTextarea1.value=oTextarea1.value.replace(/ /ig,'');//去掉 
			var score = document.getElementById("course_score").value;
			
			
			
			for(var i=0; i<arr1.length; i++)
			if(oTextarea1.value.indexOf(arr1[i])>-1)
			{
				if(oTextarea1.value.indexOf(arr1[i])>-1)
				{
					tag('点评内容不得包含敏感词!','bg_transparent');
					return false;
				}
			}
			if(score == 0)
			{
			   tag('请给课程打分!','bg_transparent');
			   
			   return false;
			}
			if(oTextarea1.value=='')
			{
				tag('请输入评价内容!','bg_transparent');
				 
				return false;
			}
			if(oHowScore.innerHTML=='' || oTextarea1.value=='')
			{
				tag('请给课程打分和评价!','bg_transparent');
			}
			if(!oHowScore.innerHTML=='' || !oTextarea1.value=='')
			{
				//异步提交评价
				var content=oTextarea1.value.replace(/&nbsp;/ig, "");
				content = content.replace(/[ ]/g,"");//去除空格
				content = content.replace(/<[^>].*?>/g,"");//去html标签
				
				ajaxPinjia(score,content,cid);
				//tag1('你已提交成功!','bg_transparent');
				oTextarea1.value='';
				oLabel.style.display='block';
				//return false;
			}
		}
		}
}

//判断写笔记和提问
function edis(tid,txt1,txt2,txt3,cid)
{
	var oEedix=getById(tid);	  
	var oTextarea=getByTagName(oEedix,'textarea')[0];
	var arr=['他妈的', '你妈的', '傻×', '衮蛋', '你妹儿'];
	var oBgBtnGray=getByClass(oEedix, 'btn_tag')[0];
	
	var oLabel=getByTagName(oEedix,'label')[0];
	var otip=getByClass(oEedix,'tips')[0]
	
	oBgBtnGray.onclick=function()
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
				tag(txt1,'bg_transparent');
				return false;
			}
		}
		if(oTextarea.value=='')
		{
			tag(txt2,'bg_transparent');
			return false;
		}
		if(!oTextarea.value=='')
		{
			oLabel.style.display='block';
			if(otip)
			{
				otip.innerHTML = "还可以输入<span>100</span>个字";
			}
			var content = oTextarea.value;
			
			//tag1(txt3,'bg_transparent');
			content=content.replace(/&nbsp;/ig, "");
			content = content.replace(/[ ]/g,"");//去除空格
			content = content.replace(/<[^>].*?>/g,"");//去html标签
			var title = content.substr(0,10);
			//提交笔记&问题
			if(tid == 'edi1'){
			ajaxBiji(title,content,cid);
			}
			else if(tid == 'edi3'){
			ajaxTiwen(content,content,cid)
			}
			oTextarea.value='';
		}
	}	  
}
//笔记 点评 评论
function textarea_box(oEditwrap)
{
	var oLabel=getByTagName(oEditwrap,'label')[0];
	var oTextarea=getByTagName(oEditwrap,'textarea')[0];
	
	
	var getLength = function(str)
	{
	   return Math.ceil(str.replace(/^\s+|\s+$/ig,'').replace(/[^\x00-\xff]/ig,'xx').length/2);
	};
	oTextarea.onkeyup = function()
	{
		var v = this.value;
		var oTagcontent3=document.getElementById('tagcontent3');
	    var oSpan=getByTagName(oTagcontent3,'span')[0];
		if(oTagcontent3)
		{
			getText(v);
		}
	}
	oTextarea.onkeydown = function()
	{
		var v = this.value;
		var oTagcontent3=document.getElementById('tagcontent3');
	    var oSpan=getByTagName(oTagcontent3,'span')[0];
		if(oTagcontent3)
		{
			getText(v);
		}
	}
	function getText(v)
	{
	  var len = getLength(v);
	  if(len==0) 
		 getByClass(document,'tips')[0].innerHTML = "还可以输入<span>100</span>个字";
	  else if(len<=100)
		 getByClass(document,'tips')[0].innerHTML = "还可以输入<span>"+(100-len)+"</span>个字";
	  else
		 getByClass(document,'tips')[0].innerHTML = "您超过了<span>"+(len-100)+"</span>个字";
	}
	oTextarea.onblur=function()
	{
		if(oTextarea.value=='')
		{ 
		  oLabel.style.display='block';
		  oEditwrap.className='editwrap mt22';
		}
	}
    oTextarea.onfocus=function()
	{
		oEditwrap.className='input_focus mt22';
		oLabel.style.display='none';
	}
	oLabel.onclick=function()
	{
		oTextarea.focus();
	}
	
	this.cleanText = function(){
		$(oEditwrap).parent().find('.tips').html("还可以输入<span>100</span>个字");
	};
	return this;
}

//笔记 点评 评论--如果没有计数就用这个
function textarea_box1(oEditwrap)
{
	var oLabel=getByTagName(oEditwrap,'label')[0];
	var oTextarea=getByTagName(oEditwrap,'textarea')[0];
	
	
	var getLength = function(str)
	{
	   return Math.ceil(str.replace(/^\s+|\s+$/ig,'').replace(/[^\x00-\xff]/ig,'xx').length/2);
	};
	oTextarea.onkeyup = function()
	{
		var v = this.value;
	}
	oTextarea.onkeydown = function()
	{
		var v = this.value;
	}
	function getText(v)
	{
	  var len = getLength(v);
	  if(len==0) 
		 getByClass(document,'tips')[0].innerHTML = "还可以输入<span>100</span>个字";
	  else if(len<=100)
		 getByClass(document,'tips')[0].innerHTML = "还可以输入<span>"+(100-len)+"</span>个字";
	  else
		 getByClass(document,'tips')[0].innerHTML = "您超过了<span>"+(len-100)+"</span>个字";
	}
	oTextarea.onblur=function()
	{
		if(oTextarea.value=='')
		{ 
		  oLabel.style.display='block';
		  oEditwrap.className='editwrap';
		}
	}
    oTextarea.onfocus=function()
	{
		oEditwrap.className='input_focus';
		oLabel.style.display='none';
	}
	oLabel.onclick=function()
	{
		oTextarea.focus();
	}
}
// 评论
function reviews(n,id)
{
	var oTk=getById(id);
	var oEditwrap=getByClass(oTk, 'editwrap')[0];
	var oLabel=getByTagName(oEditwrap,'label')[0];

	var oTextarea=getByTagName(oEditwrap,'textarea')[0];
	var oEnter=getByClass(oTk, 'enter')[0];
	
	var oSpan=getByTagName(oEnter,'span')[0];
	//var oTex_c=getByTagName(oEditwrap,'textarea')[0];
	
	var getLength = function(str)
	{
	   return Math.ceil(str.replace(/^\s+|\s+$/ig,'').replace(/[^\x00-\xff]/ig,'xx').length/2);
	};
	oTextarea.onkeyup = function()
	{
		var v = this.value;
		getText(v,n);
	}
	oTextarea.onkeydown = function()
	{
		var v = this.value;
		getText(v,n);
	}
	
	function getText(v,n)
	{
	  var len = getLength(v);
	  if(len==0) 
		 oEnter.innerHTML = "还可以输入<span>"+n+"</span>字";
	  else if(len<=n)
		 oEnter.innerHTML = "还可以输入<span>"+(n-len)+"</span>字";
	  else
		 oEnter.innerHTML = "您超过了<span>"+(len-n)+"</span>个字";
	}
	oTextarea.onblur=function()
	{
		if(oTextarea.value=='')
		{ 
		  oLabel.style.display='block';
		  oEnter.innerHTML='还可以输入<span>'+n+'</span>字';
		  oEditwrap.className='editwrap fl w333 h65';
		  oEditwrap.style.border='solid 1px #CECECE';
		}
	}
    oTextarea.onfocus=function()
	{
		oEditwrap.className='input_focus fl w333 h65';
		oEditwrap.style.border='solid 1px #85C155';
		oLabel.style.display='none';
	}
	oLabel.onclick=function()
	{
		oTextarea.focus();
	}
	var oSureBox=getByClass(oTk, 'sure_box')[0];
	var oBgBtnGray=getByClass(oSureBox,'bg_btn_aa')[0];	
	var oYes=getByClass(oSureBox,'yes')[0];	
	
	oBgBtnGray.onclick = function()
	{
		oTextarea.value='';
		oTk.style.display='none';
		oLabel.style.display='block';
		oEnter.innerHTML='还可以输入<span>'+n+'</span>字';
	}
	var oTextarea=getByTagName(oTk,'textarea')[0];
	
	/* TODO : 还原textarea输入框的字符数量提示*/
	this.cleanText = function(){
		oEnter.innerHTML='还可以输入<span>'+n+'</span>字';
	};
	//返回当前方法-做连贯操作
	return this;
}
//页面跳转
function redirect(kid,zid,cid){
	 
   window.location.href="index.php?app=order&mod=Index&act=video_ml&id="+cid+"&zid="+zid+"&k="+kid;
   video();
}
//视频播放
function video(sclass)
{
	var oCourseLearn=getByClass(document, sclass)[0];
	//alert(aCourseLearn[0].offsetHeight);
	var oLessonnum=getByClass(document, 'cl-lessonnum')[0];
	var aSection=getByClass(document, 'section');
	//alert(aSection.length);
	var oGmn2c=getByClass(document, 'g-mn2c')[0];
	//alert(oGmn2c.offsetHeight);
	var clickNow=0;
	var oPrev=getById("j-prev");
	var oNext=getById("j-next");
	var H=oCourseLearn.offsetHeight;
	var oKsicon=getByClass(document, 'ksicon-0-mark')[0];
	var now=0;
	for(var i=0; i<aSection.length; i++)
	{
		//点击
		aSection[i].index=i;
		aSection[i].onclick=function()
		{
			//clickNow=this.index;
			if(now==this.index) return false;
			var _this=this;
			tab(_this);
		}
	}
	//公共函数
	function tab(obj)
	{
		//找到课程ID
		var lesson = $(obj).attr('data-lesson');
		//找到课程ID
		var lessonid = $(obj).attr('data-lessonid');
		var vtitle   = $(obj).attr('data-title');
		var dprice   = $(obj).attr('data-price');
		var isbuy    = $(obj).attr('data-isbuy');
		
		//初始化播放器
		createObj(lesson,lessonid,vtitle,dprice,isbuy);
		for(var j=0; j<aSection.length; j++)
	    {
		   aSection[j].className='section';
		   var oSection_bj=getByTagName(aSection[j],'div')[0];
		   oSection_bj.style.display='none';
		   var oKsicon=getByTagName(aSection[j],'span')[1];
		   var oKsicon0=getByTagName(aSection[j],'span')[0];
		   var oKsicon2=getByTagName(aSection[j],'span')[2];
		   
			oKsicon0.style.color='';
			oKsicon2.style.color='';
		    oKsicon.className='fl ksicon-0-mark';
	   }
	   obj.className = 'section ahover';
	   
	   var oSection_bj = getByTagName(obj,'div')[0];
	   
	   oSection_bj.style.display = 'block';
	   var oKsicon=getByTagName(obj,'span')[1];
		var oKsicon0=getByTagName(obj,'span')[0];
		var oKsicon2=getByTagName(obj,'span')[2];
	   oKsicon.className = 'fl ksicon-0-mark ksicon-30-mark';
	   oKsicon0.style.color='#FFF';
	   oKsicon2.style.color='#FFF';
	   oLessonnum.innerHTML = (obj.index+1);
	   if(clickNow<obj.index){							
		  next();
	   }else{						
		  prev();
	   }	
	   clickNow=obj.index;	
	   now=clickNow;
	}
	//运动下
	function next(){
		$(oCourseLearn).stop().animate({top:-H,bottom:H}, 1000, function()
		{
			oCourseLearn.style.top=H+'px';
			oCourseLearn.style.bottom=-H+'px';
			$(oCourseLearn).stop().animate({top:'0', bottom:'0'});
		});
	}
	//运动上
	function prev(){
		$(oCourseLearn).stop().animate({top: '800px',bottom:'-800px'}, 500, function()
		{
			oCourseLearn.style.top=-H+'px';
			oCourseLearn.style.bottom=H+'px';
			$(oCourseLearn).stop().animate({top:'0', bottom:'0'});
		});
		
	}
	//开始执行一遍
	prev();
	//上一个、下一个
	oNext.onclick=function(){
		now--;			
		if(now==-1){
			now=0;
			oLessonnum.innerHTML = '1';
			//tag('已经到第一个了','bg_transparent');
			notes('已经到第一个了','failure');
			return false;	
		}
		else
		{
			oLessonnum.innerHTML = now;
			tab(aSection[now]);
		}
	}
	
	var oBtn2=document.getElementById('btn2');
	oBtn2.onclick=oPrev.onclick=function(){			
		now++;
		if(now>=aSection.length)
		{
			now=aSection.length;
			oLessonnum.innerHTML =parseInt(aSection.length);;
			//tag('已经到最后一个了','bg_transparent');
			notes('已经到最后一个了','failure');
			return false;	
		}
		else
		{
			oLessonnum.innerHTML = now;
			tab(aSection[now]);
		}
	}
};
//弹层 及拖拽 鼠标滚动控制弹出在中
function objshow(btnid,objid,closeid,bg)
{
	var aBtn=document.getElementById(btnid);
	var aDiv=document.getElementById(objid);
	var oClose=document.getElementById(closeid);
	var oBg_transparent=document.getElementById(bg);
	
	if(typeof aBtn != 'object' || aBtn == null){
		return;
	}
	
	aBtn.onclick=function()
	{
		if(typeof getChecked != 'undefined' && getChecked instanceof Function){    
			var ids  = getChecked();
			if(!ids.length){
				notes('你还没有选择课程呢！','failure');
				return;
			}
			$("#cash").attr("data-videos",ids);
			if($("#album_title").val() == ''){
				notes('你还没有填写专辑标题','failure');
				$("#album_title").focus();
				return;
			}
			if($("#edittxt").val() == ''){
				notes('你还没有填写专辑简介','failure');
				$("#edittxt").focus();
				return;
			}
		}
		if(typeof getIllegalVideos != 'undefined' && getIllegalVideos instanceof Function){
			var illegal = getIllegalVideos();
			if(illegal > 0){
				notes('课程中包含了已经下架的课程，请先删除','failure');
				return;
			}
		}
		if(typeof getTotalPrice != 'undefined' && getTotalPrice instanceof Function){
			var price = getTotalPrice();
			$("#total_price").html(price);
		}
		
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
		var h=parseInt((document.documentElement.clientHeight- aDiv.offsetHeight)/2)+scrollTop+'px';
		var l=parseInt((document.documentElement.clientWidth- aDiv.offsetWidth)/2)+scrollLeft+'px';
		$(aDiv).stop().animate({top:h,left:l});
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
//视频选择题
function VideoTopics()
{
	var oWritten=getByClass(document,'Test_questions')[0];
	var oQuestions=getByClass(oWritten,'questions');
	var oSarBtn=getByClass(document,'star_btn')[0];
	var oCle=getByClass(oSarBtn,'cle')[0];
	
	for(var i=0; i<oQuestions.length; i++)
	{
		tab_que(oQuestions[i]);
	}
	//单选
	function tab_que(oQuestions)
	{
		var aA=getByTagName(oQuestions,'a');
		for(var i=0; i<aA.length; i++)
		{
			aA[i].onclick=function()
			{
				/*for(i=0; i<aA.length;i++)
				{
				   var oSpan=aA[i].children[0];
				   oSpan.className='';
				}*/
				  var oSpan=this.children[0];
				  if(oSpan.className==''){
					  oSpan.className='Test_true';
				  }else{
					  oSpan.className='';
				  }
			}
		}
	}
	//清空
	oCle.onclick=function()
	{
		var aA1=getByTagName(oWritten,'a');
		
		for(var i=0; i<aA1.length; i++)
		{
			var oSpan1=aA1[i].children[0];
			oSpan1.className='';
		}
	}
}
//详情页的——每条评论
function eachomment(oCommentLi,callback)
{
	var oPl=getByClass(oCommentLi,'pl')[0];
	//var oNext=oCommentLi.nextElementSibling || oCommentLi.nextSibling ;
	var oCmtListRep=getByClass(oCommentLi,'cmt_list_rep')[0];

	var oGuan=getByClass(oCommentLi,'guan')[0];
	//var now=0;
	oPl.onclick=function()
	{
		if(typeof callback != 'undefined' && callback instanceof Function){    
			//xMLHttpRequest.para 服务器回传参数[数组]
			callback(oCommentLi);
		} 
		if(oCmtListRep.style.display=='none' || oCmtListRep.style.display=='')
		{
			
			 $(oCmtListRep).slideDown();
			 var oEm=getByTagName(this,'em')[0];
			 /*now++
			 if(oEm)
			 {
				oEm.innerHTML=parseInt(now); 
			 }*/
		}
		else
		{  
			$(oCmtListRep).slideUp();
		}
	}
}



//————**组合**————//

//组合 选择
//加入购物车
function scheckbox(oBoxMainb)
{
	var oAside=$('#aside');
    var oGoods=$('#Goods');	
	var topics=oBoxMainb.find('.topics');
	if(topics.size()>0)
	{
		topics.each(function(){
			var oSpan=$(this).find('span');
			//移除点击事件
			$(this).unbind('click');
			$(this).click(function(){
				var _this = $(this);
				if(_this.data('locked')){
					return;
				}
				_this.data('locked', true);
				var left = oAside.offset().left;
				var top = oAside.offset().top;
				
				if(oSpan.hasClass('Test_wrong')){
					$.getJSON(U('classroom/Video/addVideoMerge'),
					 {id:_this.attr('data-id')},function(data){
						if(data.status){
							oSpan.removeClass().addClass('Test_true');
							oGoods.css({
								"left":_this.offset().left+"px",
								"top":_this.offset().top+"px"
							}).show().stop().animate({
								"left":(left+18)+"px",
								"top":(top-30)+"px"},
								800,function(){
									oGoods.stop().animate({
										"top":(top+15)+"px"},
										300,function(){
											$(".J_shoping_num").fadeIn();
											var num = $('.J_shoping_num').html();
											$('.J_shoping_num').html(parseInt(num)+1);
											oGoods.fadeOut();
											_this.data('locked', false);
										})
							});
						}
						//_this.data('locked', false);
					});
				}else{
					$.getJSON(U('classroom/Video/delVideoMerge'),
					 {id:_this.attr('data-id')},function(data){
						 if(!data.status) return;
						 oSpan.removeClass().addClass('Test_wrong');
						 $(".J_shoping_num").fadeIn();
						 var num = $('.J_shoping_num').html();
						 $('.J_shoping_num').html(parseInt(num)-1);
						 oGoods.fadeOut();
						 _this.data('locked', false);
					});
				}
			});
		
		});
		
	}
}
function getFilterall()
{
	$(function(){
		$('.selected_clear').click(function(){
		   $(".selected_con").find('ul').html('');
		   $('.propAttrs > div').find('li').each(function(index, element) {
				$(".propAttrs_box").remove();
				$(element).removeClass('green_com');
				$(element).find('a').css({'color':'#999'});
				$(element).find('a').attr('data',$(element).find('a').attr('databack'));
		   });
		});
		$('.propAttrs > div').eq(0).find('li').click(function(){
			getFilter(this);
		});
		
		$('.propAttrs').find('.propAttrs_box .attrValues li').live('click',function(){
			  getFilter(this);
		});
		
		function getFilter(obj)
		{

			//移除当前点击li的所有同兄弟className
		    $(obj).parent().find('li').removeClass();
			$(obj).parent().find('li').find('a').css({'color':'#999'});
			
			$(obj).addClass('green_com');
			$(obj).find('a').css({'color':'#FFF'});
			var cid = $(obj).children().attr("data");
			$.post(U('classroom/Video/getCategoryData'),{cid:cid},function(txt){
				$(obj).children().removeAttr("data");
				if(txt.status == 0){ 
					return false
				} else {
					var percate = '';
					$.each(txt.list.data,function(index,vo){
						percate  += '<li>'
							+'<a href="javascript:;" data="'+vo.zy_video_category_id+'">'+vo.title+'</a>'
							+'</li>';
					});
					var insertCateHtml = '<div class="propAttrs_box" style="display:block;">'
									+ '<div class="attr clearfix">'
									+ '<div class="attrKey">'+txt.list.next_name+'：</div>'
									+ '<div class="attrValues mtb5 clearfix">'
									+ '<ul class="av-collapse clearfix">'
									+ percate
									+ '</ul>'
									+ '</div>'
									+ '</div>'
									+ '</div>';
					}
					$(obj).closest('div.attr').after(insertCateHtml);	
			},'json');
			
			//设置选项卡的显示
			var sn = $('.propAttrs > div').eq(0).find('.green_com').index() < 0 ? 1 : $('.propAttrs > div').eq(0).find('.green_com').index()+1;
			
			$('.propAttrs > div').each(function(i, element) {
                if(i>0)
				{
					if(i==sn)
					{
					  $(element).show();
					}
					else
					{
					   $(element).hide();
					}
			    }
            });
			
			//获取所筛选条件
			$(".selected_con").find('ul').html('');
			var str = '';
			
			$('.propAttrs > div').eq(0).find('.green_com').each(function(index, element) {
                var class_name = $(element).find('a').html();
			    var cat_name   = $(element).parents('.attrValues').prev().html();
			    str+= '<li>';
			    str+= '<a href="javascript:;"><strong> '+cat_name+'</strong><span>'+class_name+'</span></a>';
			    str+= '<em class="vl5"></em></li>';
            });
			
			$('.propAttrs > div').eq(sn).find('.green_com').each(function(index, element) {
                var class_name = $(element).find('a').html();
			    var cat_name   = $(element).parents('.attrValues').prev().html();
			    str+= '<li>';
			    str+= '<a href="javascript:;"><strong> '+cat_name+'</strong><span>'+class_name+'</span></a>';
			    str+= '<em class="vl5"></em></li>';
            });
			$(".selected_con").find('ul').html(str);
		}
	});
}
 //组合里的更多 收起----组合
 function attr_move()
 {
	var aAttrValues=getByClass(document,'attrValues');
	for(var i=0; i<aAttrValues.length; i++)
	{
		var oMoreZh=getByClass(aAttrValues[i],'more_zh')[0];
		if(oMoreZh)
		{
			downup(aAttrValues[i]);
		}
	}
	function downup(aAttrValues)
	{
		var oAvCollapse=getByClass(aAttrValues,'av-collapse')[0];
		oMoreZh.onclick=function()
		{
			if(oAvCollapse.style.height=='auto')
			{
				this.className='more_zh';
				oAvCollapse.style.height='34px';
				this.innerHTML='更多';
			}
			else
			{
				this.className='more_zh1';
				oAvCollapse.style.height='auto';
				this.innerHTML='收起';
			}
		}
		oMoreZh.onmousedown=function()
		{
			return false;
		}
	}
 }
//排序 销量评论--组合
function Sort_Sales()
{
	var oPxBox=getByClass(document,'px_box1')[0];
	var aLi=getByTagName(oPxBox,'li');
	for(i=0; i<aLi.length; i++)
	{
		(function(index){
			aLi[i].onclick=function()
			{
				for(i=0; i<aLi.length; i++)
				{
					aLi[i].children[0].className='';
				}
				aLi[index].children[0].className='sel_def';
				if(this.className=='bg_down')
				{
					this.className='bg_up';
				}
				else
				{
					this.className='bg_down';
				}
			}	  
		})(i);
	}
}
 //单选
 function danxuan()
 {
	var oTable_dl=getByClass(document,'table_dl')[0];
	var oDt=getByTagName(oTable_dl,'dt')[0];
	var oInput_all=getByClass(oDt,'Test_true_a')[0];
	var oSelectedClear=getByClass(document,'selected_clear')[0];
	
	var oDd=getByTagName(oTable_dl,'dd');
	//全选
	 oInput_all.onclick=function()
	 {
		if(this.className=='Test_true_a txt_l')
		{
			//全选取消选中
			this.className='select_all txt_l';
			for(var i=0; i<oDd.length; i++)
			{	
				oDd[i].getElementsByTagName('span')[0].className="kk select_all txt_l ";
				//把对应的隐藏域赋值为0
				$(oDd[i]).find('input[type="hidden"]').val(0).attr("price","0");
				oDd[i].getElementsByTagName('span')[0].onclick=function()
				{
					
					if(this.className=="Test_true_a")
					{
						//取消对应的隐藏域值
						$(this).next().val(0).attr("price","0");
						this.className="kk select_all txt_l";
					} 
					else 
					{
						//对应的隐藏域值
						var vid = $(this).attr('data-vid');
						var price = $(this).attr('data-price');
						$(this).next().val(parseInt(vid)).attr("price",parseFloat(price));
						this.className="Test_true_a";
					}
				};	
			}
		} 
		else 
		{
			//全选选中
			this.className='Test_true_a txt_l';
			for(var i=0; i<oDd.length; i++)
			{
				oDd[i].getElementsByTagName('span')[0].className="Test_true_a txt_l";
				//把对应的隐藏域赋值
				var vid = $(oDd[i]).find('span[name="mzcheckbox"]').attr('data-vid');
				var price = $(oDd[i]).find('span[name="mzcheckbox"]').attr('data-price');
				$(oDd[i]).find('input[type="hidden"]:first').val(parseInt(vid)).attr("price",parseFloat(price));
				oDd[i].getElementsByTagName('span')[0].onclick=function()
				{
					
					if(this.className=="kk select_all txt_l")
					{
						//对应的隐藏域值
						var vid = $(this).attr('data-vid');
						var price = $(this).attr('data-price');
						$(this).next().val(parseInt(vid)).attr("price",parseFloat(price));
						this.className="Test_true_a txt_l";
					} 
					else 
					{
						//取消对应的隐藏域值
						$(this).next().val(0).attr("price","0");
						this.className="kk select_all txt_l";
					}
				};	
			}
			 
		}
	 }
	 for(var i=0; i<oDd.length; i++)
	 {		
		oDd[i].getElementsByTagName('span')[0].onclick=function()
		{
			
			if(this.className=="Test_true_a txt_l")
			{	
				$(this).parent().find("input").val(0).attr("price","0");
				this.className="kk select_all txt_l";
			} 
			else 
			{
				var vid = $(this).parent().find("span:first").attr('data-vid');
				var price = $(this).parent().find("span:first").attr('data-price');
				$(this).parent().find("input:first").val(parseInt(vid)).attr("price",parseFloat(price));
				this.className="Test_true_a txt_l";
			}
		};
		oDd[i].getElementsByTagName('span')[0].onmousedown=function()
		{
			return false;
		}
	}
	for(var i=0; i<oDd.length; i++)
	{
		var oPerating=getByClass(oDd[i],'operating')[0];
		oPerating.onclick=function()
		{
			var _this = $(this);
			var video_id = _this.attr("data-id");
			if(confirm("确认删除")){
				$.get(U('classroom/Video/delVideoMerge'),{id:video_id},function(txt){
					if(txt.status){
						_this.parent().remove();
					} else {
						
					}
				},'json');
			}
		}
	}
	oSelectedClear.onclick=function()
	{
		
		$(".table_dl dd").remove();
	}
	oInput_all.onmousedown=function()
	{
		return false;
	}
	}


//模拟ts U函数
function U(url,params){
	var website = _ROOT_+'/index.php';
	url = url.split('/');
	if(url[0]=='' || url[0]=='@')
		url[0] = APPNAME;
	if (!url[1])
		url[1] = 'Index';
	if (!url[2])
		url[2] = 'index';
	website = website+'?app='+url[0]+'&mod='+url[1]+'&act='+url[2];
	if(params){
		params = params.join('&');
		website = website + '&' + params;
	}
	return website;
}





//换一换
function aTab(oChange)
{
	var oCourseSwap=getByClass(oChange,'course_swap')[0];
	
	var oHotCourse=getByClass(oChange,'hotCourse');
 
	var iNow=0;
	function tab()
	{
		for(var i=0; i<oHotCourse.length; i++)
		{
			oHotCourse[i].style.display='none';
		}
		oHotCourse[iNow].style.display='block';
	}
	oCourseSwap.onclick=function()
	{
		iNow++;
		if(iNow==oHotCourse.length){
			iNow=0;	
		}
		tab();	
	}
}



//****首页*****//
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
//首页播图
function Figure()
{
	$(function(){
	 var num = 0;
	 var timer=null;
     $(".bannermenu .main").find('li').mouseover(function(){
		num = $(this).index();
        showPic(this);
     });
	 function tabs(){
	    var _this = $(".bannermenu .main").find('li').eq(num);
	    showPic(_this);
		num++;
		if(num>=$(".bannermenu .main").find('li').length)
		{
		   num = 0;
		}
	 }
	timer=setInterval(tabs,5000);
	  function showPic(_this)
	  {
		 $(_this).addClass('on').siblings().removeClass('on');
		 $(".bannermenu .main").find('li span').addClass('main_span');
		 $(_this).find('span').removeClass('main_span').hide();
		 $('.bannerc').find('li').stop().animate({opacity:0,zIndex:0})
		 $('.bannerc').find('li').eq(num).stop().animate({opacity:1,zIndex:10});
	  }
	$("#bannerbg").hover(
		function () {
			clearInterval(timer);	
		},
		function () {
		   timer=setInterval(tabs,5000);
		}
	);
})
}
//*****个人中心********//


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
					var id    = $(this).attr('data-id');
					//1:课程;2:专辑;
					var type  = $(this).attr('data-type');
					//1:购买的;2:收藏的;3：上传的
					var rtype = $(this).attr('data-rtype');
					
					delVideoAndAlbumForId(id,type,rtype);
					this.parentNode.style.display='none';
				}
			}
		}
   }
}
//个人中心——导航
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
			tag('请勾选Eduline课程分成协议','transparent');
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







var mzgaojiaowang = function(){
	var self = this;
	self.defaults = {
		'dtime':1250//默认提示时间	
	};
	self._document = document;
}

//把专辑分享到点播去
mzgaojiaowang.prototype.shareToDianBo = function(_this,$id){
	var url = U('classroom/Album/sharetodianbo');
	$.ajax({
		type:'POST',
		url:url,
		data:{id:$id},
		dataType:"json",
		cache: false,
		success: function(xMLHttpRequest, textStatus, errorThrown){
			mzgaojiaowang.ajaxDone(xMLHttpRequest, textStatus, errorThrown);
			if(xMLHttpRequest.status == 1){
				$(_this).remove();
			}
		},
		error: function(xhr, ajaxOptions, thrownError){
			mzgaojiaowang.ajaxError(xhr, ajaxOptions, thrownError);
		}
	});
};

//设置学习状态
//课程ID
//type
mzgaojiaowang.prototype.mystudyvideo = function($vid,$type,callback){
	var url = U('classroom/Public/studyvideo');
	$.ajax({
		type:'POST',
		url:url,
		data:{vid:$vid,type:$type},
		dataType:"json",
		cache: false,
		success: function(xMLHttpRequest, textStatus, errorThrown){
			if(typeof callback != 'undefined' && callback instanceof Function){    
				callback(xMLHttpRequest);
			}
		},
		error: function(xhr, ajaxOptions, thrownError){
			mzgaojiaowang.ajaxError(xhr, ajaxOptions, thrownError);
		}
	});
};

//删除问题和笔记
mzgaojiaowang.prototype.delResource = function(_this,$mid,$id,$type){
	var url = U('classroom/Public/delresource');
	$.ajax({
		type:'POST',
		url:url,
		data:{mid:$mid,id:$id,type:$type},
		dataType:"json",
		cache: false,
		success: function(xMLHttpRequest, textStatus, errorThrown){
			mzgaojiaowang.ajaxDone(xMLHttpRequest, textStatus, errorThrown);
		},
		error: function(xhr, ajaxOptions, thrownError){
			mzgaojiaowang.ajaxError(xhr, ajaxOptions, thrownError);
		}
	});
};




//收藏和取消收藏
mzgaojiaowang.prototype.mycollect = function($type,$sctype,$source_id,callback){
	var url = U('classroom/Public/collect');
	$.ajax({
		type:'POST',
		url:url,
		data:{type:$type,sctype:$sctype,source_id:$source_id},
		dataType:"json",
		cache: false,
		success: function(xMLHttpRequest, textStatus, errorThrown){
			mzgaojiaowang.ajaxDone(xMLHttpRequest, textStatus, errorThrown);
			if(typeof callback != 'undefined' && callback instanceof Function){    
				setTimeout(function(){callback(xMLHttpRequest);},1255);
			}
		},
		error: function(xhr, ajaxOptions, thrownError){
			mzgaojiaowang.ajaxError(xhr, ajaxOptions, thrownError);
		}
	});
};

mzgaojiaowang.prototype.ajaxDone = function(xMLHttpRequest, textStatus, errorThrown){
	//• ui.alert | ui.error | ui.success | ui.notice 方法
	try{
		if(xMLHttpRequest.status == 1){
			//成功!
			//ui.success(xMLHttpRequest.info);
			//tag1(xMLHttpRequest.info,'transparent2');
			notes(xMLHttpRequest.info,'success');
		}else if(xMLHttpRequest.status == 2){
			//失败!
			//ui.error(xMLHttpRequest.info);
			//tag(xMLHttpRequest.info,'transparent2');
			notes(xMLHttpRequest.info,'failure');
		}else if(xMLHttpRequest.status == 0){
			//错误!
			//tag(xMLHttpRequest.info,'transparent2');
			//ui.error(xMLHttpRequest.info);
			notes(xMLHttpRequest.info,'failure');
		}
		
		if(xMLHttpRequest.referer){
			setTimeout(function(){
				//刷新本页面
				if(xMLHttpRequest.referer == 'selfhref'){
					window.location.href = window.location.href;	
				}else if(xMLHttpRequest.referer == 'selfindexhref'){
					var mz_href = window.location.href;
					if(mz_href.indexOf('user') !== -1){
						window.location.href = '/';	
					}else{
						window.location.href = window.location.href;		
					}
				}else{
					window.location.href = xMLHttpRequest.referer;	
				}
			},1255);
		}
	}catch(e){
		//tag(xMLHttpRequest.info,'transparent2');
		//ui.error(e.toString());return;
		notes(xMLHttpRequest.info,'failure');
	}
};
mzgaojiaowang.prototype.ajaxError = function(xhr, ajaxOptions, thrownError){
	notes("Http status: " + xhr.status + " " + xhr.statusText + "\najaxOptions: " + ajaxOptions + "\nthrownError:"+thrownError + "\n" +xhr.responseText,'transparent2','failure');
	//tag("Http status: " + xhr.status + " " + xhr.statusText + "\najaxOptions: " + ajaxOptions + "\nthrownError:"+thrownError + "\n" +xhr.responseText,'transparent2');
	//ui.error();
}


var mzgaojiaowang = new mzgaojiaowang();
//console.log(mzgaojiaowang);


/**
 * 普通ajax表单提交
 * @param {Object} form
 * @param {bool} isValidate  是否验证
 * @param {string} validatorGroup
 * @param {function} callback
 */
function j_validateCallback(form,call,callback) {
	var $form = $(form);
	if(typeof call != 'undefined' && call instanceof Function){    
		$i = call($form);
		if(!$i){
			return false;
		}
	}
	var _submitFn = function(){
		$.ajax({
			type: form.method || 'POST',
			url:$form.attr("action"),
			data:$form.serializeArray(),
			dataType:"json",
			cache: false,
			success: function(xMLHttpRequest, textStatus, errorThrown){
				mzgaojiaowang.ajaxDone(xMLHttpRequest, textStatus, errorThrown);
				if(typeof callback != 'undefined' && callback instanceof Function){    
					//xMLHttpRequest.para 服务器回传参数[数组]
					callback($form,xMLHttpRequest.data,xMLHttpRequest);
				}  
			},
			error: function(xhr, ajaxOptions, thrownError){
				mzgaojiaowang.ajaxError(xhr, ajaxOptions, thrownError);
			}
		});
	}
	_submitFn();
	return false;
}



//注册三步曲
function Login_Seite()
{
	
	var oNavBar=getByClass(document,'Nav_bar')[0];
	var aBtn=getByTagName(oNavBar,'li');
	var oManageAll=getByClass(document, 'Manage_all'); 
	var oNachste=getByClass(document,'nachste')[0];
	
	var oManage_all_s=getByClass(document,'School_for')[0];
	var aA=getByTagName(oManage_all_s,'a');
	for(var i=0; i<aA.length; i++)
	{
		Nav_bar_com1(aA[i],aBtn[1],oManageAll[1]);
		
		
	}
	
	function Nav_bar_com1(obj,btn,obj_tab)
	{
		obj.onclick=function()
		{
			for(var i=0; i<aBtn.length; i++)
			{
				$(this).find('b').addClass('Select_a');
				$(this).siblings().find('b').removeClass('Select_a');
				
			}
		var val = $(obj).find('input').val();
		
		var url = U('home/Register/dostep2');
		$.post(url, {xueshen:val}, function(res){
	    	 
	 
		});
		}
	}
	/*$('.Manage_all').eq(0).find('.four').click(function(){
		$(this).find('b').addClass('Select_a');
		$(this).siblings().find('b').removeClass('Select_a');
	});
	*/
	//上一步
	$('.Manage_all').eq(1).find('.prev').click(function()
	{
		var flag = false;
		nextShow(0);
	});
	//上一步
	$('.Manage_all').eq(2).find('.prev').click(function()
	{
		var flag = false;
		nextShow(1);
	});
	
	
	/*$('.Manage_all').eq(2).find('.Complete').click(function(){
	    notes('完成','success');
	});*/
	//第三步
	
	$('.nva_cent_s').find('li').click(function(){
	   $(this).addClass('tit_list').siblings().removeClass('tit_list');
	   var index = $(this).index();
	   $('.Manage_All_box').each(function(i, element) {
            if(i==index) {
			   $(element).css({'display':"block"});
			   $('input[name=controlAll]',$(element)).click(function(){
				  var _this = $(this);
			      $("input[name=selected]",$(element)).each(function(m, ele) {
                      $(ele).attr('checked',typeof(_this.attr('checked'))=='undefined' ? false : true);
                  });
			   });
			} else {
			   $(element).css({'display':"none"});
			}
       });
	});
	
	
		$('.Manage_all').eq(0).find('.next').click(function()
		{
			var flag = false;
			
			$('.Manage_all').eq(0).find(".School_for").find('a').find('b').each(function(index, element) {
                
				if($(element).hasClass('Select_a'))
				{
					
					flag = true;
					nextShow(1);
				}
            });
			if(!flag)
			{
				notes('请选择','failure');
			}
		});
		//1.
		$('.tag_active1').find('a').click(function(){
			$(this).addClass('Notes_green_bg').siblings().removeClass('Notes_green_bg');
		});
		//2.
		$('.Manage_all').eq(1).find('.next').click(function()
				{
					var flag = [];
					$('.Manage_all').eq(1).find('.tag_active1').find('a').each(function(index, element) {
		                if($(element).hasClass('Notes_green_bg'))
						{
						   flag.push(1);
						}
		            });
					$('.Manage_all').eq(1).find('.tag_active').find('a').each(function(index, element) {
		                if($(element).hasClass('Notes_green_bg'))
						{
						   flag.push(2); 
						}
		            });
					
					if(flag.length<2)
					{
						var msg = typeof(flag[0])=='undefined' ? '选择你的学习项目' : (flag[0]==1 ? '选择你的学习阶段' : '选择你的学习项目');
						notes(msg,'failure');
					}
					else
					{
						nextShow(2);
						$('.nva_cent_s').find('li').eq(0).click();
					}
					
				});
		var aTag_active=getByClass(document,'tag_active')[0];
		var aA1=getByTagName(aTag_active,'a');
		for(var i=0; i<=aA1.length; i++)
		{
			Nav_bar_com2(aA1[i],aBtn[2],oManageAll[2]);
			
		} 
		
		function Nav_bar_com2(obj,btn,obj_tab)
		{
			obj.onclick=function()
			{
				for(var i=0; i<aBtn.length; i++)
				{
					$(this).addClass('Notes_green_bg').siblings().removeClass('Notes_green_bg');
					
				}
			
			var val = $(obj).find('input').val();
			
			var url = U('home/Register/dostep3');
			$.post(url, {jieduan:val}, function(res){
				var arr =res.split(","); //字符分割 
				document.getElementById("tu").innerHTML= arr[1];//相同吐槽
				document.getElementById("guanzhu").innerHTML= arr[0];//相同阶段
				
				
				
		 
			});
			}
		}
		/*$('.tag_active').find('a').click(function(){
			
			$(this).addClass('Notes_green_bg').siblings().removeClass('Notes_green_bg');
		});*/

		//公用
		function nextShow(index)
		{
		    $('.Nav_bar').find('li').eq(index).addClass('sel_cent_r green_txt').siblings().removeClass('sel_cent_r green_txt');
			$('.Manage_all').each(function(i, element) {
                if(i==index) {
				   $(element).css({'display':"block"});
				} else {
				   $(element).css({'display':"none"});
				}
            });
		}
}
//全选 全不选
function selectAll(){
   var checklist = document.getElementsByName ("selected");
   if(document.getElementById("controlAll").checked)
   {
	   for(var i=0;i<checklist.length;i++)
	   {
		  checklist[i].checked = 1;
	   } 
   }
   else
   {
	  for(var j=0;j<checklist.length;j++)
	  {
		 checklist[j].checked = 0;
	  }
   }
}
//系列轮播——按钮
function Serie_Knopf_tab()
{
	var oTigure_Icon_ul=getByClass(document,'figure_Icon_ul')[0];
	var aLi=getByTagName=getByTagName(oTigure_Icon_ul,'li');
	for(var i=0; i<aLi.length; i++)
	{
		Serie_Knopf(aLi[i]);
	}
	function Serie_Knopf(aLi)
	{
		aLi.onmouseover=function()
		{
			
			var oFigureshowspan=getByClass(this,'figure_show_span')[0];
			var oFigureShowFx=getByClass(this,'figure_show_fx')[0];
			
			
			if(oFigureshowspan)
			{    
			    oFigureshowspan.style.display='block';
				$(oFigureshowspan).stop().animate({width:'38px'});
			}
			if(oFigureShowFx)
			{
				oFigureShowFx.style.display='block';
				$(oFigureShowFx).stop().animate({width:'138px'});
			}
		}
		aLi.onmouseout=function()
		{
			var oFigureshowspan=getByClass(this,'figure_show_span')[0];
			var oFigureShowFx=getByClass(this,'figure_show_fx')[0];
			
			
			if(oFigureshowspan)
			{
				oFigureshowspan.style.display='block';
				$(oFigureshowspan).stop().animate({width:'0'});
			}
			if(oFigureShowFx)
			{ 
			    oFigureShowFx.style.display='block';
				$(oFigureShowFx).stop().animate({width:'0'});
			}
			
		}
	}
}

//系列轮播图
function Serie_Carousel()
{
	
	var oUl=$('.figure_bar ul');
	var aLi=$('.figure_bar ul li');
	
	//var oH=$('.figure_bar ul li').find('.Graphic_box_a').outerHeight();
	var oBtnPrev=$('.figure_bar .figure_prev');
	var oBtnNext=$('.figure_bar .figure_next');
	oBtnPrev.css('margin-top', -$('.figure_bar .figure_prev').outerHeight()/2+'px');
	oBtnNext.css('margin-top', -$('.figure_bar .figure_prev').outerHeight()/2+'px');
	$('.figure_box').css('height', oUl.children().find('img').outerHeight()+'px');
	
	oUl.html(oUl.html()+oUl.html());
	
	oUl.css('width', oUl.children().outerWidth()*oUl.children().length+'px');
	
	var w=oUl.width()/2;
	var now=0;
	var str='';
	aLi.each(function(index, element) {
		str+= '<a href="javascript:;"></a>';
	}); 
	
	$('.focus_tag').html(str);
	$('.focus_tag a').eq(0).addClass('current');
	var aBtn=$('.focus_tag a');
	$(aBtn).live('click',function(){
		now=parseInt(now/aBtn.length)*aBtn.length+$(this).index();
		tab();
	});
	var Focus_tag=$('.focus_tag');
	Focus_tag.css('margin-left', -(Focus_tag.children().width()*Focus_tag.children().length)+'px');
	$('.figure_bar ul li').each(function(index, element){
		var str1='';
		if(index>=aLi.length)
		{
			index=0;
		}
		str1+= '<strong>phone<em><br/>'+(index+1)+'</em>/<em>'+aLi.length+'</em></strong>';
		$(this).find('.re_box').html(str1);
		$(this).mouseover(function(){
			$(this).find('p').css('white-space','normal');	
			//$(this).find('.Graphic_box_a').stop().animate({'height':$(this).find('.Graphic_box_a').outerHeight()+'px'});
	    });
		$(this).mouseout(function(){
			$(this).find('p').css('white-space','nowrap');
			//$(this).find('.Graphic_box_a').stop().animate({'height':oH+'px'});
	    });
		var content=$(this).find('p').html();
		var reg = /(http:\/\/|https:\/\/)((\w|=|\?|\.|\/|&|-)+)/g;
	    content = content.replace(reg, "<a href='$1$2' target='_blank'>$1$2</a>");
		$(this).find('p').html(content);
	}); 
	
	var oBody=document.getElementsByTagName('body')[0];
	$('.figure_box').mouseover(function(){
		oBody.style.overflow='scroll';
		oBody.style.overflowY='hidden';
	});
	/*$('.figure_box').mouseout(function(){
		oBody.style.overflow='';
		oBody.style.overflowY='';
	});*/
	function tab()
	{
		$(aBtn).each(function(index, element) {
			$(element).removeClass('current');
		});
		if(now>0)
		{
			aBtn.eq(now%aBtn.length).addClass('current');
		}
		else
		{
			aBtn.eq((now%aBtn.length+aBtn.length)%aBtn.length).addClass('current');
		}
		startMove(oUl, -$('.figure_bar ul li').eq(0).outerWidth()*now);
	}
	oBtnPrev.click(function(){
		now--;
		tab();
	});
	oBtnNext.click(function(){
		now++;
		tab();
	});
	/*$('.figure_bar').bind('mousewheel', function(event, delta, deltaX, deltaY){
		if(delta==1)
		{
			now--;
			tab();
			return false;
		}
		else
		{
			now++;
			tab();
			return false;
		}
	});*/
	
	var left=0;
	//运动框架
	function startMove(obj, iTarget)
	{
		clearInterval(obj.timer);
		obj.timer=setInterval(function(){
			var speed=(iTarget-left)/8;
			speed>0?Math.ceil(speed):Math.floor(speed);
			if(left==iTarget)
			{
				clearInterval(obj.timer);
			}
			left+=speed;
			//document.title=now;
			if(left<0)
			{
				obj.css('left',left%w+'px');
			}
			else
			{
				obj.css('left',(left%w-w)%w+'px');
			}
		},30);
	}
	
}




//系列——图文移入移出
function Graphic_serial(oPic_x)
{
	oPic_x.onmouseover=function()
	{
		oGraphicBox=getByClass(this,'Graphic_box')[0];
		$(oGraphicBox).stop().animate({bottom:0});
	}
	oPic_x.onmouseout=function()
	{
		oGraphicBox=getByClass(this,'Graphic_box')[0];
		$(oGraphicBox).stop().animate({bottom:-55});
	}
} 



//鼠标滑动连载
function lian_z_tab()
{
	window.onscroll=function()
	{
		var scrollTop=document.documentElement.scrollTop || document.body.scrollTop;
		
		if(scrollTop>200){
			$('.lis_l').fadeIn();
		}else {
			$('.lis_l').fadeOut();
		}
	}
	var str ='';
	var oList_ul=$('.list_ul');
	var aLi=$('.phone_list_ul li');
	aLi.each(function(index, element){
		str+= '<li>';
		str+= '<span>phone<em>'+(index+1)+'</em>/<em>'+aLi.length+'</em></span>';
		str+= '</li>';
		var str1='';
		str1+= '<span>phone<em>'+(index+1)+'</em>/<em>'+aLi.length+'</em></span>';
		$(this).find('.sel_w').html(str1);
		var content=$(this).find('.phone_list_ul_p').html();
		var reg = /(http:\/\/|https:\/\/)((\w|=|\?|\.|\/|&|-)+)/g;
	    content = content.replace(reg, "<a href='$1$2' target='_blank'>$1$2</a>");
		$(this).find('.phone_list_ul_p').html(content);
	}); 
	oList_ul.html(str);
	
	$('.list_ul li').eq(0).addClass('jt_r_lv').children().addClass('bg_lan').siblings().removeClass('jt_r_lv').children().removeClass('bg_lan');	
	$('.list_ul li').live('click',function(){
		 var index = $(this).index();
		 $(this).addClass('jt_r_lv').siblings().removeClass('jt_r_lv');
		 $(this).find('span').addClass('bg_lan');
		 $(this).siblings().find('span').removeClass('bg_lan');
		 
		 $('body,html').stop().animate({scrollTop:getPos(aLi[index]).top-50},1000); 
	});
	$('.jt_tb a').eq(0).click(function(){

		$('.list_ul').find('.jt_r_lv').prev().click();
	});
	$('.jt_tb a').eq(1).click(function(){
		
		$('.list_ul').find('.jt_r_lv').next().click();
	});
	$('.phone_list_ul').bind('mousewheel', function(event, delta, deltaX, deltaY){
		if(delta==1)
		{
			$('.list_ul').find('.jt_r_lv').prev().click();
			return false;
		}
		else
		{
			$('.list_ul').find('.jt_r_lv').next().click();
			return false;
		}
	});
	var listUl=$('.list_ul');
	$('.lis_l').css({'height':listUl.children().outerHeight()*listUl.children().length+120+'px'});
	$('.lis_l').css({'margin-top':-parseInt(($('.lis_l').outerHeight())/2)+'px'});
   }


//横向布局 ——连载
function serial_z_story()
{
	var oMenContent=getByClass(document,'men_content')[0];
	var oMen_slide_item=getByClass(oMenContent,'men_slide_item')[0];
	var oMen_list_content=getByClass(oMen_slide_item,'men_list_content')[0];
	var oMen_list_div=oMen_list_content.children;
	
	//计算宽度
	  w1=oMen_list_div[0].offsetWidth*oMen_list_div.length;
	  //alert(w1);
	 oMen_list_content.style.width=w1+'px'; 
	
	//计算高度
	var h=oMenContent.offsetHeight;
	oMenContent.style.marginTop=parseInt((document.documentElement.clientHeight-h)/2)+'px';
}


//部分收起 部分隐藏
function limt_show()
{
	var oShowAllBox=getByClass(document,'Show_all_box')[0];
	var oContentTxt=getByClass(document,'content_txt')[0];
	var oA=getByTagName(oShowAllBox,'a')[0];
    //$(oContentTxt).find('p').css({'height':'100px'});
	var h=$(oContentTxt).outerHeight();
    if(h<100)
	{
		$('.Show_all_box').hide();
	}   
	else
	{
		$('.Show_all_box').show();
		$(oContentTxt).css({'height':'100px'});
		oA.onclick=function()
		{
			if(this.className=='Show_all2 pr10')
			{
				this.className='Show_all1 pr10';
				this.innerHTML='显示全部';
				$(oContentTxt).stop().animate({height:'100px'});
			}
			else
			{
				this.className='Show_all2 pr10';
				this.innerHTML='部分隐藏';
				$(oContentTxt).stop().animate({height:h});
				
			}
		}
	}
}


//横向布局 ——连载2
function serial_z_story2()
{
	var oMenContent=getByClass(document,'men_content')[0];
	var oMen_slide_item=getByClass(oMenContent,'men_slide_item')[0];
	var oMen_list_content=getByClass(oMen_slide_item,'men_list_content')[0];
	var oMen_list_div=oMen_list_content.children;
	
	//计算宽度
	  w1=oMen_list_div[0].offsetWidth*oMen_list_div.length;
	  oMen_list_content.style.width=w1+'px'; 
	
	//计算高度
	var h=oMenContent.offsetHeight;
	oMenContent.style.marginTop=parseInt((document.documentElement.clientHeight-h)/2)+'px';
}



//邀请登录注册
function Invited_Login()
{
	$("#pageflip").hover(function() {
	$("#pageflip .pageflip_img").stop()
		.animate({
			width: '144px', 
			height: '138px'
		}, 500);
		$(".msg_block").stop()
		.animate({
			width: '134px', 
			height: '130px'
		}, 500);  
	} , function() {
	$("#pageflip .pageflip_img").stop() 
		.animate({
			width: '93px', 
			height: '84px'
		}, 220);
	$(".msg_block").stop() 
		.animate({
			width: '90px', 
			height: '81px'
		}, 200);
 });
 
	$('#pageflip').click(function(){
		var html = 	$(this).find('.text_singn_In').html();
		if(html == '登录'){
			$(this).find('.text_singn_In').html('注册');
			$('#longin_com_b').show();	
			$('#longin_com_a').hide();
			$('.msg_block').find('img').attr({src:THEME_URL+"/images/subscribe_green.png"}); 
			//回到初始状态
			$('.longin_com_b .input_div').css({'border':'solid #999 1px'}).find('input').html('');
			$('.longin_com_b .input_div').find('span').show();
		}else{
			$(this).find('.text_singn_In').html('登录');
			$('#longin_com_a').show();
			$('#longin_com_b').hide();
			$('.msg_block').find('img').attr({src:THEME_URL+"/images/subscribe_yelow.png"});
			//回到初始状态
			
			$('.longin_com_a .input_div').css({'border':'solid #999 1px'}).find('input').html('');
			$('.longin_com_a .input_div').find('span').show();
			$('.longin_com_a .Prompt1').hide();
			
			if($('.topics1 b').hasClass('Test_True')){
				$('.topics1 b').removeClass().addClass('Text_error');
			}
		}
	});
 
	/*$('#pageflip').toggle(function(){
		$(this).find('.text_singn_In').html('登录');
		$('#longin_com_a').show();
		$('#longin_com_b').hide();
		$('.msg_block').find('img').attr({src:THEME_URL+"/images/subscribe_yelow.png"});
		//回到初始状态
		
		$('.longin_com_a .input_div').css({'border':'solid #999 1px'}).find('input').html('');
		$('.longin_com_a .input_div').find('span').show();
		$('.longin_com_a .Prompt1').hide();
		
		if($('.topics1 b').hasClass('Test_True')){
			$('.topics1 b').removeClass().addClass('Text_error');
		}
	},function(){
		$(this).find('.text_singn_In').html('注册');
		$('#longin_com_b').show();	
		$('#longin_com_a').hide();
		$('.msg_block').find('img').attr({src:THEME_URL+"/images/subscribe_green.png"}); 
		//回到初始状态
		$('.longin_com_b .input_div').css({'border':'solid #999 1px'}).find('input').html('');
		$('.longin_com_b .input_div').find('span').show();
	});*/
}
/*IE6版本升级通知*/
function Warning(box)
{
	var scrollTop=document.body.scrollTop || document.documentElement.scrollTop;
	var scrollLeft=document.body.scrollLeft || document.documentElement.scrollLeft;
	box.style.display='block';
	box.style.left=(document.documentElement.clientWidth- box.offsetWidth)/2+scrollLeft+'px';
	box.style.top=(document.documentElement.clientHeight- box.offsetHeight)/2+scrollTop+'px';
	box.style.zIndex='1001';
	
	function addE()
	{
		var scrollTop=document.body.scrollTop || document.documentElement.scrollTop;
		var scrollLeft=document.body.scrollLeft || document.documentElement.scrollLeft;
		
		move(box, {top: parseInt((document.documentElement.clientHeight- box.offsetHeight)/2)+scrollTop},'buffer',null);
	}
	addEvent(window,'resize',addE);
	addEvent(window,'scroll',addE);
}
