<?php
/**
 * gArgs
 * 应用程序变量类
 * gArgs是封装了$_SESSION、$_GET/$_POST、$_COOKIE、$_SERVER、$_FILES、$_ENV等，提供一些简便的访问和使用这些
 * 全局变量的方法。
 */

class gArgs {
	/**
	 * 在内存中保存的变量
	 */
	public $args = null;

	/**
	 * 构造函数
	 *
	 */
	public function __construct(){
		$this->args = $_REQUEST;
	}

	/**
	 * 获取应用程序请求变量值，同时也可以指定获取的变量所属。
	 *
	 * @param name    获取的变量名称，如果为空，则返回全部的请求变量
	 * @param default    当前获取的变量不存在的时候，将返回的默认值
	 * @param method    获取位置，取值GET，POST，COOKIE
	 */
	public function get($name = null, $default = FALSE, $method = null)
	{
		if(null != $name){
			if( $this->has($name) ){
				if( null != $method ){
					switch (strtolower($method)) {
						case 'get':
							return $_GET[$name];
						case 'post':
							return $_POST[$name];
						case 'cookie':
							return $_COOKIE[$name];
					}
				}
				return $this->args[$name];
			}else{
				return (FALSE === $default) ? FALSE : $default;
			}
		}else{
			return $this->args;
		}
	}

	/**
	 * 设置（增加）环境变量值，该名称将覆盖原来的环境变量名称
	 *
	 * @param name    环境变量名称
	 * @param value    环境变量值
	 */
	public function set($name, $value)
	{
		$this->args[$name] = $value;
	}

	/**
	 * 检测是否存在某值
	 *
	 * @param name    待检测的环境变量名称
	 */
	public function has($name)
	{
		return isset($this->args[$name]);
	}

	/**
	 * 构造输入函数，标准用法
	 * @param args    环境变量名称的参数
	 */
	public function __input($args = -1)
	{
		if( -1 == $args )return $this;
		@list( $name, $default, $method ) = $args;
		return $this->get($name, $default, $method);
	}

	/**
	 * 获取请求字符
	 */
	public function request(){
		return $_SERVER["QUERY_STRING"];
	}
}