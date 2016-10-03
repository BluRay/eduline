// JavaScript Document
function  listdrge(id,titleid)
{
	var drge_obj=document.getElementById(id);
	var oTitleDrag=document.getElementById(titleid);
	oTitleDrag.onmousedown=function(ev)
	{
		var oEvent=ev||event;
		var disX=oEvent.clientX-drge_obj.offsetLeft;
		var disY=oEvent.clientY-drge_obj.offsetTop;
		function fnMove(ev)
		{
			var oEvent=ev || event;
			
			var l=oEvent.clientX-disX;
			var t=oEvent.clientY-disY;
			
			drge_obj.style.left=l+'px';
			drge_obj.style.top=t+'px';
		}
		function fnUp()
		{
			this.onmousemove=null;
			this.onmouseup=null;
			if(this.releaseCapture)
			{
				this.releaseCapture();
			}
		}
		if(drge_obj.setCapture)
		{
			
			drge_obj.onmousemove=fnMove;
			drge_obj.onmouseup=fnUp;
		}
		else
		{
			document.onmousemove=fnMove;
			document.onmouseup=fnUp;
		}
		if(drge_obj.setCapture)   //主要兼容IE8 7 6
		{   
			drge_obj.setCapture();    //而到了 IE8 7 6 return false 不好阻止选中的默认字体，（会出现潜在的大bug）， 所以用setcapture（），可是蛋疼的是setCapture不能捕获如document的虚标签，所以只能改回oDiv了 
		}
		return false;
	}
}