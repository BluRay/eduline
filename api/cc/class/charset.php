<?php
define('CODETABLE_DIR', dirname(__FILE__).'/charsets/');
/**
 * 字符编码转换 PHP4
 * @example
 * // instance for converting utf8 to gbk
 * $chs1 = Charset::instance('utf8', 'gbk');
 * 
 * // instance for converting gbk to utf8
 * $chs2 = Charset::instance('gbk', 'utf8');
 * 
 * // convert text
 * $string = $chs->convert($src_string);
 * 
 * // convert array(recursive)
 * $string_array = $chs->convert($src_string_array);
 * 
 */
class charset {
	
	/**
	 * 字符转换所用的表
	 *
	 * @var string
	 */
	var $table = '';
	/**
	 * iconv扩展库是否启用
	 *
	 * @var bool
	 */
	var $iconv_enabled = false;
	/**
	 * Multibyte String扩展库是否启用
	 *
	 * @var bool
	 */
	var $mb_enabled = false;
	/**
	 * unicode字符的表信息
	 *
	 * @var array
	 */
	var $unicode_table = array();
	/**
	 * 配置信息
	 *
	 * @var array
	 */
	var $config  =  array
		(
		'SourceLang'            => '',
		'TargetLang'            => '',
		'GBtoUnicode_table'     => 'gb-unicode.table',
		'BIG5toUnicode_table'   => 'big5-unicode.table',
		);

	/**
	 * 对字符转换进行初始设定
	 *
	 * @param string $SourceLang 源编码
	 * @param string $TargetLang 目标编码
	 * @param bool $ForceTable 是否强制使用表来进行转换
	 * @return Chinese
	 */
	function __construct($SourceLang, $TargetLang, $ForceTable = FALSE) {

		$this->config['SourceLang'] = self::_lang($SourceLang);
		$this->config['TargetLang'] = self::_lang($TargetLang);
		
		if($this->config['TargetLang'] != 'BIG5' && !$ForceTable) {
			if(function_exists('iconv')) {
				$this->iconv_enabled = true;
			} elseif(function_exists('mb_convert_encoding')) {
				$this->mb_enabled = true;
			}			
		} 
		
		if(!$this->iconv_enabled && !$this->mb_enabled) {
			$this->OpenTable();
		}
	}
	
	/**
	 * 
	 * @param string $SourceLang
	 * @param string $TargetLang
	 * @param string $ForceTable
	 * @return Charset
	 */
	function instance($SourceLang, $TargetLang, $ForceTable = FALSE) {
		static $obj = array();
		
		$SourceLang = self::_lang($SourceLang);
		$TargetLang = self::_lang($TargetLang);
		
		$LangKey = $SourceLang.$TargetLang;
		if (!isset($obj[$LangKey]) || $obj[$LangKey] === NULL) {
			$obj[$LangKey] = new Charset($SourceLang, $TargetLang, $ForceTable);
		}
		
		return $obj[$LangKey];
	}

	/**
	 * 转换字符集编码名称格式
	 *
	 * @param string $LangCode 字符集编码名称
	 * @return string 转换后的字符集编码名称
	 */
	function _lang($LangCode) {
		$LangCode = strtoupper($LangCode);

		if(substr($LangCode, 0, 2) == 'GB') {
			return 'GBK';
		} elseif(substr($LangCode, 0, 3) == 'BIG') {
			return 'BIG5';
		} elseif(substr($LangCode, 0, 3) == 'UTF') {
			return 'UTF-8';
		} elseif(substr($LangCode, 0, 3) == 'UNI') {
			return 'UNICODE';
		}
	}

	
	function _hex2bin($hexdata) {
		$bindata = '';
		for($i=0; $i < strlen($hexdata); $i += 2) {
			$bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}
		return $bindata;
	}

	/**
	 * 读取字符集编码转换表
	 *
	 */
	function OpenTable() {
		$this->unicode_table = array();
		if($this->config['SourceLang'] == 'GBK' || $this->config['TargetLang'] == 'GBK') {
			$this->table = CODETABLE_DIR.$this->config['GBtoUnicode_table'];
		} elseif($this->config['SourceLang'] == 'BIG5' || $this->config['TargetLang'] == 'BIG5') {
			$this->table = CODETABLE_DIR.$this->config['BIG5toUnicode_table'];
		}
		$fp = fopen($this->table, 'rb');
		$tabletmp = fread($fp, filesize($this->table));
		$key = $value = '';
		for($i = 0; $i < strlen($tabletmp); $i += 10) {
			list($key, $value) = explode(',', str_replace("\n", '', substr($tabletmp, $i, 10)));
			if($this->config['TargetLang'] == 'UTF-8') {
				$this->unicode_table[hexdec('0x'.$key)] = '0x'.$value;
			} elseif($this->config['SourceLang'] == 'UTF-8') {
				$this->unicode_table[hexdec('0x'.$value)] = '0x'.$key;
			} elseif($this->config['TargetLang'] == 'UNICODE') {
				$this->unicode_table[hexdec('0x'.$key)] = $value;
			}
		}
	}

	/**
	 * 中文字符集编码转换成UTF8编码
	 *
	 * @param string $c 要进行转换的字符
	 * @return string 转换后的字符
	 */
	function CHSUtoUTF8($c) {
		$str = '';
		if($c < 0x80) {
			$str .= $c;
		} elseif($c < 0x800) {
			$str .= (0xC0 | $c >> 6);
			$str .= (0x80 | $c & 0x3F);
		} elseif($c < 0x10000) {
			$str .= (0xE0 | $c >> 12);
			$str .= (0x80 | $c >> 6 & 0x3F);
			$str .=( 0x80 | $c & 0x3F);
		} elseif($c < 0x200000) {
			$str .= (0xF0 | $c >> 18);
			$str .= (0x80 | $c >> 12 & 0x3F);
			$str .= (0x80 | $c >> 6 & 0x3F);
			$str .= (0x80 | $c & 0x3F);
		}
		return $str;
	}
	
	/**
	 * 对源内容进行编码转换
	 *
	 * @param mixed $SourceText 要进行转换的文本（可以是数组）
	 * @return mixed 转换字符集编码后的文本
	 */
	function Convert($SourceText) {
		if (is_array($SourceText)) {
			foreach($SourceText as $key => $text) {
				$SourceText[$key] = $this->Convert($text);
			}
		} else {
			$SourceText = @$this->Convert_String($SourceText);
		}
		return $SourceText;
	}
	

	/**
	 * 对文本进行字符集编码转换
	 *
	 * @param string $SourceText 要进行转换的文本
	 * @return string 转换字符集编码后的文本
	 */
	function Convert_String($SourceText) {
		//$this->iconv_enabled = false;
		if($this->config['SourceLang'] == $this->config['TargetLang']) {
			return $SourceText;
		} elseif($this->iconv_enabled || $this->mb_enabled) {
			if($this->config['TargetLang'] <> 'UNICODE') {
				if($this->iconv_enabled) {
					return iconv($this->config['SourceLang'], $this->config['TargetLang'], $SourceText);
				}
				if($this->mb_enabled) {
					return mb_convert_encoding($SourceText, $this->config['TargetLang'], $this->config['SourceLang']);
				}
			} else {
				$return = '';
				while($SourceText != '') {
					if(ord(substr($SourceText, 0, 1)) > 127) {
						$return .= "&#x".dechex($this->Utf8_Unicode(iconv($this->config['SourceLang'],"UTF-8", substr($SourceText, 0, 2)))).";";
						$SourceText = substr($SourceText, 2, strlen($SourceText));
					} else {
						$return .= substr($SourceText, 0, 1);
						$SourceText = substr($SourceText, 1, strlen($SourceText));
					}
				}
				return $return;
			}

		} elseif($this->config['TargetLang'] == 'UNICODE') {
			$utf = '';
			while($SourceText != '') {
				if(ord(substr($SourceText, 0, 1)) > 127) {
					if($this->config['SourceLang'] == 'GBK') {
						$utf .= '&#x'.$this->unicode_table[hexdec(bin2hex(substr($SourceText, 0, 2))) - 0x8080].';';
					} elseif($this->config['SourceLang'] == 'BIG5') {
						$utf .= '&#x'.$this->unicode_table[hexdec(bin2hex(substr($SourceText, 0, 2)))].';';
					}
					$SourceText = substr($SourceText, 2, strlen($SourceText));
				} else {
					$utf .= substr($SourceText, 0, 1);
					$SourceText = substr($SourceText, 1, strlen($SourceText));
				}
			}
			return $utf;
		} else {
			$ret = '';
			if($this->config['SourceLang'] == 'UTF-8') {
				$out = '';
				$len = strlen($SourceText);
				$i = 0;
				while($i < $len) {
					$c = ord(substr($SourceText, $i++, 1));
					switch($c >> 4) {
						case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
							$out .= substr($SourceText, $i - 1, 1);
							break;
						case 12: case 13:
							$char2 = ord(substr($SourceText, $i++, 1));
							$char3 = $this->unicode_table[(($c & 0x1F) << 6) | ($char2 & 0x3F)];
							if($this->config['TargetLang'] == 'GBK') {
								$out .= $this->_hex2bin(dechex($char3 + 0x8080));
							} elseif($this->config['TargetLang'] == 'BIG5') {
								$out .= $this->_hex2bin($char3);
							}
							break;
						case 14:
							$char2 = ord(substr($SourceText, $i++, 1));
							$char3 = ord(substr($SourceText, $i++, 1));
							$char4 = $this->unicode_table[(($c & 0x0F) << 12) | (($char2 & 0x3F) << 6) | (($char3 & 0x3F) << 0)];
							if($this->config['TargetLang'] == 'GBK') {
								$out .= $this->_hex2bin(dechex($char4 + 0x8080));
							} elseif($this->config['TargetLang'] == 'BIG5') {
								$out .= $this->_hex2bin($char4);
							}
							break;
					}
				}
				return $out;
			} else {
				while($SourceText != '') {
					if(ord(substr($SourceText, 0, 1)) > 127) {
						if($this->config['SourceLang'] == 'BIG5') {
							$utf8 = $this->CHSUtoUTF8(hexdec($this->unicode_table[hexdec(bin2hex(substr($SourceText, 0, 2)))]));
						} elseif($this->config['SourceLang'] == 'GBK') {
							$utf8=$this->CHSUtoUTF8(hexdec($this->unicode_table[hexdec(bin2hex(substr($SourceText, 0, 2))) - 0x8080]));
						}
						for($i = 0; $i < strlen($utf8); $i += 3) {
							$ret .= chr(substr($utf8, $i, 3));
						}
						$SourceText = substr($SourceText, 2, strlen($SourceText));
					} else {
						$ret .= substr($SourceText, 0, 1);
						$SourceText = substr($SourceText, 1, strlen($SourceText));
					}
				}
				//$this->unicode_table = array();
				$SourceText = '';
				return $ret;
			}
		}
	}

	/**
	 * Utf8编码转换成Unicode编码
	 *
	 * @param string $char 要进行转换的字符
	 * @return string 转换后的字符
	 */
	function Utf8_Unicode($char) {
		switch(strlen($char)) {
			case 1:
				return ord($char);
			case 2:
				$n = (ord($char[0]) & 0x3f) << 6;
				$n += ord($char[1]) & 0x3f;
				return $n;
			case 3:
				$n = (ord($char[0]) & 0x1f) << 12;
				$n += (ord($char[1]) & 0x3f) << 6;
				$n += ord($char[2]) & 0x3f;
				return $n;
			case 4:
				$n = (ord($char[0]) & 0x0f) << 18;
				$n += (ord($char[1]) & 0x3f) << 12;
				$n += (ord($char[2]) & 0x3f) << 6;
				$n += ord($char[3]) & 0x3f;
				return $n;
		}
	}
	
	function Urldecode($SourceText) {
		if (is_array($SourceText)) {
			foreach($SourceText as $key => $text) {
				$SourceText[$key] = $this->Urldecode($text);
			}
		} else {
			$SourceText = urldecode($SourceText);
		}
		return $SourceText;
	}
	
	function Urlencode($SourceText) {
		if (is_array($SourceText)) {
			foreach($SourceText as $key => $text) {
				$SourceText[$key] = $this->Urlencode($text);
			}
		} else {
			$SourceText = urlencode($SourceText);
		}
		return $SourceText;
	}

}