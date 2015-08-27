<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Caught Exception</title>
</head>

<body>

	<h1>Exception Caught</h1>
    
    <p><?php echo $this->getVar('e')->getMessage(); ?></p>
    
    <pre><?php echo $this->getVar('e')->getTraceAsString(); ?></pre>

</body>
</html>