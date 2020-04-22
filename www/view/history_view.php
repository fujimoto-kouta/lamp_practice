<!DOCTYPE html>
<html lang='ja'>

<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>購入履歴</title>
</head>

<body>
  <?php include VIEW_PATH . 'templates/header_logined.php';
  require_once MODEL_PATH . 'functions.php';
  ?>

  <h1>【購入履歴】</h1>
  <table border="2" width="1300" align="center">
    <tr height="80" align="center">
      <th>注文番号</th>
      <th>注文日時</th>
      <th>合計金額</th>
      <th>購入明細</th>
    </tr>

    <?php foreach ($histories as $history) { ?>
      <tr height="50">
        <td><?php echo $history['order_number']; ?></td>
        <td><?php echo $history['created']; ?></td>
        <td><?php echo $history['total'] . '円'; ?></td>

        <form action='detail.php' method='post'>
        <td>
            <input type='hidden' name='order_number' value='<?php echo $history['order_number'] ?>'>
            <input type='hidden' name='created' value='<?php echo $history['created'] ?>'>
            <input type='hidden' name='total' value='<?php echo $history['total']?>'>
            <input type='submit' name='' value='明細'>
        </td>
        </form>
      </tr>
    <?php } ?>
    
  </table>
</body>

</html>