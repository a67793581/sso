
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>管理</title>
</head>
<body>
<?php

$sign = $core->get_cookie('sign');

echo "系统检测到您已登录 {$sign} <a href='{$_GET['callback']}?sign={$sign}'>授权</a> <a href='/index.php?logout=1&callback={$_GET['callback']}'>退出</a>"; ?>
</body>
</html>

