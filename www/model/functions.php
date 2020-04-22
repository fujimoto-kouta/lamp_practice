<?php

//var_dumpの関数
function dd($var){
  var_dump($var);
  exit();
}

//リダイレクトの関数
function redirect_to($url){
  header('Location: ' . $url);
  exit;
}

//GETで送られてきた$nameを呼び出し
function get_get($name){
  if(isset($_GET[$name]) === true){
    return $_GET[$name];
  };
  return '';
}

//POSTで送られてきた$nameを呼び出し
function get_post($name){
  if(isset($_POST[$name]) === true){
    return $_POST[$name];
  };
  return '';
}

//アップロードされる情報呼び出し
function get_file($name){
  if(isset($_FILES[$name]) === true){
    return $_FILES[$name];
  };
  return array();
}

//セッションに保存されている$name呼び出し
function get_session($name){
  if(isset($_SESSION[$name]) === true){
    return $_SESSION[$name];
  };
  return '';
}

//セッションをセットする関数
function set_session($name, $value){
  $_SESSION[$name] = $value;
}

//エラー文をいれるセッションに配列として格納
function set_error($error){
  $_SESSION['__errors'][] = $error;
}

function get_errors(){
  $errors = get_session('__errors');
  if($errors === ''){
    return array();
  }
  set_session('__errors',  array());
  return $errors;
}

//エラーがあるかどうかをチェックする関数
function has_error(){
  return isset($_SESSION['__errors']) && count($_SESSION['__errors']) !== 0;
}

//メッセージをセッションにセットする関数
function set_message($message){
  $_SESSION['__messages'][] = $message;
}

function get_messages(){
  $messages = get_session('__messages');
  if($messages === ''){
    return array();
  }
  set_session('__messages',  array());
  return $messages;
}

//セッションの中のユーザID があるかどうかのチェック
function is_logined(){
  return get_session('user_id') !== '';
}

//ファイルネームを作成するための関数
function get_upload_filename($file){
  if(is_valid_upload_image($file) === false){
    return '';
  }
  $mimetype = exif_imagetype($file['tmp_name']);
  $ext = PERMITTED_IMAGE_TYPES[$mimetype];
  //生成したランダムな数字と拡張子を合わせてファイルネーム作成
  return get_random_string() . '.' . $ext;
}

//uniqid()でランダムな１３字の数字を生成。hash関数のsha256でuniqid()の数字をハッシュ化。
//hashで生成した数字をbase_convertで変換（１６進数を３６進数に変換）
//substr(対象の文字列, 切り出す開始位置。０の場合は１文字目から, ２０文字まで切り出し)
function get_random_string($length = 20){
  return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
}

//一時ファイルにあるファイルを、保存ディレクトリに移動するための関数
function save_image($image, $filename){
  return move_uploaded_file($image['tmp_name'], IMAGE_DIR . $filename);
}

//イメージファイルを削除する関数
function delete_image($filename){
  //指定したファイルやディレクトリが存在するかどうかを調べる
  if(file_exists(IMAGE_DIR . $filename) === true){
    //指定したファイルを削除する
    unlink(IMAGE_DIR . $filename);
    //削除成功したらtrueを返す
    return true;
  }
  return false;
  
}


//対象の文字の長さが、最小文字数より大きく、最大文字数より小さいかを検証する関数
function is_valid_length($string, $minimum_length, $maximum_length = PHP_INT_MAX){
  $length = mb_strlen($string);
  return ($minimum_length <= $length) && ($length <= $maximum_length);
}

//英数字の組み合わせかどうかを検証するための関数
function is_alphanumeric($string){
  return is_valid_format($string, REGEXP_ALPHANUMERIC);
}

//正の整数かどうかを検証するための関数
function is_positive_integer($string){
  return is_valid_format($string, REGEXP_POSITIVE_INTEGER);
}

//preg_matchで検証するための関数作成
function is_valid_format($string, $format){
  return preg_match($format, $string) === 1;
}


//アップロードできる画像かどうかを検証する関数
function is_valid_upload_image($image){
  if(is_uploaded_file($image['tmp_name']) === false){
    set_error('ファイル形式が不正です。');
    return false;
  }
  $mimetype = exif_imagetype($image['tmp_name']);
  if( isset(PERMITTED_IMAGE_TYPES[$mimetype]) === false ){
    set_error('ファイル形式は' . implode('、', PERMITTED_IMAGE_TYPES) . 'のみ利用可能です。');
    return false;
  }
  return true;
}

//エスケープ関数
function h($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// トークンの生成
function get_csrf_token(){
  // get_random_string()はユーザー定義関数。
  $token = get_random_string(30);
  // set_session()はユーザー定義関数。
  set_session('csrf_token', $token);
  return $token;
}

// トークンのチェック
function is_valid_csrf_token($token){
  if($token === '') {
    return false;
  }
  // get_session()はユーザー定義関数
  return $token === get_session('csrf_token');
}