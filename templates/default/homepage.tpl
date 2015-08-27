<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $this->getVar('meta.title'); ?></title>
</head>

<body class="default home">
<div id="wrap-all">
	
    <div id="wrap-teaser">
    	<div class="wrap-inner">
        	<?php echo $this->getElements('teaser'); ?>
        </div>
    </div>
    
    <div id="wrap-content">
    	<div class="wrap-inner">
    		<?php echo $this->getElements('content'); ?>
        </div>
    </div>
    
    <div id="wrap-footer">
    </div>

</div>
</body>
</html>