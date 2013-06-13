<?php

return array(
	'mode' => 'online', // 应用程序模式，默认为调试模式
	'g_include_path' => array(), // 框架扩展功能载入路径
	'launch' => array(), // 自动执行点的根节点

	'auto_load_controller' => array(), // 控制器自动加载的扩展类名
	'auto_load_model' => array(''), // 模型自动加载的扩展类名

	'inst_class' => array(), // 已实例化的类名称
	'import_file' => array(), // 已经载入的文件
	'view_registered_functions' => array(), // 视图内挂靠的函数记录

	'default_controller' => 'main', // 默认的控制器名称
	'default_action' => 'index',  // 默认的动作名称


	'auto_session' => TRUE, // 是否自动开启SESSION支持

	'url' => array( // URL设置
		'url_path_info' => FALSE, // 是否使用path_info方式的URL
		'url_path_base' => '', // URL的根目录访问地址，默认为空则是入口文件index.php
	),

	'db' => array(  // 数据库连接配置
            'driver' => 'gMysql',     // 驱动类型
            'prefix' => '',           // 表前缀
            'master' => array (
		'host' => 'localhost', // 数据库地址
		'port' => 3306,        // 端口
		'login' => 'root',     // 用户名
		'password' => 'root',      // 密码
		'database' => 'database_name',      // 库名称		
		'persistent' => FALSE,    // 是否使用长链接
                'charset' => 'utf8'
            ),
           /* 'slave' => array (
                0 => array (
                    'host' => 'localhost', // 数据库地址
                    'port' => 3306,        // 端口
                    'login' => 'root',     // 用户名
                    'password' => 'root',      // 密码
                    'database' => 'database_name',      // 库名称
                    'persistent' => FALSE,    // 是否使用长链接
                    'charset' => 'utf8'
                ),
            )*/
		
	),
	'db_driver_path' => '', // 自定义数据库驱动文件地址
	'db_full_tblname' => TRUE, // DB是否使用表全名

	'view' => array( // 视图配置
		'enabled' => TRUE, // 开启视图
		'config' =>array(
			'template_dir' => G_SITE_VIEW_PATH.'/template_dir', // 模板目录
			'compile_dir' =>G_SITE_VIEW_PATH.'/compile_dir', // 编译目录
			'cache_dir' => G_SITE_VIEW_PATH.'/cache_dir', // 缓存目录
			'left_delimiter' => '{',  // smarty左限定符
			'right_delimiter' => '}', // smarty右限定符
			'auto_literal' => TRUE, // Smarty3新特性
		),
		'debugging' => FALSE, // 是否开启视图调试功能，在部署模式下无法开启视图调试功能
		'engine_name' => 'Smarty', // 模板引擎的类名称，默认为Smarty
		'engine_path' => G_LIBS.'/Smarty/Smarty.class.php', // 模板引擎主类路径
		'auto_ob_start' => TRUE, // 是否自动开启缓存输出控制
		'auto_dieflay' => FALSE,
		'auto_display' => FALSE, // 是否使用自动输出模板功能
		'auto_display_sep' => '/', // 自动输出模板的拼装模式，/为按目录方式拼装，_为按下划线方式，以此类推
		'auto_display_suffix' => '.html', // 自动输出模板的后缀名
	),

	'include_path' => array(
		SITE_PATH.'/include',
        G_SITE_MODEL_PATH,
        G_LIBS,
	), // 用户程序扩展类载入路径    
);
