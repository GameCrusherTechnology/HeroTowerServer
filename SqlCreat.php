<?php
$con = mysql_connect("localhost","root","19870530");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

if (mysql_query("CREATE DATABASE IF NOT EXISTS herotower",$con))
  {
  echo "Database created";
  }
else
  {
  echo "Error creating database: " . mysql_error();
  }
/* CREATE TABLE IF NOT EXISTS `farm_account_0` (
  `gameuid` int(10) unsigned NOT NULL,
  `name` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `gem` int(10) unsigned NOT NULL DEFAULT '50',
  `coin` int(10) unsigned NOT NULL DEFAULT '0',
  `love` int(10) unsigned NOT NULL DEFAULT '0',
  `exp` int(10) unsigned NOT NULL DEFAULT '0',
  `sex` int(2) unsigned NOT NULL DEFAULT '0',
  `extend` int(4) unsigned NOT NULL DEFAULT '0',
  `crop_extend` int(4) NOT NULL,
  `title` varchar(10) CHARACTER SET utf8 NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `updatetime` int(10) unsigned NOT NULL,
  `achieve` varchar(300) CHARACTER SET utf8 NOT NULL,
  `skill` varchar(20) CHARACTER SET utf8 NOT NULL,
  `skill_time` int(10) NOT NULL,
  PRIMARY KEY (`gameuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
*/

//user_trade

  mysql_select_db("herotower", $con);
  $sql = "CREATE TABLE IF NOT EXISTS user_trade
	(
	data_id int(10) unsigned NOT NULL,
	gameuid int(10) unsigned NOT NULL,
  	product_id varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  	platform varchar(10) CHARACTER SET utf8 NOT NULL,
  	status int(4) NOT NULL,
  	orderId varchar(100) CHARACTER SET utf8 NOT NULL,
  	purchaseState int(4) NOT NULL,
  	purchasetime int(10) NOT NULL,
  PRIMARY KEY (data_id)
)";
if (!mysql_query($sql,$con)){
	echo	"<br>".mysql_error();
}
//uid_gameuid_mapping
$sql = "CREATE TABLE IF NOT EXISTS `uid_gameuid_mapping` (
  `uid` varchar(50) CHARACTER SET utf8 NOT NULL,
  `gameuid` int(10) NOT NULL,
  `create_time` varchar(10) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

if (!mysql_query($sql,$con)){
	echo	"<br>".mysql_error();
}

//user_item
$i = 0;
while ($i<10){
	$sql = "CREATE TABLE IF NOT EXISTS user_item_".$i." (
	  `gameuid` int(10) NOT NULL,
	  `item_id` int(10) NOT NULL,
	  `count` int(10) NOT NULL,
	  PRIMARY KEY (`gameuid`,`item_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if (!mysql_query($sql,$con)){
		echo	"<br>".mysql_error();
	}
	$i++;
}

//user_account

$i = 0;
while ($i<10){
	$sql = "CREATE TABLE IF NOT EXISTS user_account_".$i." (
	  `gameuid` int(10) NOT NULL,
	  `coin` int(10) NOT NULL,
	  `gem` int(10) NOT NULL,
	  login	varchar(50) CHARACTER SET utf8 NOT NULL,
	  extra	int(10),
	  PRIMARY KEY (`gameuid`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if (!mysql_query($sql,$con)){
		echo	"<br>".mysql_error();
	}
	$i++;
}
//character_account
$i = 0;
while ($i<10){
	$sql = "CREATE TABLE IF NOT EXISTS character_account_".$i." (
	  `characteruid` int(10) NOT NULL,
	  `exp` int(10) NOT NULL,
   	  `name` varchar(20) CHARACTER SET utf8 NOT NULL,
	  item_id int(10) NOT NULL,
	  soldiers varchar(100) CHARACTER SET utf8 NOT NULL,
	  soldierUpdate varchar(50) CHARACTER SET utf8 NOT NULL,
	  skills varchar(100) CHARACTER SET utf8 NOT NULL,
	  PRIMARY KEY (`characteruid`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if (!mysql_query($sql,$con)){
		echo	"<br>".mysql_error();
	}
	$i++;
}
//battle_info
$i = 0;
while ($i<10){
	$sql = "CREATE TABLE IF NOT EXISTS battle_info_".$i." (
	  gameuid int(10) NOT NULL,
	  groupId int(10) NOT NULL,
   	  ordinary_info varchar(200) CHARACTER SET utf8 NOT NULL,
	  elite_info varchar(100) CHARACTER SET utf8 NOT NULL,
	  PRIMARY KEY (gameuid,groupId)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if (!mysql_query($sql,$con)){
		echo	"<br>".mysql_error();
	}
	$i++;
}

//clan_info
$i = 0;
while ($i<10){
	$sql = "CREATE TABLE IF NOT EXISTS clan_info_".$i." (
	  data_id int(10) NOT NULL,
	  name varchar(20) CHARACTER SET utf8 NOT NULL,
	  adminId int(10) NOT NULL,
   	  clanMessage varchar(200) CHARACTER SET utf8 NOT NULL,
	  boss varchar(100) CHARACTER SET utf8 NOT NULL,
	  level int(10) NOT NULL,
	  members varchar(1000) CHARACTER SET utf8 NOT NULL,	
	  PRIMARY KEY (data_id)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if (!mysql_query($sql,$con)){
		echo	"<br>".mysql_error();
	}
	$i++;
}

//user_clan
$i = 0;
while ($i<10){
	$sql = "CREATE TABLE IF NOT EXISTS user_clan_".$i." (
	  gameuid int(10) NOT NULL,
	  clan_id int(10) NOT NULL,
	  contribution int(10) NOT NULL,
	  signTime int(10) NOT NULL,
	  bossTime int(10) NOT NULL,
	  PRIMARY KEY (gameuid)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if (!mysql_query($sql,$con)){
		echo	"<br>".mysql_error();
	}
	$i++;
}

//clan_item
$i = 0;
while ($i<10){
	$sql = "CREATE TABLE IF NOT EXISTS clan_item_".$i." (
	  data_id int(10) NOT NULL,
	  item_id int(10) NOT NULL,
	  count int(10) NOT NULL,
	  PRIMARY KEY (data_id,item_id)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if (!mysql_query($sql,$con)){
		echo	"<br>".mysql_error();
	}
	$i++;
}

mysql_close($con);
?>