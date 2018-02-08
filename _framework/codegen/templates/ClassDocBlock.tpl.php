/**
<?php _e($objClassHolder->Description);
foreach($objClassHolder->Properties as $objProperty){
$objProperty->Name = str_replace(" ", "", $objProperty->Name);?>
 * @property <?php _e(AP_CoreCodeGenerator::ShortToLongType($objProperty->Type) . " " . str_replace("-", "", $objProperty->Name) . "\n");?>
<?php } ?>
**/

