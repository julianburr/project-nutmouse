<div class="wrap-input">
	<?php echo $this->includeTemplate("element/form/input/message", array("name" => $this->getParam("name"))); ?>
    <?php echo $this->includeTemplate("element/form/input/label", array("text" => $this->getParam("label"))); ?>
    <input class="password text <?php echo $this->getParam("class"); ?>" type="password" name="<?php echo $this->getParam("name"); ?>" value="<?php $this->getVar("controller")->getRequest($this->getParam("name")); ?>"<?php echo $this->printIfSet($this->getParam("placeholder"), ' placeholder="' . $this->getParam("placeholder") . '"'); ?>>
</div>