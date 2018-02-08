<?php 

wp_login_form($mixArgumentArray);

$objClass = new SocialLoginPlugin();
$objClass->Init();
$objClass->Render();

?>
