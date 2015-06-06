<?php
/**
 * 由一个带fid的数组生成一个带children的树形数组
 * 专为EasyUI的Tree的json格式设计
 * @author ljb
 *
 */
class BuildTreeArray
{
	private $idKey = 'id'; //主键的键名
	private $fidKey = 'fid'; //父ID的键名
	private $root = 0; //最顶层fid
	private $data = array(); //源数据
	private $treeArray = array(); //属性数组
	
	function __construct($data,$idKey,$fidKey,$root) {
		if($idKey) $this->idKey = $idKey;
		if($fidKey) $this->fidKey = $fidKey;
		if($root) $this->root = $root;
		if($data) {
			$this->data = $data;
			$this->getChildren($this->root);
		}
	}
	
	/**
	 * 获得一个带children的树形数组
	 * @return multitype:
	 */
	public function getTreeArray()
	{
		//去掉键名
		return array_values($this->treeArray);
	}
	
	/**
	 * @param int $root 父id值
	 * @return null or array
	 */
	private function getChildren($root)
	{
      $children = array();
		foreach ($this->data as &$node){       
			if($root == $node[$this->fidKey]){
				$node['children'] = $this->getChildren($node[$this->idKey]);
            //$node['children'] = getChildren(1);
				array_push($children,$node);
			}
			//只要一级节点
			if($this->root == $node[$this->fidKey]){
				$this->treeArray[$node[$this->idKey]] = $node;
			}         
		}
		return $children;
	}
}
?>
<?php

   define("FILE_NAME", "./DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   
   if (file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;
      return;
   }
   
   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                         //illegal char
   define("STR_LENGTH", 50);
   define("SEARCH_SIZE", 1000);                             //上限1000筆
   define("PAGE_SIZE", 100);

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);      

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   
   //link    
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }   
   
   //----- query -----
   $str_query1 = "
      select CategoryId as id, ParentId as fid, CategoryName as text, FilePath from categories where status > 0";
   if($rs = mysqli_query($link, $str_query1)){
      $data = array();
      while($row = mysqli_fetch_assoc($rs)){      
         $tmpnode = array();
         $tmpnode['id'] = intval($row['id']);
         $tmpnode['fid'] = intval($row['fid']);
         $tmpnode['text'] = $row['text'];
         $tmpnode['filepath'] = $row['FilePath']; 
         //$node['state'] = has_child($link,$row['id']) ? 'closed' : 'open';
         array_push($data,$tmpnode);
      }
      mysqli_close($link);
      
      //$data = doSql('SELECT nodeID id,fid,nodeName text FROM mytable');
		$bta = new BuildTreeArray($data,'id','fid',0);
		$data = $bta->getTreeArray();
		echo json_encode($data);      
      
      return;
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }
?>
