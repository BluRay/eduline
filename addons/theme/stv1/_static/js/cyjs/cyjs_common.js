// JavaScript Document
function higher_common()
{
	(function(){
		 //alert(typeof window.navigator.userAgent);
		if(window.navigator.userAgent.indexOf('MSIE 6.0')!=-1){
			if($('.Service_launched'))
			{
				$('.Service_launched').hide();
			}
			var oWarning=document.createElement('div');
			oWarning.className='ie6-warning';
			var oBody=document.getElementsByTagName('body')[0];
			oBody.appendChild(oWarning);
			str='';
			str='您正在使用 Internet Explorer 6，在本页面的显示效果可能有差异。建议您升级到 <a href="http://www.microsoft.com/china/windows/internet-explorer/" target="_blank">Internet Explorer 8</a> 或以下浏览器：<a href="http://www.google.com/chrome/?hl=zh-CN">Chrome</a> / <a href="http://www.mozillaonline.com/">Firefox</a> /  <a href="http://www.apple.com.cn/safari/">Safari</a> / <a href="http://www.operachina.com/">Opera</a> ';
			oWarning.innerHTML=str;
			Warning(oWarning);
			
		}
	  })();
	  //意见反馈
	 (function(){
		 objshow('feedback','proposal','proposal_close1','transparent');
		  //意见反馈 提交回复部分
		 textproposal();
	 })();
	//登录注册部分
	(function(){
	 var oGo_logn_in1=getById('go_logn_in1');	
	 var oGo_logn_in2=getById('go_logn_in2');		
	 if(oGo_logn_in1 && oGo_logn_in2)
	 {
		 objshow('go_logn_in1','Sign_In','xxguan','transparent');	
		 objshow('go_logn_in2','Sign_In','xxguan','transparent');
	 }
	
	})();
	/*(function(){
		login_show();	  
	})();*/
	(function(){
		login_focus(); 
	})();
	(function(){
		var oTopics=getByClass(document,'topics1')[0]; 
		oTopics_a(oTopics);
	})();
	(function(){
		check_from();
	})();
	//------------------------------------------------------------------
	//回到顶部
	 (function(){
		 gottop();
	 })();
	  //搜索
	 //(function(){
		 //mysearch('myInput');
	  //})();
	 //客服
	 (function(){
	    service();
	 })();
	 /*(function(){
		collect_guanzhu();	   
	   })();*/
	 //头部左侧导航
	 (function(){
	    tit_ll();
	 })();
	 //三小图标的函数
	 (function(){
		var oGo_on=getByClass(document,'go_onmousebtn');
		for(var i=0; i<oGo_on.length; i++)
		{
			//iconall(oGo_on[i]);
		}
	 })();
}

