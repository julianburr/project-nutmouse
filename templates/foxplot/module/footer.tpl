	<footer class="wrap-footer">
    	<div class="inner">
        	<div class="col col3">
            	<h2><?php echo Locale::get("Frontend.Footer.Menu.Title", null, "Wegweiser"); ?></h2>
        		<?php echo $this->includeTemplate("module/menu", array("ids" => array(1,2,3,4))); ?>
			</div>
            <div class="col col3">
            	<h2><?php echo Locale::get("Frontend.Footer.Features.Title", null, "Beliebte Features"); ?></h2>
        		<?php echo $this->includeTemplate("module/menu", array("ids" => array(1,2,3,4))); ?>
			</div>
            <div class="col col3">
            	<h2><?php echo Locale::get("Frontend.Footer.Follow.Title", null, "Folge uns"); ?></h2>
        		<?php echo Locale::get("Frontend.Footer.Follow.Text", null, "<p>Bleib auf dem Laufenden und folge Foxplot in Interweb. Egal ob Facebook, Instagram oder Email, wir halten dich up-to-date.</p>"); ?>
			</div>
        </div>
    </footer>