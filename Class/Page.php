<?php

class Page
{
	//每页显示的个数
	protected $number;
	//一共多少数据
	protected $totalCount;
	//一共多少页
	protected $totalPage;
	//当前页
	protected $page;
	//URL
	protected $url;
	//初始化成员属性
	function __construct($number = 5, $totalCount = 61)
	{
		$this->number = $number;
		$this->totalCount =  $totalCount;
		//得到总页数
		$this->totalPage = $this->getTotalPage();
		//获得当前页
		$this->page = $this->getPage();
		//得到url
		$this->url = $this->getUrl();
		echo $this->url;

	}
	protected function getTotalPage()
	{
		return ceil($this->totalCount / $this->number);
	}
	protected function getPage()
	{
		if (empty($_GET['page'])) {
			$page = 1;
		} else {
			$page = $_GET['page'];
		}
		return $page;
	}
	protected function getUrl()
	{
		//得到协议  http
		$scheme = $_SERVER['REQUEST_SCHEME'];
		//得到主机
		$host = $_SERVER['SERVER_NAME'];
		//获取端口号
		$port = $_SERVER['SERVER_PORT'];
		//获取文件路径和参数
		$pathData = $_SERVER['REQUEST_URI'];

		//index.php?username=goudan&page=3
		//对url进行操作 有page参数的，把page干掉，自己再后面再拼接
		$data = parse_url($pathData);
		//获取文件路径
		$path = $data['path'];
		//判断有没有query，如果有的话，将后面的page参数干掉
		if (!empty($data['query'])) {
			//将query中的page干掉
			parse_str($data['query'],$arr);
			unset($arr['page']);
			//将其他的参数再次拼接
			$query = http_build_query($arr);
			//将其拼接到path路径的后面
			$path = $path .'?' . $query;
		}
		$path = trim($path, '?');
		//开始拼接成一个完整的额url
		$url = $scheme . '://' . $host . ':' . $port . $path;
		return $url;
	}
	//首页
	function first()
	{
		return $this->setUrl('page=1');
	}
	//上一页
	function prev()
	{
		if ($this->page -1 < 1) {
			$page = 1;
		} else {
			$page = $this->page - 1;
		}
		return $this->setUrl('page=' . $page);
	}
	//下一页
	function next()
	{
		if ($this->page +1 >  $this->totalPage) {
			$page = $this->totalPage;
		} else {
			$page = $this->page + 1;
		}
		return $this->setUrl('page=' . $page);
	}
	//尾页
	function end()
	{
		return $this->setUrl('page=' . $this->totalPage);
	}
	protected function setUrl($str)
	{
		//index.php?username=goudan&
		if (strstr($this->url, '?')) {
			return $this->url . '&' . $str;
		} else {
			return $this->url . '?' . $str;
		}
	}
	function allPage()
	{
		return [
			'first' => $this->first(),
			'prev' => $this->prev(),
			'next' => $this->next(),
			'end' => $this->end(),

		];
	}
}