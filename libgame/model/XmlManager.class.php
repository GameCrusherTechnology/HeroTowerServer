<?php
include_once GAMELIB.'/model/ManagerBase.class.php';
class XmlManager {
	/**
	 * 获取xml中的数据
	 *
	 * @param 需要获取的数据类型 $type
	 * key：method(task,item)
	 */
	public function getList($type){
		$xml_db_info = $this->getXmlDbInfo($type);
		if ($xml_db_info === false) return false;
		if (isset($xml_db_info['type'])) $type = $xml_db_info['type'];
		$database_path = APP_ROOT.'/database/'.$xml_db_info['database_path'];
    	$simple_dom = simplexml_load_file($database_path);
    	mb_internal_encoding("UTF-8");
		$root = dom_import_simplexml($simple_dom);
		$arrItems=array();
		$this->read_child($root,$arrItems,$type);
		return $arrItems;
	}
	
	private function getArray($node) {
	  $array = false;
	  if ($node->hasAttributes()) {
	    foreach ($node->attributes as $attr) {
	      $array[$attr->nodeName] = $attr->nodeValue;
	    }
	  }
	
	  if ($node->hasChildNodes()) {
	    if ($node->childNodes->length == 1) {
	      $array[$node->firstChild->nodeName] = getArray($node->firstChild);
	    } else {
	      foreach ($node->childNodes as $childNode) {
	      if ($childNode->nodeType != XML_TEXT_NODE) {
	        $array[$childNode->nodeName][] = getArray($childNode);
	      }
	    }
	  }
	  } else {
	    return $node->nodeValue;
	  }
	  return $array;
	}
	protected function read_child($dom,&$arrItems,$type){
		if ($dom->hasChildNodes()){
			foreach ($dom->childNodes as $child){
				$this->read_child($child,$arrItems,$type);
			}
		}else {
			$tag_name='';
			$tag_name=$dom->tagName;
			if (strlen($tag_name)!=0){
				//获取父节点的信息
				$parent=$dom->parentNode;
				$parent_obj=simplexml_import_dom($parent);
				$parent_arr=$this->objToArr($parent_obj);
				//处理子节点的信息
				$arrItem_obj=simplexml_import_dom($dom);
				$arrItem_arr=$this->objToArr($arrItem_obj);
				$arrItem_arr['group']=$tag_name;
				$arrItem_arr[$type.'_type']=intval($arrItem_arr[$type.'_id']/1000);
				if (isset($parent_arr['item_id'])){
					$arrItem_arr['parent_id']=$parent_arr['item_id'];
					if (array_key_exists($parent_arr['item_id'],$arrItems)){
						$arrItems[$parent_arr['item_id']]['children_ids'][]=$arrItem_arr['item_id'];
					}else {
						$parent_arr['children_ids']=array();
						$parent_arr['children_ids'][]=$arrItem_arr['item_id'];
						$arrItems[$parent_arr['item_id']]=$parent_arr;
					}
				}
				$arrItems[$arrItem_arr[$type.'_id']]=$arrItem_arr;
			}
		}
	}
	protected function objToArr($obj){
		$arr=array();
		foreach ($obj->attributes() as $k=>$v){
			$arr[$k]=strval($v);
		}
		return $arr;
	}
	/**
	 * 获取将要解析的xml的相关信息；
	 *
	 * @param 数据类型 $type
	 * key:method
	 * @return array
	 * key:database_path,xpath,id_name
	 */
	private function getXmlDbInfo($type){
		switch($type) {
			case XmlDbType::XMLDB_ITEM:
				return array('database_path'=>'game.xml','xpath'=>'/database/Group');
			default:
				return false;
		}
	}
}

abstract class DatabaseManager extends ManagerBase {
	protected $db_type = null;
	/*
	 * 这里的定义需要根据正式版和测试版进行处理
	 */
	//存储单条数据的key
	protected $single_key_def=CacheKey::CACHE_KEY_DATABASE_SINGLE_DEF;
	//存储list的key
	protected $list_key_def=CacheKey::CACHE_KEY_DATABASE_ALL_DEFS;
//	//测试版存储单条数据的key
//	protected $single_key_def=CacheKey::CACHE_KEY_DATABASE_SINGLE_DEF_TEST;
//	//测试版存储list的key
//	protected $list_key_def=CacheKey::CACHE_KEY_DATABASE_ALL_DEFS_TEST;
	public function getDef($id) {
		if (empty($id) || intval($id) <= 0) return false;
		$key = sprintf($this->single_key_def, $this->db_type, $id);
		$def = $this->getFromCache($key);
		if ($def === false) {
			$defs = $this->getParsedDefListFromXml();
			$def = $defs[$id];
			foreach ($defs as $entry_id=>$entry) {
				$this->setToCache(sprintf($this->single_key_def, $this->db_type, $entry_id), $entry,null,0);
			}
			if (!empty($def)){//说明xml文件修改了，需要重新设置list了
				$this->setToCache(sprintf($this->list_key_def,$this->db_type),$defs,null,0);
			}
		}
		if (empty($def)){
			$this->throwException("defination of id[$id] not exist in xml,db_type is ".$this->db_type,GameStatusCode::DATABASE_ERROR);
		}
		return $def;
	}
	
	public function getDefList() {
		$key = sprintf($this->list_key_def, $this->db_type);
		$defs = $this->getFromCache($key);
		if (true || $defs === false) {
			$defs = $this->getParsedDefListFromXml();
			$this->setToCache($key, $defs, null, 0);
		}
		return $defs;
	}
	public function updateDef(){
		$defs = $this->getParsedDefListFromXml();
		foreach ($defs as $id=>$entry) {
			$this->setToCache(sprintf($this->single_key_def, $this->db_type, $id), $entry,null,0);
		}
		$this->setToCache(sprintf($this->list_key_def, $this->db_type), $defs, null, 0);
		return true;
	}
	protected function getParsedDefListFromXml(){
		$xml_mgr = new XmlManager();
		$defs = $xml_mgr->getList($this->db_type);
		$defs = array_map(array($this,'parseDef'), $defs);
		return $defs;
	}
	protected function parseDef($entry) {
		return $entry;
	}
}
class ItemManager extends DatabaseManager {
	public function __construct() {
		$this->db_type = XmlDbType::XMLDB_ITEM;
	}
    protected function getTableName(){
    	return "item";
    }
	protected function parseDef($entry) {
		return $entry;
	}
}
?>