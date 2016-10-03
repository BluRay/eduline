
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
		 $(_this).find('span').removeClass('main_span');
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





