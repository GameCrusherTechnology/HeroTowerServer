<?php

class Language {
	protected $lang_dir = '.';
	protected $locale = 'zh-cn';
	protected $lang_files = array();
	
	/**
	 * 取得一个key对应的消息
	 * @param $module string
	 * @param $key string
	 * @return string
	 */
	public function _($module,$key){
		$messages = $this->loadLangFile($module);
		if (isset($messages[$key])){
			return $messages[$key];
		}
		return null;
	}
	
	/**
	 * @return string
	 */
	public function getLocale() {
		return $this->locale;
	}
	
	/**
	 * @return string
	 */
	public function getLangDir() {
		return $this->lang_dir;
	}
	
	/**
	 * @param string $locale
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}
	
	/**
	 * @param string $lang_dir
	 */
	public function setLangDir($lang_dir) {
		$this->lang_dir = $lang_dir;
	}

	protected function loadLangFile($file){
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '',$file);
		if(isset($this->lang_files[$file])){
			return $this->lang_files[$file];
		}
		// language file name format : (lang dir)/(locale)/module_(locale).php
		$lang_file = $this->lang_dir . '/' . $this->locale . '/' . $file . '_'. $this->locale . '.php';
		if(file_exists($lang_file)){
			include $lang_file;
			$this->lang_files[$file] = $__message;
			return $__message;
		}
		throw new Exception("Language file $lang_file not exist");
	}
}

?>