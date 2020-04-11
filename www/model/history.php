<?php
//モデルパス設定
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';


//購入履歴取得する関数
function get_purchase_histories ($db, $user_id) {
  $sql = "
  SELECT
    users.user_id,
    orders.order_number,
    orders.total,
    orders.created
  FROM
    users
  JOIN
    orders
  ON
    users.user_id = orders.user_id
  WHERE
    users.user_id = ?
  ORDER BY
    order_number DESC
  ";
  return fetch_all_query($db, $sql, [$user_id]);
}

//全ての履歴入手
function get_purchase_histories_all ($db) {
  $sql = "
  SELECT
    users.user_id,
    orders.order_number,
    orders.total,
    orders.created
  FROM
    users
  JOIN
    orders
  ON
    users.user_id = orders.user_id
  ORDER BY
    order_number DESC
  ";
  return fetch_all_query($db, $sql);
}