<?php
require_once GAMELIB.'/actions/GameActionBase.class.php';
abstract class DataidActionBase extends GameActionBase {
	protected $data_mgr = null;
	protected $data = array();
	protected function getDataManager() {
		return $this->data_mgr;
	}
	//避免重复的对数据的取用
	protected function getData(){
		return $this->data;
	}
	protected function validate() {
		parent::validate();
		$data_id = $this->getParam("data_id");
		$target_gameuid = $this->getTargetGameuid();
		if ($data_id <= 0) return;
		$this->data = $this->getDataManager()->get($target_gameuid, $data_id);
		if (empty($this->data)) {
			$this->throwException("the data[data_id=$data_id,gameuid=$target_gameuid] does not exist.", GameStatusCode::DATA_NOT_EXISTS);
		}
	}
}
?>