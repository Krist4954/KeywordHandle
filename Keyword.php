<?php
include './keyview.html';

if(!empty($_FILES['file']['name'])){
	$PrefixName = $_POST['PrefixName'];
	$leftText = $_POST['leftText'];
	$rightText = $_POST['rightText'];
	$file = $_FILES['file'];
	//var_dump($file);
	if($file['size'] < 1000000 && $file['size'] > 0){
		move_uploaded_file($file['tmp_name'],$file['name']);
		//echo "上传成功</br>";
		$f_arr = file($file['name']);
		if(count($f_arr) > 0){
			$preg = $_POST['rightText'];
			if(file_exists('url.txt')){
				unlink('url.txt');
			}
			$fopen = fopen('url.txt','a');
			foreach($f_arr as $text){
				preg_match_all($preg,$text,$str1);
				if(@$str1[1][0] <> ""){
					$text2 = @$PrefixName . $str1[1][0] . $leftText;
					echo $text2 . "</br>";
					fwrite($fopen,$text2 . "\r\n");
				} 
			}
			fclose($fopen);
		}
		
	}else{
		echo "上传文件太大或未知错误";
	}
}





