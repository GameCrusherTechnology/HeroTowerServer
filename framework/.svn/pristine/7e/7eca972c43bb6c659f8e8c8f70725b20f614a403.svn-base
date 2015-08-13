<?php

class Guild extends ModelBase{
	/**
	 * 取得工会的列表
	 * @param array $params
	 * @return array
	 */
	public function getList($params){
		$page = intval($params['page']);
		$per_page_count = intval($params['per_page']);
		if($page < 1){
			$page = 1;
		}
		if($per_page_count < 1){
			$per_page_count = 10;
		}
		$offset = ($page - 1) * $per_page_count;
		
		$sql = "select * from guild limit $offset,$per_page_count";
		$list = $this->dbhelper->getAll($sql);
		return $list;
	}
	
	/**
	 * 获得一个行会的信息。
	 * @param array $params
	 */
	public function getInfo($params){
		$gid = intval($params['gid']);
		$sql = "select * from guild where gid = %d";
		$list = $this->dbhelper->getOne($sql,$gid);
		return $list;
	}
	
	/**
	 * @see IAction::execute()
	 *
	 * @param array $params
	 * @return array
	 */
	public function create($params) {
		$guild_keys = array('uid','name','max_num','affiche','username');
		$guild_info = $this->getParams($guild_keys,$params);
		$gid = $this->dbhelper->inserttable('guild',$guild_info,true);
		return $gid;
	}
	
	public function update($gid,$update_info){
		$info_keys = array('affiche','vice_uid','money','prestige',
		'description','status','type','member_num');
		$update = $this->getExistParams($info_keys,$update_info);
		$this->dbhelper->updatetable('guild',$update,array('gid' => $gid));
	}

	public function delete($gid){
		$this->dbhelper->execute('delete from guid where gid = %d',$gid);
	}
	
	public function joinMember($gid,$member){
		if(empty($member['job']) || empty($gid) || empty($member['uid'])){
			$this->throwException('job is empty',STATUS_PARAMETER_ERROR);
		}
		$insert_info = $this->getParams(array('jon','uid','status'),$member);
		$insert_info['gid'] = $gid;
		$this->dbhelper->inserttable('guild_member',$insert_info);
		
	}
	
	public function deleteMember($gid,$uid){
		$sql = "delete from guild_member where gid=%d and uid = %d";
		$this->dbhelper->execute($sql,array($gid,$uid));
	}
	
	public function updateMemberInfo($gid,$member){
		$update_keys = array('job','status');
		$update_info = $this->getExistParams($update_keys,$member);
		$this->dbhelper->updatetable('guild_member',$update_info,array('gid' => $gid));
	}
	
}

?>