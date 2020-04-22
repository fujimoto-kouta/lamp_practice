<?php
//モデルパス
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';

// DB利用

//商品情報取得関数
function get_item($db, $item_id){
  $sql = "
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
    WHERE
      item_id = ?
  ";

  return fetch_query($db, $sql, [$item_id]);
}

function get_items($db){
  $sql = '
  SELECT
    item_id, 
    name,
    stock,
    price,
    image,
    status
  FROM
    items
';

return fetch_all_query($db, $sql);
}


//新着順の商品取得
function get_items_new($db, $is_open = false){
  $sql = '
  SELECT
    item_id, 
    name,
    stock,
    price,
    image,
    status
  FROM
    items
';
if($is_open === true){
  $sql .= '
    WHERE status = 1
    ORDER BY item_id DESC
  ';

return fetch_all_query($db, $sql);
}
}

//価格の安い順の商品取得
function get_items_cheap($db, $is_open = false){
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';
  if($is_open === true){
    $sql .= '
      WHERE status = 1
      ORDER BY
      price
    ';
  }

  return fetch_all_query($db, $sql);
}

//値段が高い順の商品取得
function get_items_expensive($db, $is_open = false){
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';
  if($is_open === true){
    $sql .= '
      WHERE status = 1
      ORDER BY
      price DESC
    ';
  }

  return fetch_all_query($db, $sql);
}




//全てのアイテム情報を取得する関数
function get_all_items($db){
  return get_items($db);
}

//公開になっているアイテム情報を取得する関数
function get_open_items($db, $sort){
    if($sort === '価格の安い順') {
      return get_items_cheap($db, true);
    } else if($sort === '価格の高い順') {
      return get_items_expensive($db, true);
    } else if($sort === '新着順') {
      return get_items_new($db, true);
    }

  return get_items_new($db, true);
}

function regist_item($db, $name, $price, $stock, $status, $image){
  $filename = get_upload_filename($image);
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    return false;
  }
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

//アイテムを登録する際のトランザクションを施した関数
function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  $db->beginTransaction();
  //アイテムのインサートと画像の保存が成功した場合はコミット
  if(insert_item($db, $name, $price, $stock, $filename, $status) 
    && save_image($image, $filename)){
    $db->commit();
    return true;
  }
  //登録できなかった場合はロールバック
  $db->rollback();
  return false;
  
}

function insert_item($db, $name, $price, $stock, $filename, $status){
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES(?, ?, ?, ?, ?);
  ";

  return execute_query($db, $sql, [$name, $price, $stock, $filename, $status_value]);
}

//公開非公開を変更する関数
function update_item_status($db, $item_id, $status){
  $sql = "
    UPDATE
      items
    SET
      status = ?
    WHERE
      item_id = ?
    LIMIT 1
  ";
  
  return execute_query($db, $sql, [$status, $item_id]);
}

//アイテムのストックをアップデートする関数
function update_item_stock($db, $item_id, $stock){
  $sql = "
    UPDATE
      items
    SET
      stock = ?
    WHERE
      item_id = ?
    LIMIT 1
  ";
  
  return execute_query($db, $sql, [$stock, $item_id]);
}

function destroy_item($db, $item_id){
  $item = get_item($db, $item_id);
  if($item === false){
    return false;
  }
  $db->beginTransaction();
  //アイテム情報と画像情報を両方削除できた場合はコミット
  if(delete_item($db, $item['item_id'])
    && delete_image($item['image'])){
    $db->commit();
    return true;
  }
  //削除できないなどの不具合がある場合はロールバック
  $db->rollback();
  return false;
}

//アイテム情報削除する関数
function delete_item($db, $item_id){
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = ?
    LIMIT 1
  ";
  
  return execute_query($db, $sql, [$item_id]);
}


// 非DB

function is_open($item){
  return $item['status'] === 1;
}

function validate_item($name, $price, $stock, $filename, $status){
  $is_valid_item_name = is_valid_item_name($name);
  $is_valid_item_price = is_valid_item_price($price);
  $is_valid_item_stock = is_valid_item_stock($stock);
  $is_valid_item_filename = is_valid_item_filename($filename);
  $is_valid_item_status = is_valid_item_status($status);

  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

//アイテムの名前を検証するための関数
function is_valid_item_name($name){
  $is_valid = true;
  //商品名が規定より短い場合、または長い場合のエラー処理
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  return $is_valid;
}

//アイテムの値段を検証するための関数
function is_valid_item_price($price){
  $is_valid = true;
  if(is_positive_integer($price) === false){
    set_error('価格は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

//アイテムのストックを検証する関数
function is_valid_item_stock($stock){
  $is_valid = true;
  if(is_positive_integer($stock) === false){
    set_error('在庫数は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

//ファイルの名前を検証する関数
function is_valid_item_filename($filename){
  $is_valid = true;
  if($filename === ''){
    $is_valid = false;
  }
  return $is_valid;
}

//ファイルのステータスを検証する関数
function is_valid_item_status($status){
  $is_valid = true;
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    $is_valid = false;
  }
  return $is_valid;
}