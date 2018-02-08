<?php
/* 
*  Module Name: AP Login/Registration Module
*  Description: An AgilePress module for creating and maintaining Regular and Social Login and Registeration pages
*  Author: Arun Surath
   Menu: ap-login-reg
*  version: 0.0
*/

require_once(str_replace("module.php", "" , __FILE__) . "/AP_LoginRegistrationModulePage.class.php" ); 

add_action('admin_menu', array(AP_LoginRegistrationAdminPage , "Init"));
