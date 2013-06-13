<?php
/**
 * gMysql MySQL数据库的驱动支持
 */
class gMysql {
	/**
	 * 数据库链接句柄
	 */
	public $conn;// 当前使用的连接
	/**
	 * 执行的SQL语句记录
	 */
	public $arrSql;

	// 当前连接ID
	public $m_link_id = null; // 主库连接
	public $s_link_id = null; // 从库连接
        
	// 是否多库
	public $multi_server = false;
        
	// 数据库连接参数配置
	public $dbConfig = array ();
    public $dbCurrent = array ();


        /**
	 * 按SQL语句获取记录结果，返回数组
	 *
	 * @param sql  执行的SQL语句
	 */
	public function getArray($sql)
	{
		if( ! $result = $this->query($sql) )return FALSE;
		if( ! mysql_num_rows($result) )return FALSE;
		$rows = array();
		while($rows[] = mysql_fetch_array($result,MYSQL_ASSOC)){}
		mysql_free_result($result);
		array_pop($rows);
		return $rows;
	}

	/**
	 * 返回当前插入记录的主键ID
	 */
	public function newinsertid()
	{
		return mysql_insert_id($this->conn);
	}

	/**
	 * 格式化带limit的SQL语句
	 */
	public function setlimit($sql, $limit)
	{
		return $sql. " LIMIT {$limit}";
	}

	/**
	 * 执行一个SQL语句
	 *
	 * @param sql 需要执行的SQL语句
	 */
	public function exec($sql)
	{
		$this->arrSql[] = $sql;
        $this->initConnect ( true );
		
		return mysql_query($sql, $this->conn);
		if( $result = mysql_query($sql, $this->conn) ){
			return $result;
		}else{
			gError("{$sql}<br />执行错误: " . mysql_error());
		}
	}
        /**
         * 执行一个SQL语句，主要用于查询
         * @param type $sql
         * @param type $master default:false 为true:强制读主库；为false：在有从库的的情况下优先读从库，否则读主库
         */
        public function query ($sql, $master = false) {
            $this->arrSql[] = $sql;
            $this->initConnect ( $master );
            if( $result = mysql_query($sql, $this->conn) ){
		return $result;
            }else{
                 gError("{$sql}<br />执行错误: " . mysql_error());
            }
        }

	/**
	 * 返回影响行数
	 */
	public function affected_rows()
	{
		return mysql_affected_rows($this->conn);
	}

	/**
	 * 获取数据表结构
	 *
	 * @param tbl_name  表名称
	 */
	public function getTable($tbl_name)
	{
		return $this->getArray("DESCRIBE {$tbl_name}");
	}

	/**
	 * 构造函数
	 *
	 * @param dbConfig  数据库配置
	 */
	public function __construct($dbConfig)
	{
            $this->dbConfig = $dbConfig;
            $this->multi_server = empty ( $this->dbConfig ['slave'] ) ? false : true;
	}
	/**
	 * 连接数据库方法
	 * @param type $dbConfig 
	 */
	public function connect ($db) {
		$this->dbCurrent = $db;
		$linkfunction = ( TRUE == $db['persistent'] ) ? 'mysql_pconnect' : 'mysql_connect';
		$this->conn = $linkfunction ( $db ['host'], $db ['login'], $db ['password']);

		if (! $this->conn) {
				gError("数据库链接错误 : " . mysql_error());
		}
		mysql_select_db($db['database'], $this->conn) || gError("无法使用数据库 : " . mysql_error());
		mysql_query ( "SET NAMES '" . $db ['charset'] . "'", $this->conn );            
	}
	
	/**
	 * 初始化数据库连接
	 * @param type $master 
	 */
	public function initConnect ($master = true) {
		if ($master || !$this->multi_server) {
				if($this->m_link_id){
						$this->conn = $this->m_link_id;
				} else {
						$this->connect ( $this->dbConfig ['master'] );
						$this->m_link_id = $this->conn;
				}
		} else {
				if($this->s_link_id){
						$this->conn = $this->s_link_id;
				} else {
						$rand = array_rand($this->dbConfig ['slave']);
						$this->connect ( $this->dbConfig ['slave'][$rand] );
						$this->s_link_id = $this->conn;
				}
		}
        }
        
	/**
	 * 对特殊字符进行过滤
	 *
	 * @param value  值
	 */
	public function __val_escape($value) {
		if(is_null($value))return 'NULL';
		if(is_bool($value))return $value ? 1 : 0;
		if(is_int($value))return (int)$value;
		if(is_float($value))return (float)$value;
		if(@get_magic_quotes_gpc())$value = stripslashes($value);
                $this->conn || $this->initConnect();
		return '\''.mysql_real_escape_string($value, $this->conn).'\'';
	}

	/**
	 * 析构函数
	 */
	public function __destruct()
	{
            if( TRUE != @$this->dbCurrent['persistent'] )@mysql_close($this->conn);
	}
}

