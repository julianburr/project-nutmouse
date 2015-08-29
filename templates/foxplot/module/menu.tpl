<nav class="menu <?php echo $this->getParam("class"); ?>">
	<ul>
	<?php
        if(is_array($this->getParam("ids"))){
			foreach($this->getParam("ids") as $id){
            	$class = "";
                if($this->getVar('controller')->isCurrentContentID($id)){
                	$class .= " active";
                } 
				echo "\t\t<li class='" . $class . "'><a href='" . $this->getUrl($id) . "'>" . $this->getTitle($id) . "</a></li>\n";
			}
		}
	?>
    </ul>
</nav>