// JavaScript Document
function higher_common()
{
	  //���!
	 (function(){
		 objshow('feedback','proposal','proposal_close1','transparent');
		  //���! �ύ�ظ�����
		 textproposal();
	 })();
	//��½ע�Ჿ��
	(function(){
		login_up();
	})();
	(function(){
		login_show();	  
	})();
	(function(){
		login_focus(); 
	})();
	(function(){
		danxuan_bar();
	})();
	(function(){
		check_from();
	})();
	//------------------------------------------------------------------
	//�ص�����
	 (function(){
		 gottop();
	 })();
	  //����
	 (function(){
		 mysearch('myInput');	   
	  })();
	
	 //��Сͼ��ĺ���
	 (function(){
		var oGo_on=getByClass(document,'go_onmousebtn');
		for(var i=0; i<oGo_on.length; i++)
		{
			iconall(oGo_on[i]);
		}
	 })();
	
}