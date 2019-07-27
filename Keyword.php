<?php
include './keyview.html';

$sqlhost = "localhost";
$sqlname = "root";
$sqlpass = "root";
$dbname = 'test';
$conn = mysqli_connect($sqlhost,$sqlname,$sqlpass,$dbname);

if($conn->connect_error) {
	die('数据库连接失败：' . $conn->connect_error);
}

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
					//echo $text2 . "</br>";
					fwrite($fopen,$text2 . "\r\n");
				} 
			}
			fclose($fopen);
			$f_arr = file('url.txt');
			$f_arr2 = array_unique($f_arr);
			unlink('url.txt');
			//查询历史记录
			$fopen = fopen('url.txt','a');
			foreach($f_arr2 as $check){
				$sql="select * from user where word='{$check}'";
				$sql2="INSERT INTO user (word) VALUES ('{$check}')";
				$result = $conn->query($sql);
				if($result->num_rows == 0){
					//echo $result->num_rows . '</br>';
					$conn->query($sql2);
					echo $check . "</br>";
					fwrite($fopen,$check);
				}
			}
			fclose($fopen);
			$conn->close();
			/*
			$fopen = fopen('url.txt','a');
			foreach($f_arr2 as $text1){
				echo $text1 . "</br>";
				fwrite($fopen,$text1);
			}
			fclose($fopen);
			*/
		}
		
	}else{
		echo "上传文件太大或未知错误";
	}
	
}




