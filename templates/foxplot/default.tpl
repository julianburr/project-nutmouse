<!DOCTYPE html>
<html>
<head>
    <?php echo $this->includeTemplate("module/meta"); ?>
</head>

<body class="default content-<?php echo $this->getVar('data.id'); ?>">
<div class="wrap-all">
    
    <?php echo $this->includeTemplate("module/header"); ?>
    
    <?php if($this->countElements('intro') > 0): ?>
    <div class="wrap-intro">
    	<div class="inner">
        	<div class="col col1">
        		<?php echo $this->getElements('intro'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="wrap-content">
    	<div class="inner">
        	<div class="col col3">&nbsp;</div>
            <div class="col col3-2">
        		<?php echo $this->getElements('content'); ?>
            </div>
        </div>
    </div>
        
    <?php echo $this->includeTemplate("module/footer"); ?>

</div>
</body>
</html>