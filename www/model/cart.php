<?php
//モデルパス設定
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';

//カートの中身全てを取得する関数
function get_user_carts($db, $user_id)
{
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = ?
  ";
  return fetch_all_query($db, $sql, [$user_id]);
}

//ユーザのカート内の指定したアイテムを取得する関数
function get_user_cart($db, $user_id, $item_id)
{
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = ?
    AND
      items.item_id = ?
  ";

  return fetch_query($db, $sql, [$user_id, $item_id]);
}

//カートに追加する関数
function add_cart($db, $user_id, $item_id)
{
  //$cart変数に格納
  $cart = get_user_cart($db, $user_id, $item_id);
  //カートにアイテムがない場合の処理（新規としてインサートをする）
  if ($cart === false) {
    return insert_cart($db, $user_id, $item_id);
  }
  //カートにアイテムがあった場合の処理（総数のアップデート）
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}

//カートに新規のアイテムをインサートする関数
//戻り値で、execute_query()を返す
function insert_cart($db, $user_id, $item_id, $amount = 1)
{
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES(?, ?, ?)
  ";

  return execute_query($db, $sql, [$item_id, $user_id, $amount]);
}

//カートの商品の総数の変更する関数
function update_cart_amount($db, $cart_id, $amount)
{
  $sql = "
    UPDATE
      carts
    SET
      amount = ?
    WHERE
      cart_id = ?
    LIMIT 1
  ";
  return execute_query($db, $sql, [$amount, $cart_id]);
}

//カートの中身を削除する関数
function delete_cart($db, $cart_id)
{
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = ?
    LIMIT 1
  ";

  return execute_query($db, $sql, [$cart_id]);
}

//カートの中身を購入する一連の流れの関数
function purchase_carts($db, $carts)
{
  //トランザクション処理
  $db->beginTransaction();

  if (validate_cart_purchase($carts) === false) {
    return false;
  }
  foreach ($carts as $cart) {
    if (update_item_stock(
      $db,
      $cart['item_id'],
      $cart['stock'] - $cart['amount']
    ) === false) {
      set_error($cart['name'] . 'の購入に失敗しました。');
    }
  }
  //ユーザのカートを消去する処理
  delete_user_carts($db, $carts[0]['user_id']);
  //購入履歴、購入詳細へインサートする処理
  create_histories($db, $carts);
  //エラーがあれば、ロールバック
  if (has_error()) {
    $db->rollBack();
    return false;
  } else {
    //エラーがなければコミット
    $db->commit();
    return true;
  }

}

//$user_idを基に、カートの中身を削除する関数
function delete_user_carts($db, $user_id)
{
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = ?
  ";

  execute_query($db, $sql, [$user_id]);
}

//カートの中身の総額を取得する関数
function sum_carts($carts)
{
  //$total_price変数を設定
  $total_price = 0;
  foreach ($carts as $cart) {
    //カートの配列を展開し、総額に足していく
    $total_price += $cart['price'] * $cart['amount'];
  }
  return $total_price;
}

//カートの中身を検証する関数
function validate_cart_purchase($carts)
{
  //カートの中身が０個だった場合の処理
  if (count($carts) === 0) {
    set_error('カートに商品が入っていません。');
    return false;
  }
  foreach ($carts as $cart) {
    if (is_open($cart) === false) {
      set_error($cart['name'] . 'は現在購入できません。');
    }
    //購入総数がストック総数を上回っていた時の処理
    if ($cart['stock'] - $cart['amount'] < 0) {
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
    //購入総数が０個だった場合の処理
    if ($cart['amount'] === 0) {
      set_error(($cart['name']) . 'の購入数を０個以上にしてください。購入可能数' . $cart['stock']);
    }
  }
  if (has_error() === true) {
    return false;
  }
  return true;
}

//ordersテーブルにインサートする処理
function insert_order($db, $user_id, $total_price)
{
  $sql = "
    INSERT INTO
      orders(
        user_id,
        total
      )
    VALUES(?, ?)
  ";

  return execute_query($db, $sql, [$user_id, $total_price]);
}


//order_number取得
function get_order_number($db, $user_id)
{
  $sql = "
    SELECT
      order_number
    FROM
      orders
    WHERE
      user_id = ?
    ORDER BY order_number DESC
    LIMIT 1;
  ";
  return fetch_query($db, $sql, [$user_id]);
}


//orderdetailsテーブルにインサートする処理
function insert_orderdetails($db, $order_number, $item_id, $amount, $purchase_price)
{
  $sql = "
  INSERT INTO
  orderdetails(
    order_number,
    item_id,
    amount,
    purchase_price
    )
  VALUES(?, ?, ?, ?)
  ";

  return execute_query($db, $sql, [$order_number, $item_id, $amount, $purchase_price]);
}

//orderテーブルにインサート、$order_number取得、orderdetailsテーブルにインサート
//する処理を一つにまとめた関数
function create_histories($db, $carts)
{
  $total_price =  sum_carts($carts);
  // //orderテーブルにインサートする処理
  $result = insert_order($db, $carts[0]['user_id'], $total_price);
  if ($result !== true) {
    return false;
  }

  // //$order_numberを取得する処理
  $order_number = get_order_number($db, $carts[0]['user_id']);

  // //orderdetailsテーブルにインサートする処理
  foreach ($carts as $cart) {
    $result = insert_orderdetails($db, $order_number['order_number'], $cart['item_id'], $cart['amount'], $cart['price']);
    if ($result !== true) {
      return false;
    }
  }
  return true;
}
