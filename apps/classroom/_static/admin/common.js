// JavaScript Document

/**
 * 删除意见反馈
 * @param integer id 反馈ID
 * @return void
 */
admin.delSuggest = function(id){
	if('undefined' == typeof(id)||!id) id = admin.getChecked();
	if(!id){
        ui.error('请选择要删除的反馈');
		return false;
    }
	if(confirm('确定要删除此反馈吗？')){
        $.post(U('classroom/AdminSuggest/del'),{id:id},function(msg){
            admin.ajaxReload(msg);
        },'json');
    }
}
/**
 * 删除讲师
 * @param _id
 * @param action
 * @returns {boolean}
 */
admin.delTeacher=function(_id,action){

    var id = ("undefined"== typeof(_id)|| _id=='') ? admin.getChecked() : _id;
    if(id==''){
        ui.error("讲师id不能为null");
        return false;
    }
    if(!confirm("是否确认删除此讲师？")){
        return false;
    }
    $.post(U('classroom/AdminTeacher/'+action),{ids:id},function(msg){
        admin.ajaxReload(msg);
    },'json');
}

admin.delTeacherAll=function(action){

    var ids=admin.getChecked();
    ids = ("undefined"== typeof(ids)|| ids=='') ? admin.getChecked() : ids;
    if(ids==''){
        ui.error("讲师id不能为null");
        return false;
    }
    if(!confirm("是否确认？")){
        return false;
    }
    $.post(U('classroom/AdminTeacher/'+action),{ids:ids},function(msg){
        admin.ajaxReload(msg);
    },'json');
}
/**
 * 删除提现记录
 * @param integer id 提现记录ID
 * @return void
 */
admin.delWithdraw = function(id){
	if('undefined' == typeof(id)||!id) id = admin.getChecked();
	if(!id){
        ui.error('请选择要删除的记录');
		return false;
    }
	if(confirm('确定要删除此记录吗？')){
        $.post(U('classroom/AdminWithdraw/del'),{id:id},function(msg){
            admin.ajaxReload(msg);
        },'json');
    }
}

admin.zyPageBack = function(){
	window.history.back();
	return false;
}