	<header class="wrap-header">
    	<div class="inner">
        	<div class="col col1">
            	<?php
                	echo $this->includeTemplate("module/header/logo");
                    echo $this->includeTemplate("module/menu", array("ids" => array(1,2,3,4)));
                ?>
            </div>
        </div>
    </header>