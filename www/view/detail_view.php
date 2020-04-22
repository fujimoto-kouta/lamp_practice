<!DOCTYPE html>
<html lang='ja'>

<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>購入明細</title>
</head>

<body>
  <?php include VIEW_PATH . 'templates/header_logined.php';
  require_once MODEL_PATH . 'functions.php';

  ?>

<h1>【購入明細】</h1>
<table border="2" width="1300" align="center">
    <tr height="50" align="center">
      <th>注文番号</th>
      <th>注文日時</th>
      <th>合計金額</th>
    </tr>
      <tr>
        <td><?php echo $_POST['order_number']; ?></td>
        <td><?php echo $_POST['created']; ?></td>
        <td><?php echo $_POST['total'] . '円'; ?></td>
      </tr>
  </table>
  <br>
  <br>
  <table border="2" width="1300" align="center">
    <tr height="50" align="center">
      <th>商品名</th>
      <th>価格</th>
      <th>購入数</th>
      <th>小計</th>
    </tr>
    <?php foreach ($details as $detail) { ?>
      <tr>
        <td><?php echo $detail['name']; ?></td>
        <td><?php echo $detail['purchase_price'] . '円'; ?></td>
        <td><?php echo $detail['amount']; ?></td>
        <td><?php echo $detail['purchase_price'] * $detail['amount'] . '円'; ?></td>
      </tr>
    <?php } ?>
    
  </table>



