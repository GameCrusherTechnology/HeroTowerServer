<?php
require_once FRAMEWORK . '/game/ModelBase.class.php';

/**
 * 任务系统的任务信息更新类。该类根据提供的任务xml描述文件，
 * 解析得到一个数组。
 *
 */
class TaskCompiler extends ModelBase {

	/**
	 * 编译一个任务描述文件为一个序列化以后的数据文件，并返回解析后的数组
	 * @param string $xml_file
	 * @return array
	 */
	public function compile($xml_file){
		if(!file_exists($xml_file)){
			$this->throwException('xml file not exist.',1);
		}
		$file_name = basename($xml_file,'.xml');
		$dir = dirname($xml_file);
		$obj_file = $dir . '/' . $file_name . '.dat';
		// 如果目标文件不存在，或者xml的最后修改时间较新，从新生成xml的序列化文件
		if(!file_exists($obj_file) || filemtime($obj_file) < filemtime($xml_file)){
			$xml_obj = simplexml_load_file($xml_file);
			//file_put_contents('log/compiler.log',print_r($xml_obj,true));
			// 如果没有定义task，则抛出异常
			if(empty($xml_obj->task)){
				$this->throwException('no tasks defined.',1);
			}
			$task_list = array();
			foreach ($xml_obj->task as $obj_task) {
				$task = array();
				$attributes = $obj_task->attributes();
				$task['task_id'] = intval($attributes['id']);
				// task的id必须大于0
				if(empty($task['task_id'])){
					throw new Exception('task id must > 0',1);
				}
				$task['coin'] = intval($obj_task->awards->coin);
				$task['experience'] = intval($obj_task->awards->experience);
				$level_limit = $obj_task->grade->attributes();
				$task['level_min'] = intval($level_limit['min']);
				$task['level_max'] = intval($level_limit['max']);
				$task['next_task'] = intval($obj_task->next_id);
				$task['prev_task'] = 0;
				$task_list[$task['task_id']] = $task;
			}
			// 设置task的前置task id
			$this->setPrevTask($task_list);
			// 写入解析得到的xml的序列化结果到缓存文件
			if(file_put_contents($obj_file,serialize($task_list)) === false){
				$this->throwException('write cache file error',1);
			}
			return $task_list;
		}else{
			return unserialize(file_get_contents($obj_file));
		}
	}
	/**
	 * 根据存在的task列表，设置task的前置task id
	 * @param array $task_list 该值通过引用传递，会改变task列表数据
	 * 中的prev_task字段的值
	 */
	protected function setPrevTask(&$task_list){
		foreach ($task_list as $task_id => $task) {
			if(!empty($task['next_task'])){
				$next_task = &$task_list[$task['next_task']];
				$next_task['prev_task'] = $task_id;
			}
		}
	}
}

?>