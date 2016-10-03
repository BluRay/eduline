/*
 *  课程分类
 */
(function($){
	$.fn.mzcategory = function(settings){
		if(this.length<1){return;};
		// 默认值
		var defaults ={
			url:'',
			hiddencid:'cid',//分类ID表单名称
			navData:'',//导航数据内容
			urlfirstdata:U('widget/VideoLevel/urlfirstdata'),
			type:1// 1:课程分类，2:点播分类
		};
		settings=$.extend({},defaults,settings);
		var obj  = this;
		var fun  = {};
		//设置面板的名称ID
		settings.panelAdmin  = 'Tree_Menu'+$(obj).attr('id');
		settings.transparent = settings.panelAdmin+'transparent';
		settings.closename   = settings.panelAdmin+'close';
		settings.okname      = settings.panelAdmin+'ok';
		
		fun.createHtml = function(){
			if(!$('#'+settings.panelAdmin)[0]){
				var $obj = {'class':'main_box_index shadow Tree_Menu','id':settings.panelAdmin};
				var panelAdmin  = $('<div></div>',$obj);
				$('body').append(panelAdmin);
			}
			if(!$('#'+settings.transparent)[0]){
				var transparent = $('<div></div>',{'class':'transparent','id':settings.transparent,'style':'display: none; z-index: 800;'});	
				$('body').append(transparent);
			}
			if(!$('#'+settings.closename)[0]){
				var html  = '<div id="'+settings.closename+'" class="close fr mr15 mt5">×</div>';
					html += '<h3 class="bor_b txt_l h40">选择</h3>';
					html += '<a class="ml86 Return sel_w" href="javascript:;">上一级</a>';
					html += '<div class="selected_con_a m5_38 w370 clearfix" style="background:none;padding-left: 10px;">';
					html += '<ul class="mzulcategory" style="height:auto;overflow;hidden;">';
					//html += '<li><a href="javascript:;">北京</a><em class="vl5"></em></li>';
					//html += '<li><a href="javascript:;">北京第二外国语学院1</a><em class="vl5"></em></li>';
					html += '</ul>';
					html += '</div>';
					html += '<div class="proposal_box_b">';
					html += '<div class="proposal_box_a clearfix" style="height:240px;">';
					//第一次
					//html += fun.createContent(settings.content);
					//html += '<a data-id="0" title="上海">上海</a>';
					html += '</div>';
					html += '<div style="text-align:center;height:auto;overflow;hidden;"><a id="'+settings.okname+'" style="margin-left:270px;" class="sel_w" href="javascript:;">确定</a></div>';
					html += '</div>';
				$('#'+settings.panelAdmin).html(html);
			}
		};
		//创建导航
		fun.createNav = function(){
			var html = '';
		 	$.each(settings.navData,function(i,k){
				html += '<li><a href="javascript:;" data-id="'+k.id+'" data-pid="'+k.pid+'">'+k.title+'</a><em class="vl5"></em></li>';
			});
			$('#'+settings.panelAdmin).find('ul.mzulcategory').html(html);
		}
		//创建导航
		fun.createNav1 = function(){
			if(settings.navData.length <= 0){
				return false;
			}
			var ids  = []; 
			var html = '<ul>';
			var txt  = '';
		 	$.each(settings.navData,function(i,k){
				ids.push(k.id);
				txt  += '-'+k.title;
				html += '<li><a href="javascript:;"><span>'+k.title+'</span></a><em class="vl5"></em></li>';
			});
			
			var input = '<input name="'+settings.hiddencid
			    +'" type="hidden" value="'+ids.join(',')+'"/>';
			
			if($(obj).is('input:text')){
				$(obj).val(txt.substring(1));
				$(obj).parent().next('div.selected_con').html(input);
			}else{
				html += '</ul>'+input;
				$(obj).parent().next('div.selected_con').html(html);
			}
			
			return true;
		}
		
		//创建分类内容
		fun.createContent = function($data){
			if(!$data){
				$('#'+settings.panelAdmin).find('div.proposal_box_a').html('没有内容了!');
				return '';	
			}
			var html = '';
			$.each($data,function(i,k){
				var cat_id = k.zy_video_category_id?k.zy_video_category_id:k.zy_school_category_id;
				html += '<a data-id="'+cat_id+'" data-pid="'+k.pid+'" title="'+k.title+'">'+k.title+'</a>';
			});
			$('#'+settings.panelAdmin).find('div.proposal_box_a').html(html?html:'没有内容了');
		};
		
		//远程获取数据
		fun.getData = function($pid){
			$.ajax({
				url : settings.url,
				type : 'POST',
				data : {pid:$pid,type:settings.type},
				async: false,
				timeout: 5000,
				dataType : 'json',
				success:function(_data){
					settings.content = _data?_data:[];
				},
				error : function(xhr, type) {
					alert(xhr.responseText);
				}
			});
		};
		//设置导航条数据
		fun.setNavData = function(data){
			var isok = true;
			$.each(settings.navData,function(i,k){
				if(k.pid == data.pid){
					isok = false;
					settings.navData[i] = data;
					return false;
				}
			});
			if(isok){
				settings.navData.push(data);	
			}
		}
		//设置弹出框的位置
		fun.setObjLeftOrTop = function(){
			var scrollTop=document.body.scrollTop || document.documentElement.scrollTop;
			var scrollLeft=document.body.scrollLeft || document.documentElement.scrollLeft;
			
			var top  = (document.documentElement.clientHeight - $('#'+settings.panelAdmin)[0].offsetHeight)/2+scrollTop;
			var left = (document.documentElement.clientWidth  - $('#'+settings.panelAdmin)[0].offsetWidth)/2+scrollLeft;
			
			$('#'+settings.panelAdmin).css({left:left+'px',top:top+'px'});	
		}
		
		//内容小项点击事件
		$('#'+settings.panelAdmin).find('div.proposal_box_a a').live('click',function(){
			var title = $(this).html();
			var pid   = $(this).attr('data-pid');
			var id    = $(this).attr('data-id');
			//或者导航条数据
			fun.setNavData({title:title,pid:pid,id:id});
			//创建导航条
			fun.createNav();
			//取下级的数据
			fun.getData(id);
			//创建内容
			fun.createContent(settings.content);
		});
		$('#'+settings.panelAdmin).find('a.Return').live('click',function(){
			if(settings.navData.length == 0){
				return;
			}
			//丢掉最后一个元素
			var data = settings.navData.pop();
			//创建导航条
			fun.createNav();
			//取下级的数据
			fun.getData(data.pid);
			//创建内容
			fun.createContent(settings.content);
		});
		//导航条单元点击
		$('#'+settings.panelAdmin).find('ul.mzulcategory a').live('click',function(){
			var id = $(this).attr('data-id');
			var pos = 0;
			//找到点击的位置
			$.each(settings.navData,function(i,k){
				if(id == k.id){
					pos = i;
				}
			});
			//删除后面的数据
			settings.navData.splice(pos+1,settings.navData.length);
			//创建导航条
			fun.createNav();
			//取下级的数据
			fun.getData(settings.navData[pos].id);
			//创建内容
			fun.createContent(settings.content);
			
		});
		//关闭按钮
		$('#'+settings.closename).live('click',function(){
			$('#'+settings.panelAdmin).hide();	
			$('#'+settings.transparent).hide();
		});
		//确定按钮
		$('#'+settings.okname).live('click',function(){
			if(!fun.createNav1()){
				alert('没有选择分类!');
				return;
			}
			$('#'+settings.panelAdmin).hide();	
			$('#'+settings.transparent).hide();
		});
		
		$(obj).click(function(){
			$('#'+settings.panelAdmin).show();	
			$('#'+settings.transparent).show();
			
			fun.setObjLeftOrTop();
		});
		//创建html
		fun.createHtml();
		if(settings.navData){
			$.ajax({
				url : settings.urlfirstdata,
				type : 'POST',
				data : {ids:settings.navData,type:settings.type},
				async: false,
				timeout: 5000,
				dataType : 'json',
				success:function(_data){
					settings.navData = [];
					if(_data){
						$.each(_data,function(i,k){
							var cat_id = k.zy_video_category_id?k.zy_video_category_id:k.zy_school_category_id;
							fun.setNavData({title:k.title,pid:k.pid,id:cat_id});
						});
						//创建导航条
						fun.createNav();
						//取下级的数据
						fun.getData(settings.navData[settings.navData.length-1].id);
						//创建内容
						fun.createContent(settings.content);
						fun.createNav1();
					}else{
						//首次取数据
						fun.getData(0);
						//创建html
						fun.createHtml();
						//第一次创建内容
						fun.createContent(settings.content);
					}
				},
				error : function(xhr, type) {
					//alert(xhr.responseText);
				}
			});
		}else{
			settings.navData = [];
			//首次取数据
			fun.getData(0);
			//创建html
			fun.createHtml();
			//第一次创建内容
			fun.createContent(settings.content);
		}
		
		//绑定事件
		$(window).resize(function() {
			fun.setObjLeftOrTop();
		});
		$(window).scroll(function() {
			fun.setObjLeftOrTop();
		});
	};
	
})(jQuery);
