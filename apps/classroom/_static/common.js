
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






