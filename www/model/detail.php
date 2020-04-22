<?php
//モデルパス設定
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';


$order_number = $_POST['order_number'];
$created = $_POST['created'];
$total = $_POST['total'];


//購入明細取得する関数
function get_purchase_detail ($db, $order_number, $user) {
  $sql = "
  SELECT
    items.name,
    orderdetails.purchase_price,
    orderdetails.amount,
    orders.total
  FROM
    items
  JOIN
    orderdetails
  ON
    items.item_id = orderdetails.item_id
  JOIN
    orders
  ON
    orderdetails.order_number = orders.order_number
  WHERE
    orderdetails.order_number = ?
  ";
  $params = [$order_number];
  if($user['type'] === USER_TYPE_NORMAL) {
    $sql .= ' AND exists(SELECT * FROM orders WHERE order_number = ? AND user_id = ?)';
    array_push($params, $order_number, $user['user_id']);
  }
  return fetch_all_query($db, $sql, $params);
}
