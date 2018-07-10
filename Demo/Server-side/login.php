
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>登录</title>
</head>
<body>
<form method="post">
    <p>用户名：<input type="text" name="username" /></p>
    <p>密  码：<input type="password" name="password" /></p>
    <input type="hidden" name="callback" value="<?php echo $_GET['callback']; ?>" />
    <input type="submit" value="登录" />
</form>
</body>
</html>

