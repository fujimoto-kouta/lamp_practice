<?php
//define(定数名, 値)

//modelとviewのそれぞれのパス設定
define('MODEL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../model/');
define('VIEW_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../view/');


//イメージ、スタイルシート、イメージディレクトリのそれぞれのパス設定
define('IMAGE_PATH', '/assets/images/');
define('STYLESHEET_PATH', '/assets/css/');
define('IMAGE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/' );

//データベース情報の定数
define('DB_HOST', 'mysql');
define('DB_NAME', 'sample');
define('DB_USER', 'testuser');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8');

//それぞれのページへのパスを設定
define('SIGNUP_URL', '/signup.php');
define('LOGIN_URL', '/login.php');
define('LOGOUT_URL', '/logout.php');
define('HOME_URL', '/index.php');
define('CART_URL', '/cart.php');
define('FINISH_URL', '/finish.php');
define('ADMIN_URL', '/admin.php');
define('HISTORY_URL', '/history.php');

//英数字の組み合わせの正規表現
define('REGEXP_ALPHANUMERIC', '/\A[0-9a-zA-Z]+\z/');
//正の整数かを調べる正規表現
define('REGEXP_POSITIVE_INTEGER', '/\A([1-9][0-9]*|0)\z/');

//ユーザ名の最小文字数
define('USER_NAME_LENGTH_MIN', 6);
//,ユーザ名の最大文字数
define('USER_NAME_LENGTH_MAX', 100);
//ユーザパスワードの最小文字数
define('USER_PASSWORD_LENGTH_MIN', 6);
//ユーザパスワードの最大文字数
define('USER_PASSWORD_LENGTH_MAX', 100);

//ユーザタイプの定数（１が管理者で、２がそれ以外のユーザ）
define('USER_TYPE_ADMIN', 1);
define('USER_TYPE_NORMAL', 2);

//アイテム名の最小文字数
define('ITEM_NAME_LENGTH_MIN', 1);
//アイテム名の最大文字数
define('ITEM_NAME_LENGTH_MAX', 100);

//アイテムの公開と非公開
define('ITEM_STATUS_OPEN', 1);
define('ITEM_STATUS_CLOSE', 0);

//登録を許可されているステータス
define('PERMITTED_ITEM_STATUSES', array(
  'open' => 1,
  'close' => 0,
));

//登録を許可されている画像のタイプ（拡張子）
define('PERMITTED_IMAGE_TYPES', array(
  IMAGETYPE_JPEG => 'jpg',
  IMAGETYPE_PNG => 'png',
));