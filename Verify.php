<?php

	//��֤����
	class Verify
	{
		//��֤�����
		protected $number;
		//��֤������
		protected $codeType;
		//��
		protected $width;
		//��
		protected $height;
		//ͼƬ����
		protected $imageType;
		//��֤��
		protected $code;
		//ͼƬ��Դ
		protected $image;

		//��ʼ����Ա����
		function __construct($number = 4, $codeType = 0,$width = 100, $height = 30, $imageType = 'png')
		{
			$this->number = $number;
			$this->codeType = $codeType;
			$this->width = $width;
			$this->height = $height;
			$this->imageType = $imageType;

			//������֤��ĺ���
			$this->code = $this->getCode();
		}

		//������֤���ַ����ĺ���
		protected function getCode()
		{
			switch ($this->codeType) {
				//������
				case 0:
					$code = $this->getNumberCode();
					break;
				//��ĸ
				case 1:
					$code = $this->getCharCode();
					break;
				//���
				case 2:
					$code = $this->getNumCharCode();
					break;
				default :
					exit('��֧�ֵ�����');
			}
			return $code;
		}

		//����
		protected function getNumberCode()
		{
			$str = join('', range(0, 9));
			return substr(str_shuffle($str), 0, $this->number);
		}

		//��ĸ
		protected function getCharCode()
		{
			$arr = range('a', 'z');
			$str = join('', $arr);
			$str .= strtoupper($str);
			return substr(str_shuffle($str), 0, $this->number);
		}

		//���
		protected function getNumCharCode()
		{
			$str = '';
			for ($i=0; $i < $this->number; $i++) {
				$t = mt_rand(0,2);
				switch ($t) {
					case 0:
						$str .= chr(mt_rand(48, 57));
						break;
					case 1:
						$str .= chr(mt_rand(65, 90));
						break;
					case 2:
						$str .= chr(mt_rand(97, 122));
						break;

				}
			}
			return $str;
		}

		//������Դ
		public function outImage()
		{
			//���ɻ���
			$this->image = $this->createImage();
			//��䱳����ɫ
			$this->fillBackground();
			//д��֤��
			$this->drawCode();
			//������Ԫ��
			$this->drawDisturb();
			//����������
			$this->show();
		}

		//��������
		protected function createImage()
		{
			return imagecreatetruecolor($this->width, $this->height);
		}

		//��䱳��ɫ
		protected function fillBackground()
		{
			imagefill($this->image,0, 0, $this->lightColor());
		}

		//����ǳɫϵ
		protected function lightColor()
		{
			return imagecolorallocate($this->image, mt_rand(130,255), mt_rand(130,255), mt_rand(130,255));
		}

		//������ɫϵ
		protected function darkColor()
		{
			return imagecolorallocate($this->image, mt_rand(0,120), mt_rand(0,120), mt_rand(0,120));
		}

		//д�ַ���
		protected function drawCode()
		{
			for ($i=0; $i < $this->number; $i++) {
				$c = $this->code[$i];
				$width = ceil($this->width / $this->number);
				$x = mt_rand($i * $width + 10, ($i + 1) * $width - 15);
				$y = mt_rand(0, $this->height - 15);
				imagechar($this->image , 5, $x, $y, $c, $this->darkColor());
			}
		}

		//д����Ԫ��
		protected function drawDisturb()
		{
			for ($i=0; $i < $this->width * $this->height / 20; $i++) {
				$x = mt_rand(0, $this->width);
				$y = mt_rand(0, $this->height);
				imagesetpixel($this->image, $x, $y, $this->darkColor());
			}
		}

		//����������
		protected function show()
		{
			header('Content-Type:image/'. $this->imageType);
			$func = 'image' . $this->imageType;
			$func($this->image);
		}
	}