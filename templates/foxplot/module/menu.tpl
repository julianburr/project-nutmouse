<?php
    $this->defineParam("menu", "int");
    $this->defineParam("id", "string");
    $this->defineParam("ids", "array(int)");
    $this->defineParam("labels", "array(int)");
?><nav class="menu <?php echo $this->getParam("class"); ?>">
	<ul>
	<?php
        if(is_array($this->getParam("ids"))){
			foreach($this->getParam("ids") as $key => $id){
            	$class = "";
                if($this->getVar('controller')->isCurrentContentID($id)){
                	$class .= " active";
                } elseif($this->getVar('controller')->isCurrentContentParent($id)){
                	$class .= " active-parent";
                }
                $labels = $this->getParam('labels');
                if(isset($labels[$key])){
                	$label = $labels[$key];
                } else {
                	$label = $this->getTitle($id);
                }
				echo "\t\t<li class='" . $class . "'><a href='" . $this->getUrl($id) . "'>" . $label . "</a></li>\n";
			}
		}
	?>
    </ul>
</nav>