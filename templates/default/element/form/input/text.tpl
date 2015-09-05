<div class="wrap-input">
	<?php echo $this->includeTemplate("element/form/input/message", array("name" => $this->getParam("name"))); ?>
    <?php echo $this->includeTemplate("element/form/input/label", array("text" => $this->getParam("label"))); ?>
    <input class="text <?php echo $this->getParam("class"); ?>" type="text" name="<?php echo $this->getParam("name"); ?>" value="<?php echo $this->getVar("controller")->getRequest($this->getParam("name")); ?>"<?php echo $this->printIfSet($this->getParam("placeholder"), ' placeholder="' . $this->getParam("placeholder") . '"'); ?>>
</div>