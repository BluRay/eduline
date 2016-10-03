<?php
/** 
* 短信发送模型 
* @author   Ashang <ashangmanage@phpzsm.com>
* @date 2014-10-29 下午5:12:24 
* @php blog http://phpzsm.com 
*/ 
class SmsModel extends Model {
	protected $apis    = array(
			'send' => 'http://api.sms.cn/mt/'
	);
	
	protected $error   = null;
	
	
	protected $configs = array(
			'uid'    => null,
			'pwd'    => null,
			'time'   => null,
			//'mid'    => null,
	);
	public function _initialize(){
		$value=D('SystemData')->where(array('list'=>'admin_Config','key'=>'sms'))->getField('value');
		$value=unserialize($value);
		$this->configs['uid']=$value['sms_uid'];
		$this->configs['pwd']=$value['sms_pwd'];
	}
	
	
	public function send($mobiles, $content){
		$this->error = array();
		$post['uid'] = $this->configs['uid'];
		$post['pwd'] = $this->getPassword();
		if(!is_array($mobiles)) {
			$mobiles = explode(',', $mobiles);
		}
		$mobile = '';
		foreach($mobiles as $value){
			$mobile .= trim($value).',';
		}
		if(!$mobile) {
			$this->error = '没有需要发送的号码';
			return false;
		}
		$post['mobileids'] = rtrim(str_replace(',', NOW_TIME.',', $mobile), ',');
		$post['mobile']    = rtrim($mobile, ',');
		$post['content']   = $content;
		if(!empty($this->configs['time'])) {
			if(!is_numeric($this->configs['time'])){
				$post['time'] = strtotime($this->configs['time']);
			}
			$post['time'] = date('Y-m-d H:i', $this->configs['time']);
		}
		$post['encode'] = 'utf8';
		$post['mid']    = '';
		$result = $this->request($this->apis['send'], array('post'=>$post));
		parse_str($result, $results);
		if($results['stat'] == '100'){
			return true;
		}else{
			$this->error = $result;
			return false;
		}
	}
	
	
	public function set($spec, $value = null){
		if(is_array($spec)) {
			foreach($spec as $name => $value){
				$this->__set($name, $value);
			}
		}else{
			$this->__set($spec, $value);
		}
		return $this;
	}
	
	
	public function __get($name){
		if(isset($this->configs[$name])) {
			return $this->configs[$name];
		}else{
			return null;
		}
	}
	
	
	public function __set($name, $value){
		if(isset($this->configs[$name])) {
			$this->configs[$name] = $value;
		}
	}
	
	
	public function getError(){
		return $this->error;
	}
	
	protected function getPassword(){
		return md5($this->configs['pwd'].$this->configs['uid']);
	}
	
	protected function request($url, array $args = array()){
		$opts = array(
				CURLOPT_URL => $url,
				CURLOPT_HEADER => false,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_RETURNTRANSFER => true,
		);
		if(isset($args['post'])){
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = $args['post'];
		}
		if(isset($args['ua'])) $opts[CURLOPT_USERAGENT] = $args['ua'];
		if(isset($args['rf'])) $opts[CURLOPT_REFERER] = $args['rf'];
		if(isset($args['ck'])) $opts[CURLOPT_COOKIE]  = $args['ck'];
		$hs = isset($args['hs']) ? $args['hs'] : array();
		if(isset($args['ip'])){
			$hs[] = 'X-FORWARDED-FOR:'.$args['ip'];
			$hs[] = 'CLIENT-IP:'.$args['ip'];
		}
		if(!empty($hs)) $opts[CURLOPT_HTTPHEADER] = $hs;
		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$response = @curl_exec($ch);
		if($response && strtolower(json_encode($response)) == 'null'){
			$response = @mb_convert_encoding($response, 'utf-8', 'gbk');
		}
		return $response;
	}
	
	
}