<?php
// 1000以下的为保留错误，framework使用的
class GameStatusCode {
	//  未知错误
	const UNKNOWN_ERROR = 1;
	//已经使用过
	const HAS_USED = 2;
	// status constants definition
	const DATABASE_ERROR = 1000;
	// 用户不存在
	const USER_NOT_EXISTS = 1001;
	// 指定的item不存在
	const ITEM_NOT_EXISTS = 1002;
	// 动作不存在
	const ACTION_NOT_EXISTS = 1003;
	// 该条数据不存在
	const DATA_NOT_EXISTS = 1004;
	// 用户的item不足
	const ITEM_NOT_ENOUGH = 1005;
	// 用户的authcode已过期
	const AUTH_CODE_EXPIRED = 1006;
	// 用户的authcode错误
	const AUTH_CODE_ERROR = 1007;
	// memcache set failure
	const SET_MEMCACHE_ERROR = 1008;
	// 要送的礼物不存在
	const GIFT_NOT_EXISTS = 1009;
	// 客户端传的参数不正确
	const PARAMETER_ERROR = 1010;
	// 数据不正确
	const DATA_ERROR = 1011;
	
	
	// 系统维护中
	const SYSTEM_MAINTAIN = 2001;
	const USER_IS_BANNED = 2002;
}

class CacheKey {
	// 用户信息的缓存
	const CACHE_KEY_PLATFORM_USER_INFO = 'ck_ui_%s';
	// 用户好友的缓存
	const CACHE_KEY_PLATFORM_USER_FRIENDS = 'ck_uf_%s';
	// 用户的event log
	const CACHE_KEY_USER_EVENT_LOG = 'ck_el_%d';
	// 用户的action log
	const CACHE_KEY_USER_ACTION_LOG = 'ck_al_%d';
	// 缓存单条database定义, %s为数据库类型, %d为单条记录的id
	const CACHE_KEY_DATABASE_SINGLE_DEF = 'ck_%s_%d';
	// 缓存全部database定义, %s为数据库类型
	const CACHE_KEY_DATABASE_ALL_DEFS = 'ck_%s';
	// 测试版缓存单条database定义, %s为数据库类型, %d为单条记录的id
	const CACHE_KEY_DATABASE_SINGLE_DEF_TEST = 'ck_test_%s_%d';
	// 测试版缓存全部database定义, %s为数据库类型
	const CACHE_KEY_DATABASE_ALL_DEFS_TEST = 'ck_test_%s';
	// 判断用户是否可以发生某个操作的缓存
	const CACHE_KEY_CAN_ACTION_HAPPEN_FLAG = 'ck_ah_%d_%d';
	// 数据库中变化量的累积记录的缓存
	const CACHE_KEY_ACCUMULATION_FLAG = 'ck_acc_%s_%d';
	
}

class XmlDbType {
	const XMLDB_ITEM = 'item';
}

class InitUser{
	public static $account_arr = array(	
										'gem' => 50,
										'coin' => 1000,
										'extra'=>1
										);
	public static $item_arr = array(	
					"10000"=>array(	array('item_id'=>80000,"count"=>5),
							array('item_id'=>70100,"count"=>10),
							array('item_id'=>70002,"count"=>10)),
					"10001"=>array(	array('item_id'=>80000,"count"=>5),
							array('item_id'=>70101,"count"=>10),
							array('item_id'=>70001,"count"=>10)),
					"10002"=>array(	array('item_id'=>80000,"count"=>5),
							array('item_id'=>70100,"count"=>10),
							array('item_id'=>70005,"count"=>10)),
	);
	
	public static $treasure_activity = array(
		"littleCoin"=>array("id"=>"littleCoin","count"=>10000,"type"=>"coin","vip"=>10),
		"middleCoin"=>array("id"=>"middleCoin","count"=>50000,"type"=>"coin","vip"=>50),
		"largeCoin"	=>array("id"=>"largeCoin","count"=>100000,"type"=>"coin","vip"=>100),
		"littleGem"	=>array("id"=>"littleGem","count"=>100,"type"=>"gem","vip"=>10),
		"middleGem"	=>array("id"=>"middleGem","count"=>500,"type"=>"gem","vip"=>50),
		"largeGem"	=>array("id"=>"largeGem","count"=>1000,"type"=>"gem","vip"=>100),
		"time"	=> "1422575999"
	);
	
	
}
class MethodType {
	//表示物品的变化方式
	const METHOD_APPEND = 1;
	const METHOD_UPDATE = 2;
	const METHOD_ADD = 3;
	const METHOD_SUB = 4;
	const METHOD_DEL = 5;
	
	//购买物品时的方式
	const METHOD_COIN = 1;
	const METHOD_MONEY = 2;
	
	
}

class StaticFunction{
	public static function expToGrade($exp){
        return intval (sqrt($exp/10));
    }
    public static function gradeToExp($grade){
        return intval(pow($grade,2)*10);
    }
	public static function gradeToLove($grade){
        return intval(pow($grade+1,2)*10 - pow($grade,2)*10);
    }
	public static function expToVip($exp){
        return intval (sqrt($exp/10));
    }
    public static function getStrengthLimit($level=0,$increace=0){
    	return intval($level/2)+$increace + 15;
    }
    
    public static function getUpSkillCoin($level){
    	$arr = array(100,500,1000,2000,4000,6000,8000,10000,15000,20000);
    	return $arr[$level];
    }
 	public static function getUpSoldierCoin($level){
    	$arr = array(100,1000,5000,10000,20000);
    	return $arr[$level];
    }
    public static $heroCost = array(
			array("gem"=>10,"coin"=>100),
			array("gem"=>100000,"coin"=>100000),
			array("gem"=>100,"coin"=>1000),
			array("gem"=>100000,"coin"=>100000),
			array("gem"=>100,"coin"=>1000),
			array("gem"=>100000,"coin"=>100000),
			array("gem"=>100,"coin"=>1000),
			array("gem"=>100000,"coin"=>100000),
			array("gem"=>100,"coin"=>1000),
			array("gem"=>100000,"coin"=>100000)
		);
    public static $wildRewardRate =  array(
    					array(70,30),
				    	array(60,30,10),
				    	array(40,40,20),
				    	array(30,30,20,10,10),
				    	array(10,20,30,20,20)
				    	);
	 public static $wildRewards =  array( 
	 					array('coin'=>1200,'coin'=>800,'exp'=>150,'14000'=>1,'20001'=>1,'50009'=>1,'50010'=>1),
				    	array('coin'=>2500,'coin'=>1500,'exp'=>100,'14000'=>1,'14001'=>1,'20001'=>2,'50010'=>1,'50011'=>1),
				    	array('coin'=>3000,'coin'=>6000,'14000'=>1,'14001'=>1,'14002'=>1,'20001'=>3,'50010'=>1,'50011'=>1,'50012'=>1),
				    	array('14000'=>1,'14001'=>1,'14002'=>1,'14003'=>1,'14004'=>1,'20001'=>8,'50011'=>1,'50012'=>1),
				    	array('14006'=>1,'14005'=>1,'14004'=>1,'20001'=>30,'50012'=>1)
			    	);
			    	
	public static $VipList = array(
			array('level'=>0,'energyAddTotal'=>0,'tradeMoneyCount'=>1,'useEnergyCount'=>3,'useCardCount'=>3,'inviteFriendCount'=>1,'pkAddCount'=>3),
			array('level'=>1,'energyAddTotal'=>5,'tradeMoneyCount'=>2,'useEnergyCount'=>4,'useCardCount'=>5,'inviteFriendCount'=>1,'pkAddCount'=>4),
			array('level'=>2,'energyAddTotal'=>10,'tradeMoneyCount'=>3,'useEnergyCount'=>5,'useCardCount'=>7,'inviteFriendCount'=>1,'pkAddCount'=>5),
			array('level'=>3,'energyAddTotal'=>15,'tradeMoneyCount'=>4,'useEnergyCount'=>6,'useCardCount'=>9,'inviteFriendCount'=>2,'pkAddCount'=>6),
			array('level'=>4,'energyAddTotal'=>20,'tradeMoneyCount'=>5,'useEnergyCount'=>7,'useCardCount'=>12,'inviteFriendCount'=>3,'pkAddCount'=>7),
			array('level'=>5,'energyAddTotal'=>25,'tradeMoneyCount'=>6,'useEnergyCount'=>8,'useCardCount'=>15,'inviteFriendCount'=>4,'pkAddCount'=>8),
			array('level'=>6,'energyAddTotal'=>30,'tradeMoneyCount'=>7,'useEnergyCount'=>9,'useCardCount'=>18,'inviteFriendCount'=>5,'pkAddCount'=>9),
			array('level'=>7,'energyAddTotal'=>35,'tradeMoneyCount'=>8,'useEnergyCount'=>10,'useCardCount'=>22,'inviteFriendCount'=>6,'pkAddCount'=>10),
			array('level'=>8,'energyAddTotal'=>40,'tradeMoneyCount'=>9,'useEnergyCount'=>15,'useCardCount'=>25,'inviteFriendCount'=>7,'pkAddCount'=>11),
			array('level'=>9,'energyAddTotal'=>50,'tradeMoneyCount'=>10,'useEnergyCount'=>20,'useCardCount'=>30,'inviteFriendCount'=>8,'pkAddCount'=>12)
			);
	public static function getTotalPower($level,$vipL){
		return 80+$level*2+StaticFunction::$VipList[$vipL]['energyAddTotal'];
	}
	
	//monster list
	public static $MonsterList=array(50000,50001,50002,50003,50004,50005,50006,50007,50008,50009,
									 50010,50011,50012,50013,50014,50015,50016,50017,50018,50019,
									 50020,50021,50022,50023,50024,50025,50026);
	public static $POWER_CYCLE = 300;
	public static $power_battle_cost = 5;
	public static function resetCurPower($hero_info){
		$power = $hero_info['power'];
		$powertime = $hero_info['powertime'];
		$totalPower = StaticFunction::getTotalPower(StaticFunction::expToGrade($hero_info['exp']),StaticFunction::expToVip($hero_info['vip']));
		
		$curpower = 0;
		if ($power>=$totalPower){
			$curpower = $power;
		}else{
			$curpower = $power + floor((time() - $powertime)/StaticFunction::$POWER_CYCLE);
			$curpower = min($curpower,$totalPower);
		}
		$hero_info['power'] = $curpower;
		$hero_info['powertime'] = time();
		return $hero_info;
	}
	public static function getWildReward($step){
//    	$rate = StaticFunction::$wildRewardRate[$step];
//    	$key = StaticFunction::getOneByRate($rate);
    	
		$rewards = StaticFunction::$wildRewards[$step];
		$r_key = array_rand($rewards,1);
		
		return array("id"=>$r_key,"count"=>$rewards[$r_key]);
    }
    /**
     * 获取一系列rate中的一个而且必须要出现的一个
     *	rate必须是整数
     * @param $awards_rate array($k=>$rate)
     * @return unknown
     */
	public static function getOneByRate($awards_rate){
		$total_rand=array_sum($awards_rate);
		$rand_key=mt_rand(0,$total_rand);
		foreach ($awards_rate as $k=>$rate){
			if ($rand_key<=$rate){
				return $k;
			}else {
				$rand_key-=$rate;
			}
		}
	}
}
?>