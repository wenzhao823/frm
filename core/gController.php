<?php
class gController {

	/**
	 * 视图对象
	 */
	public $v;

	/**
	 * 赋值到模板的变量
	 */
	private $__template_vals = array();

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		if(TRUE == $GLOBALS['G']['view']['enabled']){
			$this->v = gClass('gView');
		}
	}

    /**
     *
     * 跳转程序
     *
     * 应用程序的控制器类可以覆盖该函数以使用自定义的跳转程序
     *
     * @param $url  需要前往的地址
     * @param $delay   延迟时间
     */
    public function jump($url, $delay = 0){
		echo "<html><head><meta http-equiv='refresh' content='{$delay};url={$url}'></head><body></body></html>";
		exit;
    }

    /**
     *
     * 错误提示程序
     *
     * 应用程序的控制器类可以覆盖该函数以使用自定义的错误提示
     *
     * @param $msg   错误提示需要的相关信息
     * @param $url   跳转地址 或 跳转地址集合，例如：
     *  array (
     *      array ('key'=>'内网', 'url'=>'http://home.qihoo.net'),
     *      array ('key'=>'外网', 'url'=>'http://www.360.cn'),
     *  )
     */
    public function error($msg, $url = ''){
        $type = 'error';
        include_once  G_MSG . '/alert.html';
        exit;
    }

    /**
     *
     * 成功提示程序
     *
     * 应用程序的控制器类可以覆盖该函数以使用自定义的成功提示
	 *
     * @param $msg   成功提示需要的相关信息
     * @param $url   跳转地址
     */
    public function success($msg, $url = ''){
        $type = 'success';
        include_once  G_MSG . '/alert.html';
        exit;
    }

	/**
	 * 魔术函数，获取赋值作为模板内变量
	 */
	public function __set($name, $value)
	{
		if(TRUE == $GLOBALS['G']['view']['enabled'] && false !== $value){
			$this->v->engine->assign(array($name=>$value));
		}
		$this->__template_vals[$name] = $value;
	}


	/**
	 * 魔术函数，返回已赋值的变量值
	 */
	public function __get($name)
	{
		return $this->__template_vals[$name];
	}

	/**
	 * 输出模板
	 *
     * @param $tplname   模板路径及名称
     * @param $output   是否直接显示模板，设置成FALSE将返回HTML而不输出
	 */
	public function display($tplname, $output = TRUE)
	{
		@ob_start();
		if(TRUE == $GLOBALS['G']['view']['enabled']){
			$this->v->display($tplname);
		}else{
			extract($this->__template_vals);
			require($tplname);
		}
		if( TRUE != $output )return ob_get_clean();
	}

	/**
	 * 自动输出页面
	 * @param tplname 模板文件路径
	 */
	public function auto_display($tplname)
	{
		if( TRUE != $this->v->displayed && FALSE != $GLOBALS['G']['view']['auto_display']){
			if( method_exists($this->v->engine, 'templateExists') && TRUE == $this->v->engine->templateExists($tplname))$this->display($tplname);
		}
	}

	/**
	 * 魔术函数，实现对控制器扩展类的自动加载
	 */
	public function __call($name, $args)
	{
		if(in_array($name, $GLOBALS['G']["auto_load_controller"])){
			return gClass($name)->__input($args);
		}elseif(!method_exists( $this, $name )){
			gError("方法 {$name}未定义！<br />请检查是否控制器类(".get_class($this).")与数据模型类重名？");
		}
	}

	/**
	 * 获取模板引擎实例
	 */
	public function getView()
	{
		$this->v->addfuncs();
		return $this->v->engine;
	}
	/**
	 * 设置当前用户的语言
     * @param $lang   语言标识
	 */
	public function setLang($lang)
	{
		if( array_key_exists($lang, $GLOBALS['G']["lang"]) ){
			@ob_start();
			$domain = ('www.' == substr($_SERVER["HTTP_HOST"],0,4)) ? substr($_SERVER["HTTP_HOST"],4) : $_SERVER["HTTP_HOST"];
			setcookie($GLOBALS['G']['g_app_id']."_gLangCookies", $lang, time()+31536000, '/', $domain ); // 一年过期
			$_SESSION[$GLOBALS['G']['g_app_id']."_gLangSession"] = $lang;
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * 获取当前用户的语言
	 */
	public function getLang()
	{
		if( !isset($_COOKIE[$GLOBALS['G']['g_app_id']."_gLangCookies"]) )return $_SESSION[$GLOBALS['G']['g_app_id']."_gLangSession"];
		return $_COOKIE[$GLOBALS['G']['g_app_id']."_gLangCookies"];
	}
}
