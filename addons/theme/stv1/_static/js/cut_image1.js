var CutImageClass = Class.create();
CutImageClass.prototype = {
	initialize: function(image_id , drag_div , preview_div) {
		this.image_id = image_id;
		this.drag_div = drag_div;
		this.preview_div = preview_div;
		this.isOpera = navigator.userAgent.indexOf("Opera") != -1; // 判断浏览器类型
		this.height = 100;
		this.width = 100;
	},

	initDrag: function() {
		new DragDrop(this.drag_div , this.drag_div);
	},

	begin_cut_image: function(){
		var tmpPosition = Position.positionedOffset( $(this.image_id) );
		var image_x = tmpPosition[0];
		var image_y = tmpPosition[1];
		$(this.drag_div).style.position = "absolute";
		$(this.drag_div).style.left = image_x + "px";
		$(this.drag_div).style.top = image_y + "px";

		this.initDrag();
		this.change_preview();
	},

	end_cut_image: function(){
		var img=document.getElementById('cut_img').src;
		var id=document.getElementById('id').value;
		var tmpPosition = this.get_cut_image_offset();
		var url = 'http://localhost/cut/admin_company_logocut.php?';
		var pars = 'x=' + tmpPosition[0] + '&y=' + tmpPosition[1]+'&img='+img+"&id="+id;
		var showResponse = function(originalRequest){
			$('msg').innerHTML = originalRequest.responseText;
		};
		var myAjax = new Ajax.Request(
						url,
						{method: 'post', parameters: pars, onComplete: showResponse}
						);
	},

	get_cut_image_offset: function(){
		var tmpPosition = Position.positionedOffset( $(this.image_id) );

		var image_x = tmpPosition[0];
		var image_y = tmpPosition[1];
		var div_x = parseInt( $(this.drag_div).style.left );
		var div_y = parseInt( $(this.drag_div).style.top );

		var x = div_x - image_x;
		var y = div_y - image_y;
		return [x , y];
	},

	change_preview: function(){
		var tmpPosition = this.get_cut_image_offset();
		var offset_x = tmpPosition[0];
		var offset_y = tmpPosition[1];

		var image_width = $(this.image_id).offsetWidth;
		var image_height = Element.getHeight(this.image_id);

		this.fix_bound(offset_x , offset_y , image_width , image_height);

		var preview_offset_x = image_width - offset_x;
		var preview_offset_y = image_height - offset_y;

		// change background position..
		$(this.preview_div).style.backgroundPosition = preview_offset_x + 'px ' + preview_offset_y + 'px';
		return [preview_offset_x , preview_offset_y];
	},

	fix_bound: function(offset_x , offset_y , image_width , image_height){
		if( offset_x < 0 ){
			$(this.drag_div).style.left = ( parseInt( $(this.drag_div).style.left ) - offset_x ) + 'px';
		}
		if( offset_y < 0 ){
			$(this.drag_div).style.top = ( parseInt( $(this.drag_div).style.top ) - offset_y ) + 'px';
		}
		if( offset_x > image_width - this.width ){
			$(this.drag_div).style.left = ( parseInt( $(this.drag_div).style.left ) - offset_x + image_width - this.width ) + 'px';
		}
		if( offset_y > image_height - this.height ){
			$(this.drag_div).style.top = ( parseInt( $(this.drag_div).style.top ) - offset_y + image_height - this.height ) + 'px';
		}
	}
}

function start_Drag(){
	this.isDragging = false;
	return false;
}

function when_Drag(clientX , clientY){
	if (!this.isDragging){
		this.isDragging = true;
	}
	CutImageUtil.change_preview();
}

function end_Drag(){
	this.isDragging = false;
	return true;
}