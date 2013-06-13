<?php
defined('SITE_PATH') OR exit('SET_ERROR');
// 载入系统常量定义
define('G_PATH', __DIR__);
require(G_PATH."/gDefine.php");
// 载入核心函数库
require(G_PATH."/gFunctions.php");

//note 判断服务器类型（test,develop,product）
gLoadServer();
//note 根据服务器类型加载配置文件
include SITE_PATH . "/include/config_" . QIHOOTYPE . ".php";

// 载入配置文件
empty ($gConfig) && $gConfig = null;
$GLOBALS['G'] = gConfigReady(require(G_PATH."/gConfig.php"),$gConfig);
if (strtolower($GLOBALS['G']['mode']) == 'debug') {
	define('DEBUG', TRUE);
} else {
	define('DEBUG', FALSE);
}

// 自动开启SESSION
if($GLOBALS['G']['auto_session'])@session_start();

gImport(G_CORE."/gArgs.php", FALSE, TRUE);
// 载入核心MVC架构文件
gImport(G_CORE."/gController.php", FALSE, TRUE);
gImport(G_CORE."/gModel.php", FALSE, TRUE);
gImport(G_CORE."/gView.php", FALSE, TRUE);

empty ($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] = '';
gParseURL(htmlspecialchars($_SERVER['REQUEST_URI']));