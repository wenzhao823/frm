<?php
	define('FRM_VERSION', '0.0.1'); // 当前框架版本

	if(!defined('SITE_PATH'))define('SITE_PATH', dirname(G_PATH).'/site');

	define('G_LIBS', G_PATH.'/libs');
	define('G_CORE', G_PATH.'/core');
	define('G_MSG', G_PATH.'/msg');

	define('G_SITE_CONTROLLER_PATH', SITE_PATH.'/controller');// 用户控制器程序的路径定义
	define('G_SITE_MODEL_PATH', SITE_PATH.'/model');// 用户模型程序的路径定义
	define('G_SITE_VIEW_PATH', SITE_PATH.'/view');// 用户视图路径定义
	define('G_SITE_CONTROLLER_PREFIX', '');// 用户控制器类前缀
	define('G_SITE_MODEL_PREFIX', '');// 用户模型类前缀

