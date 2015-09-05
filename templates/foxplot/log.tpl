<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Foxplot Log</title>
</head>

<body>

	<h1>Access</h1>

	<h1>Test</h1>
    <?php 
    	echo FormManager::createInput("text", "test", "Dies ist ein Test"); 
    ?>

	<?php if($this->getVar('controller')->getSession()->isLoggedIn()): ?>
    <p>You are logged in!</p>
    <h1>Logout</h1>
    <?php
    	$form = new FormManager();
        $form->setName("login");
        $form->setMethod("post");
        $form->addHiddenAction("userLogout");
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
        $form->addInput("text", "username", $this->getVar('controller')->getRequest('username'));
        $form->addInput("password", "password", "");
        $form->addButton("Login");
        echo $form->createOutput();
    ?>
    <?php endif; ?>
    
    <h1>Backtrace</h1>
    <pre><?php
    	debug_print_backtrace();
    ?></pre>

</body>
</html>