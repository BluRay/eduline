// JavaScript Document
// 检查文章表单提交
admin.checklzarticleInfo = function(form) {
	if(form.title.value.replace(/^ +| +$/g,'')==''){
		ui.error('文章标题不能为空!');
		return false;
	}
	if(form.description.value.replace(/^ +| +$/g,'')==''){
		ui.error('文章描述不能为空!');
		return false;
	}
	if(form.source.value.replace(/^ +| +$/g,'')==''){
		ui.error('文章来源不能为空!');
		return false;
	}
	return true;
};
// 检查连载内容表单提交
var checklzindexInfo = function(form) {
	if(form.find('select[name="title"]:selected').val() <= 0){
		ui.error('连载分期不能为空!');
		return false;
	}
	if(form.find('input[name="title"]').val().replace(/^ +| +$/g,'')==''){
		ui.error('连载标题不能为空!');
		return false;
	}
	if(form.find('textarea[name="description"]').val().replace(/^ +| +$/g,'')==''){
		ui.error('连载描述不能为空!');
		return false;
	}
	if(form.find('input[name="source"]').val().replace(/^ +| +$/g,'')==''){
		ui.error('连载来源不能为空!');
		return false;
	}
	return true;
};
// 检查分期表单提交
admin.checkxdateInfo = function(form,$type) {
	if($type == 'add' && form.cid.value <= 0){
		ui.error('连载栏目不能为空!');
		return false;
	}
	if(form.name.value.replace(/^ +| +$/g,'')==''){
		ui.error('分期名称不能为空!');
		return false;
	}
	return true;
};
// 检查专题分类表单提交
admin.checkCategoryInfo= function(form) {
	if(form.title.value.replace(/^ +| +$/g,'')==''){
		ui.error('专题分类标题不能为空!');
		return false;
	}
	if(form.templet.value.replace(/^ +| +$/g,'')==''){
		ui.error('模板名称不能为空!');
		return false;
	}else{
		//只能输入英文
		var reg = /^[a-zA-Z0-9]{1,}$/;     
        var r = form.templet.value.match(reg);     
        if(r==null){  
            ui.error('模板名称可以是英文或者数字!');
			return false;
        }   
	}
	return true;
};
//检查讲师表单提交
admin.checkTeacher=function(form) {
	if(form.name.value.replace(/^ +| +$/g,'')==''){
		ui.error('讲师姓名不能为空!');
		return false;
	}
	if(form.inro.value.replace(/^ +| +$/g,'')==''){
		ui.error('讲师简介不能为空!');
		return false;
	}
	if(form.title.value.replace(/^ +| +$/g,'')==''){
		ui.error('讲师职称不能为空!');
		return false;
	}
	if(form.head_id.value.replace(/^ +| +$/g,'')==''){
		ui.error('讲师照片!');
		return false;
	}
	return true;
};

// 检查专题表单提交
admin.checkSpecialInfo = function(form) {
	if(form.sc_id.value <= 0){
		ui.error('专题分类不能为空!');
		return false;
	}
	if(form.title.value.replace(/^ +| +$/g,'')==''){
		ui.error('专题名称不能为空!');
		return false;
	}
	if(form.foldername.value.replace(/^ +| +$/g,'')==''){
		ui.error('文件夹不能为空!');
		return false;
	}else{
		//只能输入英文
		var reg = /^[a-zA-Z0-9]{1,}$/;     
        var r = form.foldername.value.match(reg);     
        if(r==null){  
            ui.error('文件夹名可以是英文或者数字!');
			return false;
        }   
	}
	return true;
};



//处理银行卡
admin.BankCardEdit = function(_id,action,title,type){
	var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error(L('PUBLIC_SELECT_TITLE_TYPE',{'title':title,'type':type}));
        return false;
   }
   if(confirm(L('PUBLIC_CONFIRM_DO',{'title':title,'type':type}))){
	   $.post(U('classroom/AdminCard/'+action),{id:id},function(msg){
			admin.ajaxReload(msg);
  	 },'json');
   }	
};

//处理笔记
admin.mzNoteEdit = function(_id,action,title,type){
	var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error(L('PUBLIC_SELECT_TITLE_TYPE',{'title':title,'type':type}));
        return false;
   }
   if(confirm(L('PUBLIC_CONFIRM_DO',{'title':title,'type':type}))){
	   $.post(U('classroom/AdminNote/'+action),{id:id},function(msg){
			admin.ajaxReload(msg);
  	 },'json');
   }	
};


//处理提问
admin.mzQuestionEdit = function(_id,action,title,type){
	var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error(L('PUBLIC_SELECT_TITLE_TYPE',{'title':title,'type':type}));
        return false;
   }
   if(confirm(L('PUBLIC_CONFIRM_DO',{'title':title,'type':type}))){
	   $.post(U('classroom/AdminQuestion/'+action),{id:id},function(msg){
			admin.ajaxReload(msg);
  	 },'json');
   }	
};
//处理点评
admin.mzReviewEdit = function(_id,action,title,type){
	var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error(L('PUBLIC_SELECT_TITLE_TYPE',{'title':title,'type':type}));
        return false;
   }
   if(confirm(L('PUBLIC_CONFIRM_DO',{'title':title,'type':type}))){
	   $.post(U('classroom/AdminReview/'+action),{id:id},function(msg){
			admin.ajaxReload(msg);
  	 },'json');
   }	
};

//处理专题
admin.mzSpecialEdit = function(_id,action,title,type){
	var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error(L('PUBLIC_SELECT_TITLE_TYPE',{'title':title,'type':type}));
        return false;
   }
   if(confirm(L('PUBLIC_CONFIRM_DO',{'title':title,'type':type}))){
	   $.post(U('classroom/AdminSpecial/'+action),{id:id},function(msg){
			admin.ajaxReload(msg);
  	 },'json');
   }	
};
//专题添加封面
admin.mzSpecialAddCover = function(_id){
	if(!_id){
		ui.error('专题信息不正确!');
	}
	ui.box.load(U('classroom/AdminSpecial/addcover')+'&sid='+_id+'&a='+Math.random(), "添加专题封面");
}




//处理连载分期
admin.mzXdateEdit = function(_id,action,title,type){
	var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error(L('PUBLIC_SELECT_TITLE_TYPE',{'title':title,'type':type}));
        return false;
   }
   if(confirm(L('PUBLIC_CONFIRM_DO',{'title':title,'type':type}))){
	   $.post(U('classroom/AdminLianZai/'+action),{id:id},function(msg){
			admin.ajaxReload(msg);
  	 },'json');
   }	
};
//处理连载内容
admin.mzLzContentEdit = function(_id,action,title,type){
	var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error(L('PUBLIC_SELECT_TITLE_TYPE',{'title':title,'type':type}));
        return false;
   }
   if(confirm(L('PUBLIC_CONFIRM_DO',{'title':title,'type':type}))){
	   $.post(U('classroom/AdminLianZai/'+action),{id:id},function(msg){
			admin.ajaxReload(msg);
  	 },'json');
   }	
};

//处理连载内容----选择内容类型的点击事件
admin.mzchangeContent = function(_this){
	var val = $(_this).val();
	//内容类型【1:图文类型;2:文章类型;3:视频类型】
	if(val == 1){
		$('#txtmzimage').show();
		$('#txtmzvideo').hide();
	}else if(val == 3){
		$('#txtmzimage').hide();
		$('#txtmzvideo').show();
	}
};


addcontentcheckForm = function(_this){
	if(_this.find('input[name="title"]').val().replace(/^ +| +$/g,'')==''){
		ui.error('连载标题不能为空!');
		return false;
	}
	if(_this.find('textarea[name="description"]').val().replace(/^ +| +$/g,'')==''){
		ui.error('连载描述不能为空!');
		return false;
	}
	if(_this.find('input[name="source"]').val().replace(/^ +| +$/g,'')==''){
		ui.error('连载来源不能为空!');
		return false;
	}
	return true;
}
addcontentpost_callback = function(_this,data){
	if(data.status != undefined){
		if(data.status == '0'){
			ui.error(data.info);
		} else {
			ui.success(data.info);
			setTimeout(function(){
				window.location.href = data.data.jumpUrl;	
			},1255);
		}
	}
}
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
				//mzgaojiaowang.ajaxDone(xMLHttpRequest, textStatus, errorThrown);
				if(typeof callback != 'undefined' && callback instanceof Function){   
					callback($form,xMLHttpRequest);
				}  
			},
			error: function(xhr, ajaxOptions, thrownError){
				ui.error("未知错误!");
				//mzgaojiaowang.ajaxError(xhr, ajaxOptions, thrownError);
			}
		});
	}
	_submitFn();
	return false;
}
admin.addSubjectCategory = function(){
    ui.box.load(U('classroom/AdminVideoCategory/addSubjectCategory'), "添加科目分类");
}
admin.editSubjectCategory = function(subject_id){
    ui.box.load(U('classroom/AdminVideoCategory/editSubjectCategory')+'&subject_id='+subject_id, "编辑科目分类");
}
admin.delSubject = function(subject_id){
   if(confirm("你确定要删除此科目分类？")){
	   $.post(U('classroom/AdminVideoCategory/delSubjectCategory'),{subject_id:subject_id},function(msg){
			if(msg.status==0){
	        	ui.error(msg.data);
	        }else{
	        	ui.success(msg.data);
	        	window.location.href = window.location.href;
	        }
  	 	},'json');
   }
}