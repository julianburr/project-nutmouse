<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Log</title>
</head>

<body>

	<?php if($this->__['controller']->getSession()->isLoggedIn()): ?>
    <p>You are logged in!</p>
    <h1>Logout</h1>
    <form name="logout" action="" method="post">
    	<input type="hidden" name="do[]" value="userLogout">
        <button>Logout</button>
    </form>
    <?php else: ?>
    <h1>Login</h1>
    <form name="login" action="" method="post">
    	<input type="hidden" name="do[]" value="userLogin">
        <input type="text" name="username" placeholder="Username" value="<?php if(isset($this->__['controller']->getRequest()['username'])) echo $this->__['controller']->getRequest()['username']; ?>">
        <input type="password" name="password" placeholder="Password">
        <button>Login</button>
    </form>
    <?php endif; ?>
    
    <h1>Backtrace</h1>
    <pre><?php
    	debug_print_backtrace();
    ?></pre>
    
    <h1>Crypttest</h1>
    <?php
    	$test = "I am a string!";
        $encrypt = Crypt::encode($test, "mypassword");
        $decrypt = Crypt::decode($encrypt, "mypassword");
    ?>
    <pre><?php echo $encrypt; ?></pre>
    <pre><?php echo $decrypt; ?></pre>

	<h1>Log</h1>
    <pre><?php
    	var_dump($this->__);
    ?></pre>

</body>
</html>