<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Log</title>
</head>

<body>

	<h1>_POST</h1>
    <pre><?php var_dump($this->getVar('controller')->getPost()); ?>

	<h1>Session</h1>
    <pre><?php var_dump($this->getVar('controller')->getSession()->isLoggedIn(), $this->getVar('controller')->getSession()->getUser()); ?></pre>
    
    <h1>Backtrace</h1>
    <pre><?php
    	debug_print_backtrace();
    ?></pre>
    
	<h1>Log</h1>
    <pre><?php
    	var_dump($this->getVars());
    ?></pre>

</body>
</html>