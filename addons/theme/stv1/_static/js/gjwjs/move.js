function css(obj, name, value)
{
	if(arguments.length==2)
	{
		return parseFloat(obj.currentStyle?obj.currentStyle[name]:document.defaultView.getComputedStyle(obj, false)[name]);
	}
	else if(arguments.length==3)
	{
		switch(name)
		{
			case 'width':
			case 'height':
			case 'paddingLeft':
			case 'paddingTop':
			case 'paddingRight':
			case 'paddingBottom':
				value=Math.max(value,0);
			case 'left':
			case 'right':
			case 'top':
			case 'bottom':
			case 'marginLeft':
			case 'marginTop':
			case 'marginRight':
			case 'marginBottom':
			case 'fontSize':
				obj.style[name]=value+'px';
				break;
			case 'opacity':
				obj.style.filter="alpha(opacity:"+value*100+")";
				obj.style.opacity=value;
				break;
			default:
				obj.style[name]=value;
		}
	}
	
	return function (name_in, value_in){css(obj, name_in, value_in)};
}

function fnCss(obj, name, value)
{
	if(arguments.length==2)
	{
		return name=='opacity'? parseInt(css(obj, 'opacity')*100) : parseInt(css(obj, name));
	}
	else if(arguments.length==3)
	{
		if (name=='opacity')
		{
			obj.style.opacity=value/100;
			obj.style.fliter='alpha(opacity:'+value+')';
		}
		else
		{
			obj.style[name]=value+'px';
		}
	}
}

function move (obj, json, iType, iTime, fnEnd, fnDuring)
{
	//var fnMove=null;
	if (obj.timer)clearInterval(obj.timer);
	
	switch(iType)
	{
		case 'buffer':
			obj.timer=setInterval(function (){
				bufferMove(obj, json, iTime, fnEnd, fnDuring);
			}, 30);
			
			break;
		case 'flex':
			obj.timer=setInterval(function (){
				flexMove(obj, json, iTime, fnEnd, fnDuring);
			}, 30);
			
			break;
		case 'time':
			var n={};
			var start={};
			var dis={};
			var count=parseInt(iTime/30);
			
			for (var name in json)
			{
				start[name]=fnCss(obj, name);
				dis[name]=json[name]-start[name];
				n[name]=0;
			}
			
			obj.timer=setInterval(function (){
				timeMove(obj, json, iTime, fnEnd, fnDuring, n, start, dis, count);
			}, 30);
			break;
	}
	
	
	function bufferMove(obj, json, iTime, fnEnd, fnDuring)
	{
		var bStop=true;
		var name='';
		var speed=0;
		var cur=0;
		if (!iTime)iTime=8;
		
		for(name in json)
		{
			cur=fnCss(obj, name);
			if(json[name]!=cur)
			{
				bStop=false;
				
				speed=(json[name]-cur)/iTime;
				speed=speed>0?Math.ceil(speed):Math.floor(speed);
				
				fnCss(obj, name, cur+speed);
			}
		}
		
		if(fnDuring)fnDuring.call(obj);
		
		if(bStop)
		{
			clearInterval(obj.timer);
			obj.timer=null;
			fnEnd && fnEnd.call(obj);
		}
	}
	
	function flexMove(obj, json, iTime, fnEnd, fnDuring)
	{
		var bStop=true;
		var name='';
		var speed=0;
		var cur=0;
		
		for(name in json)
		{
			if(!obj.oSpeed)obj.oSpeed={};
			if(!obj.oSpeed[name])obj.oSpeed[name]=0;
			cur=fnCss(obj, name);
			if(Math.abs(json[name]-cur)>1 || Math.abs(obj.oSpeed[name])>1)
			{
				bStop=false;
				
				obj.oSpeed[name]+=(json[name]-cur)/5;
				obj.oSpeed[name]*=0.7;
				var maxSpeed=iTime;
				if(iTime && Math.abs(obj.oSpeed[name])>maxSpeed)
				{
					obj.oSpeed[name]=obj.oSpeed[name]>0?maxSpeed:-maxSpeed;
				}
				
				fnCss(obj, name, cur+obj.oSpeed[name]);
			}
		}
		
		if(fnDuring)fnDuring.call(obj);
		
		if(bStop)
		{
			clearInterval(obj.timer);
			
			for (var name in json)
			{
				fnCss(obj, name, json[name]);
			}
			obj.timer=null;
			fnEnd && fnEnd(obj);
		}
	}
	
	function timeMove(obj, json, iTime, fnEnd, fnDuring, n, start, dis, count)
	{
		var bStop=false;
		var cur=0;
		
		for (var name in json)
		{
			n[name]++;
			
			cur=start[name]+n[name]*dis[name]/count;
			fnCss(obj, name, cur);
			
			if (n[name]==count)
			{
				bStop=true;
			}
		}
		
		if(fnDuring)fnDuring.call(obj);
		
		if(bStop)
		{
			clearInterval(obj.timer);
			obj.timer=null;
			fnEnd && fnEnd(obj);
		}
	}
}
