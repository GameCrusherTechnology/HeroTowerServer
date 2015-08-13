<?php

class XMLSerielizer {
	/**
	 * 需要序列化的原始数据
	 * @var array
	 */
	protected $originalData;
	/**
	 * 序列化后的xml字符串
	 * @var string
	 */
	protected $xml = '';
	/**
	 * 生成的xml的字符集
	 * @var string
	 */
	protected $charset = 'utf-8';
	/**
	 * 创建的xml的命名空间
	 * @var string
	 */
	protected $namespace = '';
	/**
	 * 产生的xml的根节点
	 * @var string
	 */
	protected $rootElement = 'root';
	
	/**
	 * 如果一个数组中的key值是数字，那么使用该名字
	 * @var string
	 */
	protected $default_node_name = 'item';

	/**
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * @return string
	 */
	public function getRootElement() {
		return $this->rootElement;
	}

	/**
	 * @param string $namespace
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}

	/**
	 * @param string $rootElement
	 */
	public function setRootElement($rootElement) {
		$this->rootElement = $rootElement;
	}
	
	public function __construct($data){
		$this->originalData = $data;
	}
	
	/**
	 * 获取数据的xml表示
	 * @return string
	 */
	public function getXml(){
		if(empty($this->xml)){
			$this->xml = $this->toXML();
		}
		return $this->xml;
	}
	
	protected function toXML(){
		if(empty($this->originalData) || !is_array($this->originalData)){
			return sprintf("<%s></%s>",$this->rootElement,$this->rootElement);
		}
		$xmlstr = <<<XML
<?xml version='1.0' encoding='$this->charset'?>
<$this->rootElement></$this->rootElement>
XML;
		$xml = new SimpleXMLElement($xmlstr);
		$this->array2xml($xml,$this->originalData);
		return $xml->asXML();
	}
	
	/**
	 * 把一个数组转换成xml
	 * @param SimpleXMLElement $element
	 * @param array $data
	 */
	protected function array2xml(SimpleXMLElement &$element,array $data){
		foreach ($data as $key => $value) {
			if(is_numeric($key)){
				$key = $this->default_node_name;
			}
			if(is_array($value)){
				$child = $element->addChild($key);
				$this->array2xml($child,$value);
			}else{
				$element->addChild($key,$value);
			}
		}
	}
}
