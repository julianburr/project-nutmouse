<?php
	$pagetitle = $this->getVar("meta.title.0");
	if(!$pagetitle) $pagetitle = Config::get("Frontend.Page.DefaultTitle");
	$sitetitle = Config::get("Frontend.Title");
	$titlesep = "";
	if($sitetitle) $titlesep = " | ";
?>
 	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta charset="utf-8">
    
    <title><?php echo $pagetitle . $titlesep . $sitetitle; ?></title>