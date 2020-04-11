<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'cart.php';
require_once MODEL_PATH . 'history.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

$db = get_db_connect();
$user = get_login_user($db);

if($user['type'] === USER_TYPE_NORMAL) {
$histories = get_purchase_histories($db, $user['user_id']);
} else if($user['type'] === USER_TYPE_ADMIN) {
  $histories = get_purchase_histories_all($db);
}
include_once VIEW_PATH . 'history_view.php';