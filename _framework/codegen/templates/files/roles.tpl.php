
class AP_UserRoleConstants {

	public static $strRolesArray = array(
<?php
foreach($mixUserRolesArray as $key => $mixUserRoleArray){
?>
		'<?= $mixUserRoleArray['slug'] ?>' => array(
			<?php
				foreach($mixUserRoleArray as $keyLower => $valueLower){
					?>
	'<?= $keyLower ?>' => <?php if(is_string($valueLower)) echo "'" . $valueLower . "'"; else{
						echo "array(
";
						foreach($valueLower as $keyBottom => $valueBottom){
								?>
					'<?= $valueBottom['name'] ?>' => <?= $valueBottom['value'] ?>,
<?php
						}
						echo "						)";
	}?><?= ($keyLower == "capabilities") ? "" : "," ?>

			<?php
		}
			?>
		)<?= ($key != (count($mixUserRolesArray) - 1)) ? "," : "" ?>

<?php
}?>
	);
	
}