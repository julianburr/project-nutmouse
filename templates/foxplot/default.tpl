<!DOCTYPE html>
<html>
<head>
    <?php echo $this->includeTemplate("module/meta"); ?>
</head>

<body>
    
    <?php  
    
    	// Header
    	echo $this->includeTemplate("module/header");
        
        // Main content elements from model
    	echo $this->getElements('content');
        
        // Footer
        echo $this->includeTemplate("module/footer");
        
    ?>

</body>
</html>