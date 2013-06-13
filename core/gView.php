<?php
class gView {
	/**
	 * 模板引擎实例
	 */
	public $engine = null;
	/**
	 * 模板是否已输出
	 */
	public $displayed = FALSE;

	/**
	 * 构造函数，进行模板引擎的实例化操作
	 */
	public function __construct()
	{
		if(FALSE == $GLOBALS['G']['view']['enabled'])return FALSE;
		if(FALSE != $GLOBALS['G']['view']['auto_ob_start'])ob_start();
		$this->engine = gClass($GLOBALS['G']['view']['engine_name'],null,$GLOBALS['G']['view']['engine_path']);
		if( $GLOBALS['G']['view']['config'] && is_array($GLOBALS['G']['view']['config']) ){
			$engine_vars = get_class_vars(get_class($this->engine));
			foreach( $GLOBALS['G']['view']['config'] as $key => $value ){
				if( array_key_exists($key,$engine_vars) )$this->engine->{$key} = $value;
			}
		}
		if( !empty($GLOBALS['G']['g_app_id']) && isset($this->engine->compile_id) )$this->engine->compile_id = $GLOBALS['G']['g_app_id'];
		// 检查编译目录是否可写
		if( empty($this->engine->no_compile_dir) && (!is_dir($this->engine->compile_dir) || !is_readable($this->engine->compile_dir)))gError("模板编译目录“".$this->engine->compile_dir."”不可写！");
		//gAddViewFunction('T', array( 'gView', '__template_T'));
		//gAddViewFunction('gUrl', array( 'gView', '__template_gUrl'));
	}

	/**
	 * 输出页面
	 * @param tplname 模板文件路径
	 */
	public function display($tplname)
	{
		try {
				$this->addfuncs();
				$this->displayed = TRUE;
				if($GLOBALS['G']['view']['debugging'] && g_DEBUG)$this->engine->debugging = TRUE;
				$this->engine->display($tplname);
		} catch (Exception $e) {
			gError( $GLOBALS['G']['view']['engine_name']. ' Error: '.$e->getMessage() );
		}
	}

	/**
	 * 注册已挂靠的视图函数
	 */
	public function addfuncs()
	{
		if( is_array($GLOBALS['G']["view_registered_functions"]) ){
			foreach( $GLOBALS['G']["view_registered_functions"] as $alias => $func ){
				if( is_array($func) && !is_object($func[0]) )$func = array(gClass($func[0]),$func[1]);
				$this->engine->registerPlugin("function", $alias, $func);
				unset($GLOBALS['G']["view_registered_functions"][$alias]);
			}
		}
	}
	/**
	 * 辅助gUrl的函数，让gUrl可在模板中使用。
	 * @param params 传入的参数
	 */
	public function __template_gUrl($params)
	{
		$controller = $GLOBALS['G']["default_controller"];
		$action = $GLOBALS['G']["default_action"];
		$args = array();
		$anchor = null;
		foreach($params as $key => $param){
			if( $key == $GLOBALS['G']["url_controller"] ){
				$controller = $param;
			}elseif( $key == $GLOBALS['G']["url_action"] ){
				$action = $param;
			}elseif( $key == 'anchor' ){
				$anchor = $param;
			}else{
				$args[$key] = $param;
			}
		}
		return gUrl($controller, $action, $args, $anchor);
	}
	/**
	 * 辅助T的函数，让T可在模板中使用。
	 * @param params 传入的参数
	 */
	public function __template_T($params)
	{
		return T($params['w']);
	}
}
