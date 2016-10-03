<?php
/**
 * 类名：xml2array
 * 功能：解析xml数据工具
 * 详细：兼容PHP4和PHP5，不需要修改
 * 版本：1.0
 * 修改日期：2010-12-27
 * 作者：POCLE Team
 * 说明：以下代码只是为了方便用户测试而提供的样例代码，用户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */
class xml2array {
	var $xml = '';
	var $length = 0;
	function __construct($xml) {
		if(empty($xml)) {
			return null;
		}
		$this->xml = $xml;
	}
	
	function _struct_to_array($values, &$i) {
		$child = array();
		if(isset($values[$i]['value']))
			array_push($child, $values[$i]['value']);
		
		while($i++ < $this->length) {
			
			switch($values[$i]['type']) {
				case 'cdata' :
					array_push($child, $values[$i]['value']);
					break;
				
				case 'complete' :
					$name = $values[$i]['tag'];
					if(!empty($name)) {
						$value = isset($values[$i]['value']) ? $values[$i]['value'] : '';
						
						$value = isset($values[$i]['attributes']) ? $this->_merge_attributes(array('value' => $value), $values[$i]['attributes']) : $value;
						
						$this->_stuff_child($name, $child, $value);
					
					}
					break;
				
				case 'open' :
					$name = $values[$i]['tag'];
					
					if(!empty($name)) {
						$value = $this->_struct_to_array($values, $i);
						
						$value = isset($values[$i]['attributes']) ? $this->_merge_attributes($value, $values[$i]['attributes']) : $value;
						
						$this->_stuff_child($name, $child, $value);
					}
					
					break;
				
				case 'close' :
					return $child;
					break;
			}
		}
		return $child;
	}
	
	function _stuff_child($name, &$child, $value) {
		$tmp = null;
		
		if(isset($child[$name])) {
			if(!isset($child[$name][0])) {
				$tmp = $child[$name];
				$child[$name] = array();
				$child[$name][0] = $tmp;
			}
			$child[$name][sizeof($child[$name])] = $value;
			return true;		
		} else {
			$child[$name] = $value;
		}

		return true;
	}
	
	function _merge_attributes($value, $attributes = array()) {
		if($attributes) {
			$value = array_merge($attributes, $value);
		}
		return $value;
	}
	
	function parse($index_tag = '') {
		$xml = & $this->xml;
		$values = array();
		$index = array();
		$array = array();
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($parser, $xml, $values, $index);
		xml_parser_free($parser);

		$i = 0;
		$this->length = count($values);
		if($index_tag) {
			foreach($index as $k => $v) {
				if(strtolower($k) == strtolower($index_tag)) {
					$i = $v[0];
					$this->length = $v[count($v)-1];
					break;
				}
			}
		}
		
		$value = $this->_struct_to_array($values, $i);
		
		if(!$index_tag) {
			$name = $values[$i]['tag'];
			$array[$name] = isset($values[$i]['attributes']) ? $this->_merge_attributes($value, $values[$i]['attributes']) : $value;
		} else {		
			$array = isset($values[$i]['attributes']) ? $this->_merge_attributes($value, $values[$i]['attributes']) : $value;
		}
		return $array;
	}

}
