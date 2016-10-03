// 加入群组
function joingroup(gid) {
    if(MID=='0'){
	  reg_login()
	  return ;
    }

    ui.box.load(U('group/Group/joinGroup')+'&gid='+gid,'加入群组');
}
function addPost(gid){
    if(MID=='0'){
        reg_login()
        return ;
    }
    window.location = U('group/Topic/add')+'&gid='+gid;
}
// 删除群组
function delgroup(gid) {
	if(MID=='0'){
	  reg_login()
	  return ;
    }
    ui.box.load(U('group/Group/delGroupDialog')+'&gid='+gid,'解散群组');
}
// 退出群组
function quitgroup(gid) {
	if(MID=='0'){
	  reg_login()
	  return ;
    }
    ui.box.load(U('group/Group/quitGroupDialog')+'&gid='+gid,'退出群组');
}
//解散小组
function addsubmit(gid) {
	var verify = $('#verify').val();
	if (verify == "" || verify == undefined) {
		ui.error("请输入验证码");
		return ;
	}
	
	if (confirm('解散后无法恢复！确认解散？')) {
		var verify = $('input[name="verify"]').val()
		$.ajax({
 			type: "POST",
 			url: U('group/Group/delGroup'),
 			data:   "gid="+gid+"&verify=" + verify,
 			success: function(msg){
 				if (msg == '1') {
	 				$('#pop1').hide();
	 				$('#pop2').show();
 				} else {
					ui.error(msg);
 				}
 			} 
		});
	}
}
// 过滤html，字串检测长度
function checkPostContent(content)
{
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/<br>/g, "");
	content = content.replace(/<p>/g, "");
	content = content.replace(/<\/p>/g, "");
	return getLength(content);
}