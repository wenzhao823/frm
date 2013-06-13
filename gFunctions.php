<?php
//note 加载程序模式
function gLoadServer(){
    $qihoo_server_type = get_cfg_var('qihoo.server_type');
    empty($qihoo_server_type) && exit('SERVER_ERROR');
    define("QIHOOTYPE", $qihoo_server_type);
}
function gRun(){
	GLOBAL $__controller, $__action;

	// 对路由进行自动执行相关操作
	//gLaunch("router_prefilter");

	// 对将要访问的控制器类进行实例化
	$handle_controller = gClass($__controller, null, G_SITE_CONTROLLER_PATH."/{$__controller}.php");
	// 调用控制器出错将调用路由错误处理函数
	if(!is_object($handle_controller) || !method_exists($handle_controller, $__action)){
		//eval($GLOBALS['G']["diefatcher_error"]);
		//gError('控制器/动作 调用错误!');
		echo '您访问的页面不存在，请检查后重试。';
		exit;
	}
	// 路由并执行用户代码
	$handle_controller->$__action();
	// 控制器程序运行完毕，进行模板的自动输出
	if(FALSE != $GLOBALS['G']['view']['auto_dieflay']){
		$__tplname = $__controller.$GLOBALS['G']['view']['auto_dieflay_sep'].
				$__action.$GLOBALS['G']['view']['auto_dieflay_suffix']; // 拼装模板路径
		$handle_controller->auto_dieflay($__tplname);
	}
	// 对路由进行后续相关操作
	//gLaunch("router_postfilter");
}

/** gDump  格式化输出变量程序
 *
 * @param vars    变量
 * @param output    是否将内容输出
 * @param show_trace    是否将使用gError对变量进行追踪输出
 */
function gDump($vars, $output = TRUE, $show_trace = FALSE){
	// 部署模式下同时不允许查看调试信息的情况，直接退出。
	if(TRUE != g_DEBUG && TRUE != $GLOBALS['G']['allow_trace_onrelease'])return;
	if( TRUE == $show_trace ){ // 显示变量运行路径
		$content = gError(htmlefecialchars(print_r($vars, true)), TRUE, FALSE);
	}else{
		$content = "<div align=left><pre>\n" . htmlefecialchars(print_r($vars, true)) . "\n</pre></div>\n";
	}
    if(TRUE != $output) { return $content; } // 直接返回，不输出。
       echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>{$content}</body></html>";
	   return;
}

/** gImport  载入包含文件
 *
 * @param filename    需要载入的文件名或者文件路径
 * @param auto_search    载入文件找不到时是否搜索系统路径或文件，搜索路径的顺序为：应用程序包含目录 ->  ef框架包含文件目录
 * @param auto_error    自动提示扩展类载入出错信息
 */
function gImport($sfilename, $auto_search = TRUE, $auto_error = FALSE){
	if(isset($GLOBALS['G']["import_file"][md5($sfilename)]))return TRUE; // 已包含载入，返回
	// 检查$sfilename是否直接可读
	if( (TRUE == @is_file($sfilename)) && (TRUE == @is_readable($sfilename)) ){//必须是一个物理文件，而非目录
		require($sfilename); // 载入文件
		$GLOBALS['G']['import_file'][md5($sfilename)] = TRUE; // 对该文件进行标识为已载入
		return TRUE;
	}else{
		if(TRUE == $auto_search){ // 需要搜索文件
			// 按“应用程序包含目录 -> 框架包含文件目录”的顺序搜索文件
			foreach(array_merge( $GLOBALS['G']['g_include_path'],$GLOBALS['G']['include_path'] ) as $g_include_path){
				// 检查当前搜索路径中，该文件是否已经载入
				if(isset($GLOBALS['G']["import_file"][md5($g_include_path.'/'.$sfilename)]))return TRUE;
				if( is_readable( $g_include_path.'/'.$sfilename ) ){
					require($g_include_path.'/'.$sfilename);// 载入文件
					$GLOBALS['G']['import_file'][md5($g_include_path.'/'.$sfilename)] = TRUE;// 对该文件进行标识为已载入
					return TRUE;
				}
			}
		}
	}
	if( TRUE == $auto_error )gError("未能找到名为：{$sfilename}的文件");
	return FALSE;
}

/** gAccess 数据缓存及存取程序
 *
 * @param method    数据存取模式，取值"w"为存入数据，取值"r"读取数据，取值"c"为删除数据
 * @param name    标识数据的名称
 * @param value    存入的值，在读取数据和删除数据的模式下均为NULL
 * @param life_time    变量的生存时间，默认为永久保存
 */
function gAccess($method, $name, $value = NULL, $life_time = -1){}

/** gClass  类实例化函数  自动载入类定义文件，实例化并返回对象句柄
 *
 * @param class_name    类名称
 * @param args   类初始化时使用的参数，数组形式
 * @param sdir 载入类定义文件的路径，可以是目录+文件名的方式，也可以单独是目录。sdir的值将传入import()进行载入
 * @param force_inst 是否强制重新实例化对象
 */
function gClass($class_name, $args = null, $sdir = null, $force_inst = FALSE){
    
	// 检查类名称是否正确，以保证类定义文件载入的安全性
	if(preg_match('/[^a-z0-9\-_.]/i', $class_name))gError($class_name."类名称错误，请检查。");
	// 检查是否该类已经实例化，直接返回已实例对象，避免再次实例化
	if(TRUE != $force_inst)if(isset($GLOBALS['G']["inst_class"][$class_name]))return $GLOBALS['G']["inst_class"][$class_name];

	// 如果$sdir不能读取，则测试是否仅路径
	if(null != $sdir && !gImport($sdir) && !gImport($sdir."/{$class_name}.php"))return FALSE;

	$has_define = FALSE;
	// 检查类定义是否存在
	if(class_exists($class_name, false) || interface_exists($class_name, false)){
		$has_define = TRUE;
	}else{
		if( TRUE == gImport($class_name.'.php')){
			$has_define = TRUE;
		}
	}

	if(FALSE != $has_define){
		$argString = '';$comma = '';              
		if(null != $args)for ($i = 0; $i < count($args); $i ++) { $argString .= $comma . "\$args[$i]"; $comma = ', '; }
		eval("\$GLOBALS['G']['inst_class'][\$class_name]= new \$class_name($argString);");
		return $GLOBALS['G']["inst_class"][$class_name];
	}
	gError($class_name."类定义不存在，请检查。");
}

/** gError 框架定义的系统级错误提示
 *
 * @param msg    出错信息
 * @param output    是否输出
 * @param stop    是否停止程序
 */
function gError($msg, $output = TRUE, $stop = TRUE){
	//if($GLOBALS['G']['ef_error_throw_exception'])throw new Exception($msg);
	if(TRUE != DEBUG){error_log($msg);if(TRUE == $stop)exit;}
	$traces = debug_backtrace();
	$bufferabove = ob_get_clean();
	require_once(G_LIBS.'/gError.php');
	if(TRUE == $stop)exit;
}

/** gLaunch  执行挂靠程序
 *
 * @param configname    挂靠程序设置点名称
 * @param launchargs    挂靠参数
 * @param return    是否存在返回数据，如需要返回，则该挂靠点仅能有一个挂靠操作
 */
function gLaunch($configname, $launchargs = null, $returns = FALSE ){
	if( isset($GLOBALS['G']['launch'][$configname]) && is_array($GLOBALS['G']['launch'][$configname]) ){
		foreach( $GLOBALS['G']['launch'][$configname] as $launch ){
			if( is_array($launch) ){
				$reval = gClass($launch[0])->{$launch[1]}($launchargs);
			}else{
				$reval = call_user_func_array($launch, $launchargs);
			}
			if( TRUE == $returns )return $reval;
		}
	}
	return false;
}
/**
 *
 * gUrl
 *
 * URL模式的构建函数
 *
 * @param controller    控制器名称，默认为配置'default_controller'
 * @param action    动作名称，默认为配置'default_action' 
 * @param args    传递的参数，数组形式
 * @param anchor    跳转锚点
 */
function gUrl($controller = null, $action = null, $args = null, $anchor = null) {
	$controller = ( null != $controller ) ? $controller : $GLOBALS['G_SP']["default_controller"];
	$action = ( null != $action ) ? $action : $GLOBALS['G_SP']["default_action"];
	// 使用扩展点
	if( $launch = spLaunch("function_url", array('controller'=>$controller, 'action'=>$action, 'args'=>$args, 'anchor'=>$anchor), TRUE ))return $launch;
	if( TRUE == $GLOBALS['G_SP']['url']["url_path_info"] ){ // 使用path_info方式
		$url = $GLOBALS['G_SP']['url']["url_path_base"]."/{$controller}/{$action}";
		if(null != $args)foreach($args as $key => $arg) $url .= "/{$key}/{$arg}";
	}else{
		$url = $GLOBALS['G_SP']['url']["url_path_base"]."?". $GLOBALS['G_SP']["url_controller"]. "={$controller}&";
		$url .= $GLOBALS['G_SP']["url_action"]. "={$action}";
		if(null != $args)foreach($args as $key => $arg) $url .= "&{$key}={$arg}";
	}
	if(null != $anchor) $url .= "#".$anchor;
	return $url;
}

/** gMkdirs
 *
 * 循环建立目录的辅助函数
 *
 * @param dir    目录路径
 * @param mode    文件权限
 */
function gMkdirs($dir, $mode = 0777){
	if (!is_dir($dir)) {
		gMkdirs(dirname($dir), $mode);
		return @mkdir($dir, $mode);
	}
	return true;
}

/** gConfigReady   快速将用户配置覆盖到框架默认配置
 *
 * @param preconfig    默认配置
 * @param useconfig    用户配置
 */
function gConfigReady( $preconfig, $useconfig = null){
	$nowconfig = $preconfig;
	if (is_array($useconfig)){
		foreach ($useconfig as $key => $val){
			if (is_array($useconfig[$key])){
				@$nowconfig[$key] = is_array($nowconfig[$key]) ? gConfigReady($nowconfig[$key], $useconfig[$key]) : $useconfig[$key];
			}else{
				@$nowconfig[$key] = $val;
			}
		}
	}
	return $nowconfig;
}

/**
*gParseURL 解析访问URL
*
*@param url  当前访问的URL
*/
if (!function_exists('gParseURL')) {
	function gParseURL($url){
		GLOBAL $__controller, $__action, $__args;
		$GLOBALS['G']["url_controller"]=$GLOBALS['G']["url_action"]=$GLOBALS['G']["url_args"]=null;
		if(strlen($url)>1){
					if(strpos($url, '?') === FALSE){
						$url_args = explode("/", $url);
					}else{
						$request_uri_info = explode('?', $url);
						$url_args = explode("/",$request_uri_info[0]);
					}
			for($u = 1; $u < count($url_args); $u++){
						if (empty($url_args[$u])) continue;
				if($u == 1)$GLOBALS['G']["url_controller"] = $url_args[$u];
				elseif($u == 2)$GLOBALS['G']["url_action"] = $url_args[$u];
				else {$GLOBALS['G']["url_args"][$u-3] = $url_args[$u];}
			}
		}
		// 构造执行路由
		$__controller = isset($GLOBALS['G']["url_controller"]) ? $GLOBALS['G']["url_controller"] : $GLOBALS['G']["default_controller"];
		$__action = isset($GLOBALS['G']["url_action"]) ? $GLOBALS['G']["url_action"] : $GLOBALS['G']["default_action"];
	}
}

/**
 * 取客户端IP地址的字符串形式
 * 
 * @return  string  $ip IP地址
 */
function gGetIpStr() {
	$ip = '';
	if (getenv ( 'HTTP_CLIENT_IP' )) {
		$ip = getenv ( 'HTTP_CLIENT_IP' );
	} elseif (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
		list ( $ip ) = explode ( ',', getenv ( 'HTTP_X_FORWARDED_FOR' ) );
	} elseif (getenv ( 'REMOTE_ADDR' )) {
		$ip = getenv ( 'REMOTE_ADDR' );
	} else {
		$ip = $_SERVER ['REMOTE_ADDR'];
	}
	return $ip;
}
/**
 * 取客户端IP地址的整数形式
 * @return type 
 */
function gGetIpInt () {
    return sprintf("%u",ip2long(gGetIpStr()));
}