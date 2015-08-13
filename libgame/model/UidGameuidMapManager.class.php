<?php
class UidGameuidMapManager extends ManagerBase {
	/**
	 * 通过uid获取用户的gameuid
	 *
	 * @param 用户的uid $uid
	 * @return 用户的gameuid
	 */
	public function getGameuid($uid) {
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("start to get mapped gameuid from uid[$uid]");
		}
		$req = RequestFactory::createGetRequest(get_app_config());
		$req->setTable($this->getTableName());
		$req->addKeyValue("uid", $uid);
		$result = $req->fetchOne();
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("retrieved gameuid uid mapping[uid=$uid,gameuid=".$result['gameuid']."]");
		}
		if (empty($result) || empty($result['gameuid'])) return false;
		return $result['gameuid'];
	}
	/**
	 * 通过gameuid获取用户的uid
	 *
	 * @param 用户的gameuid $gameuid
	 * @return 用户的uid
	 */
	public function getUid($gameuid) {
		$req = RequestFactory::createGetRequest(get_app_config());
		$req->setTable($this->getTableName());
		$req->addKeyValue("gameuid", $gameuid);
		$result = $req->fetchOne();
		if (empty($result) || empty($result['uid'])) return false;
		return $result['uid'];
	}
	/**
	 * 通过gameuid获取用户的create_time
	 *
	 * @param 用户的gameuid $gameuid
	 * @return 用户的create_time
	 */
	public function getCreatetime($gameuid) {
		$req = RequestFactory::createGetRequest(get_app_config());
		$req->setTable($this->getTableName());
		$req->addKeyValue("gameuid", $gameuid);
		$result = $req->fetchOne();
		if (empty($result) || empty($result['create_time'])) return false;
		return $result['create_time'];
	}
	/**
	 * 通过一组uid获取相应的gameuid
	 *
	 * @param 用户的uid数组 $uids
	 * @return 相应的gameuids
	 */
	public function getGameuidList($uids) {
		if (empty($uids)) return array();
		if (is_string($uids)) $uids = explode(",", $uids);
		if (!is_array($uids)) $uids = array($uids);
		if (count($uids) == 0) return array();
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("start to get mapped gameuids from uid list[".print_r($uids,true)."]");
		}
		$req = RequestFactory::createGetRequest(get_app_config());
		$req->setTable($this->getTableName());
		$req->addKeyValue("uid", $uids);
		$req->setCacheType(TCRequest::CACHE_IN_LIST);
		$gameuids = $req->execute();
		if (empty($gameuids)) return array();
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("retrieved gameuid uid mapping[".print_r($gameuids,true)."]");
		}
		return $gameuids;
	}
	/**
	 * 将uid和gameuid绑定
	 *
	 * @param string $uid 用户uid
	 * @param int $gameuid 用户gameuid
	 * @return void
	 */
	public function createMapping($uid, $gameuid) {
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("mapping uid[$uid] with gameuid[$gameuid]");
		}
		$req = RequestFactory::createInsertRequest(get_app_config());
		$req->setTable($this->getTableName());
		$req->setColumns("uid,gameuid");
		$req->addValues(array($uid, $gameuid));
		$req->execute();
	}
	
	/**
	 * 将uid从映射表中删除
	 *
	 * @param string $uid 用户uid
	 * @return bool 是否删除成功，成功返回true，否则返回false
	 */
	public function deleteMapping($uid) {
		$this->logger->writeInfo("tring to delete uid[$uid] from uid_gameuid_mapping.");
		$req = RequestFactory::createDeleteRequest(get_app_config());
		$req->setTable($this->getTableName());
		$req->addKeyValue("uid", $uid);
		$affected_rows = $req->execute();
		if (intval($affected_rows) > 0) return true;
		return false;
	}
	protected function getTableName() {
		return "uid_gameuid_mapping";
	}
}
?>