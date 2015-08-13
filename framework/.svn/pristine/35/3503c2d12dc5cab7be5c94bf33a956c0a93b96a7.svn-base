<?php
import('elex.action.IAction');
import('elex.rest.DataProvider');

class RestServiceActionBase implements IAction {
	/**
	 *
	 * @var DataProvider
	 */
	protected $data_provider = null;
	
	protected $action_param_keys = array();
	/**
	 * 过滤器数组。
	 */
	protected $filters = array();
	
	public function __construct(DataProvider $provider){
		$this->data_provider = $provider;
	}
	
	/**
	 * 使用该函数注册一个过滤器，过滤器函数使用一个数组参数，该数组包含
	 * 外部请求传入的参数
	 * @param callback $filter
	 */
	protected function registerFilter($filter){
		if(is_callable($filter)){
			$this->filters[] = $filter;
		}else{
			trigger_error('filter not callable.');
		}
	}
	
	/**
	 * 执行所有的过滤器。
	 * @param array $params 通过引用传递的参数数组。
	 */
	protected function executeFilters(&$params){
		if(is_array($this->filters) && !empty($this->filters)){
			foreach ($this->filters as $filter) {
				$filter($params);
			}
		}
	}
	
	/**
	 * @see IAction::execute()
	 *
	 * @param array $params
	 */
	public function execute($params = null){
		trigger_error('function not implemented.');
	}

	/**
	 * 取得该动作需要的参数
	 *
	 * @param array $params
	 * @return array
	 */
	protected function getParams($params){
		$result = array();
		if(is_array($this->action_param_keys)){
			foreach ($this->action_param_keys as $key) {
				$result[] = isset($params[$key]) ? $params[$key] : '';
			}
		}elseif(is_string($this->action_param_keys)){
			$result[] = $params[$key];
		}
		return $result;
	}
}

?>