<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Foxplot Log</title>
</head>

<body>

	<?php if($this->__['controller']->getSession()->isLoggedIn()): ?>
    <p>You are logged in!</p>
    <h1>Logout</h1>
    <?php
    	$form = new FormManager();
        $form->setName("login");
        $form->setMethod("post");
        $form->addHiddenAction("userLogin");
        $form->addButton("Logout");
        echo $form->createOutput();
    ?>
    <?php else: ?>
    <h1>Login</h1>
    <?php
    	$form = new FormManager();
        $form->setName("login");
        $form->setMethod("post");
        $form->addHiddenAction("userLogin");
        $form->addInput("text", "username", $this->__['controller']->getRequest('username'));
        $form->addInput("password", "password", "");
        $form->addButton("Login");
        echo $form->createOutput();
    ?>
    <?php endif; ?>
    
    <h1>Backtrace</h1>
    <pre><?php
    	debug_print_backtrace();
    ?></pre>
    
	<h1>Log</h1>
    <pre><?php
    	var_dump($this->__);
    ?></pre>

</body>
</html>