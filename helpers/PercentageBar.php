<?php

/**
 * Shortcut to output safe text. 
 *
 */
class Helper_PercentageBar extends NovemberHelper
{
	public function PercentageBar($percentageComplete, $scale = 1, $colour='#00CF1C')
	{	
		$barwidth = $scale * 100;
		$barheight = $scale * 10;
		$percentage = $percentageComplete * $scale;
		$color = $percentage > ($barwidth) ? '#f00' : $colour;
		$fontSize = $barheight - 2;
		?>
		<div style="float: right; width: <?php echo $barwidth;?>px; background-color: #4F0014; height: <?php echo $barheight;?>px;">
		<span style="color: #eef; float: right; font-size: <?php echo $fontSize;?>px"><?php echo sprintf('%0.2f', $percentageComplete) ?>%</span>
		<div style="height: <?php echo $barheight;?>px; background-color: <?php echo $color?>; width: <?php echo $percentage > $barwidth ? ($barwidth+5) : $percentage?>px;"></div>
		</div>
		<?php 
	}
}

?>