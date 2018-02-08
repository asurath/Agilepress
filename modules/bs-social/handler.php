<?php

include_once(  str_replace( "wp-content\plugins\agilepress\modules\bs-social" , "" , dirname(__FILE__)) . "wp-blog-header.php");
$social = new SocialLoginPlugin();
$social->Run();