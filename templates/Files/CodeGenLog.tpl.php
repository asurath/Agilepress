--------------------------------------------------------------------------------
CodeGen start date: <?php _e($this->strDate);?>

Requested by: <?php _e($this->objUser->data->display_name . " (ID #" . $this->objUser->data->ID . ")");?>

--------------------------------------------------------------------------------
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->Folders)); _e(($intCountArray[1]) ? $intCountArray[1] : 0)?> Plugin Folders Created:
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->Folders)); _e(($intCountArray[3]) ? $intCountArray[3] : 0)?> Plugin Folders OverWritten:
<?php foreach ($this->objCodeGenStatus->Folders as $strPath => $intSuccess){?>
	[<?php _e($strPath . "] "); _e(OutputChangeMessage($intSuccess));?> 
<?php }?>
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->ExtensionFiles)); _e(($intCountArray[1]) ? $intCountArray[1] : 0)?> Standard Plugin Files Created:
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->ExtensionFiles)); _e(($intCountArray[3]) ? $intCountArray[3] : 0)?> Standard Plugin Files OverWritten:
<?php foreach ($this->objCodeGenStatus->ExtensionFiles as $strName => $intSuccess){?>
	[<?php _e($strName . "] "); _e(OutputChangeMessage($intSuccess));?> 
<?php }?>
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->Controls)); _e(($intCountArray[1]) ? $intCountArray[1] : 0)?> Controls Created:
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->Controls)); _e(($intCountArray[3]) ? $intCountArray[3] : 0)?> Controls OverWritten:
<?php foreach ($this->objCodeGenStatus->Controls as $strName => $intSuccess){?>
	[<?php _e($strName . "] "); _e(OutputChangeMessage($intSuccess));?> 
<?php }?>
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->PostTypes)); _e(($intCountArray[1]) ? $intCountArray[1] : 0)?> PostTypes Created:
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->PostTypes)); _e(($intCountArray[3]) ? $intCountArray[3] : 0)?> PostTypes OverWritten:
<?php foreach ($this->objCodeGenStatus->PostTypes as $strName => $intSuccess){?>
	[<?php _e($strName . "] "); _e(OutputChangeMessage($intSuccess));?> 
<?php }?>
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->PostItems)); _e(($intCountArray[1]) ? $intCountArray[1] : 0)?> PostItems Created:
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->PostItems)); _e(($intCountArray[3]) ? $intCountArray[3] : 0)?> PostItems OverWritten:
<?php foreach ($this->objCodeGenStatus->PostItems as $strName => $intSuccess){?>
	[<?php _e($strName . "] "); _e(OutputChangeMessage($intSuccess));?> 
<?php }?>
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->UserTypes)); _e(($intCountArray[1]) ? $intCountArray[1] : 0)?> UserTypes Created:
<?php $intCountArray = array_count_values(ArrayClean($this->objCodeGenStatus->UserTypes)); _e(($intCountArray[3]) ? $intCountArray[3] : 0)?> UserTypes OverWritten:
<?php foreach ($this->objCodeGenStatus->UserTypes as $strName => $intSuccess){?>
	[<?php _e($strName . "] "); _e(OutputChangeMessage($intSuccess));?> 
<?php }?>
--------------------------------------------------------------------------------
END OF CODEGEN REPORT
--------------------------------------------------------------------------------
<?php
function OutputChangeMessage($intSuccess){
	switch ($intSuccess){
		case 0:
			return "failed to write,";
		case 1:
			return "was written,";
		case 2:
			return "already exists, was not modified,";
		case 3:
			return "already exists, was overwritten,";
	}
}

function ArrayClean($var){
	foreach( $var as $key => $value){
		if($value === true)
			$var[$key] = 1;
		if($var === false)
			$var[$key] = 0;
		}
	return $var;
}