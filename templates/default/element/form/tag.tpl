<div class="wrap-form wrap-form-<?php echo $this->getParam('name'); ?>">
	<?php echo $this->includeTemplate("element/form/message", array("name" => $this->getParam("name"))); ?>
    <form name="<?php echo $this->getParam('name'); ?>" method="<?php echo $this->getParam('method'); ?>" action="<?php echo $this->getParam('action'); ?>">
    	<input type="hidden" name="formname" value="<?php echo $this->getParam('name'); ?>">
        <?php echo $this->getElements("inputs"); ?>
    </form>
</div>