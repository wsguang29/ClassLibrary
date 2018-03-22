<?php

	class Upload
	{
		//上传路径
		protected $path = './upload/';
		//文件允许后缀
		protected $allSuffix = ['jpg', 'png', 'wbmp', 'gif', 'jpeg', 'bmp'];
		//文件允许mime类型
		protected $allMime = ['image/png', 'image/gif', 'image/jpeg', 'image/wbmp'];
		//文件允许大小
		protected $allSize = 200000;
		//是否启用随机名
		protected $isRandName = true;
		//是否启用日期目录
		protected $isDatePath = true;
		//错误号
		protected $errNum;
		//错误信息
		protected $errInfo;
		//文件名
		protected $file;
		//文件后缀
		protected $suffix;
		//文件mime类型
		protected $mime;
		//文件大小
		protected $size;
		//临时路径
		protected $tmpName;
		//新文件路径
		protected $newPath;
		//新文件名
		protected $newName;

		//初始化一批成员属性
		public function __construct($arr = [])
		{
			foreach($arr as $key => $value){
				$this->setOption[$key] = $value;
			}
		}

		//处理setOption
		protected function setOption($key,$value)
		{
			$keys = array_keys(get_class_vars(__CLASS__));
			if(in_array($key,$keys)){
				$this->key = $value;
			}
		}

		//上传文件函数
		public function uploadfile($key)
		{
			//判断是否设置路径
			if(empty($this->path)){
				$this->setOption('errNum', '-1');
				return false;
			}

			//判断路径是否存在
			if(!$this->checkDir()){
				$this->setOption('errNum', '-2');
				return false;
			}

			//获取错误号
			$error = $FILES[$key]['error'];
			if($error){
				$this->setOption('errNum', $error);
				return false;
			}else{
				$this->getFileInfo($key);
			}

			//判断大小/mime/后缀
			if((!$this->checkSize()) || (!$this->checkMime) || ($this->checkSuffix())){
				return false;
			}

			//获得新文件名、新文件路径
			$this->newName = $this->createNewName();
			$this->newPath = $this->createNewPath();

			//判断是否上传文件、移动文件
			if(is_uploaded_file($this->tmpName)){
				if(move_uploaded_file($this->tmpName,$this->newPath,$this->newName)){
					$this->setOption('errNum', 0);
					return $this->newPath;
				}else{
					$this->setOption('errNum', '-6');
					return false;
				}
			}
		}

		//创建新文件路径
		protected function createNewPath()
		{
			if($this->isDatePath){
				$path = $this->path . date('y/m/d');
				if(!file_exists($path)){
					mkdir($path,0777,true);
				}
				return $path;
			}else{
				return $this->path;
			}
		}

		//创建新文件名
		protected function createNewName()
		{
			if($this->isRandName){
				$name = uniqid() . '.' . $this->suffix;
			}else{
				$name = $this->file;
			}
			return $name;
		}

		//检查文件大小
		protected function checkSize()
		{
			if(in_array($this->size, $this->allSize)){
				$this->setOption('errNum', '-3');
				return false;
			}
			return true;
		}

		//检查文件mime
		protected function checkMime()
		{
			if(in_array($this->mime, $this->allMime)){
				$this->setOption('errNum', '-4');
				return false;
			}
			return true;
		}

		//检查文件后缀
		protected function checkSuffix()
		{
			if(in_array($this->suffix, $this->allSuffix)){
				$this->setOption('errNum', '-5');
				return false;
			}
			return true;
		}

		//处理将信息保存到属性2
		protected function getFileInfo($key)
		{
			//获取文件名
			$this->file = $FILES[$key]['name'];
			//获取文件mime
			$this->mime = $FILES[$key]['mime'];
			//获取文件临时文件
			$this->tmpName = $FILES[$key]['tmp_name'];
			//获取文件大小
			$this->size = $FILES[$key]['size'];
			//获取文件后缀
			$this->suffix = pathinfo($this->file)['extension'];
		}

		//处理检查函数checkDir
		protected function checkDir()
		{
			//是否文件或者文件夹
			if(!file_exists($this->path) || !is_dir($this->path)){
				return mkdir($this->path,0777,true);
			}
			//是否可写
			if(!is_writable($this->path)){
				return chmod($this->path,0777);
			}
			return true;
		}

		//写一个get方法，让外部得到错误号和错误信息
		function __get($name)
		{
			if ($name == 'errNum') {
				return $this->errorNumber;
			} else if($name == 'errInfo'){
				return $this->getErrorInfo();
			}
		}
		//错误号对应的错误信息
		protected function getErrorInfo()
		{
			//-1=>文件路径没有设置
			//-2=文件不是目录或者是权限错误
			//-3=》文件尺寸过大
			//-4=》文件的mime的类型不符合
			//-5=》文件的后缀不符合
			//-6-》文件不是上传文件
			switch ($this->errorNumber) {
				case 0:
					$str = '上传成功';
					break;
				case 1:
					$str = '超过了PHP.INI里面的限制';
					break;
				case 2:
					$str = '文件超过了html里面的设置';
					break;
				case 3:
					$str = '部分文件上传';
					break;
				case 4:
					$str = '文件没有被上传';
					break;
				case 6:
					$str = '找不到临时文件夹';
					break;
				case 7:
					$str = '写入失败';
					break;
				case -1:
					$str = '文件路径没有设置';
					break;
				case -2:
					$str = '文件不是目录或者是权限错误';
					break;
				case -3:
					$str = '文件尺寸过大';
					break;
				case -4:
					$str = '文件的mime的类型不符合';
					break;
				case -5:
					$str = '文件的后缀不符合';
					break;
				case -6:
					$str = '文件不是上传文件';
					break;

			}
			return $str;
		}
	}