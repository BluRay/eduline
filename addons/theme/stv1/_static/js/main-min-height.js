//worap部分最低高度设置
$(function(){

           var headHeight = $('.header-worap').height();//头部的高度等于headHeight
           var footHeight = $('.footer-worap').height();//尾部的高度等于footHeight
           var miniHeight = (parseInt($(document).height()-headHeight-footHeight))//最低高度等于  整个页面的高度减去尾等于头部高减去尾部
           $('.worap').css({"min-height":(miniHeight-60)+'px'});//worap(盒子类名)的miniHeight-60高等于
//worap-con部分最低高度设置
			$('.worap-con').css({"min-height":(miniHeight-80)+'px'});//worap(盒子类名)的miniHeight-60高等于
});



//个人中心页面左边高度计算和赋值
$(document).ready(function(){
	var gao_l = $(".user-nav").height();
	var gao_m = $(".user-con").innerHeight();
	if(gao_l>gao_m){
		$(".user-con").height(gao_l);
	}else{
		$(".user-nav").height(gao_m);
	}

    });