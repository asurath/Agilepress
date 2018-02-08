<?php
define ('__FRAMEWORK_PATH', dirname(__FILE__));
define('SOCIAL_LOGIN_CLASSES', __FRAMEWORK_PATH . '/classes');
define('SOCIAL_LOGIN_BASE_CLASSES', __FRAMEWORK_PATH . '/classes/base');

// FACEBOOK

define( 'FACEBOOK_APP_ID', get_option("FBAppID") );
define( 'FACEBOOK_APP_SECRET', get_option("FBAppSecret") );
define( 'FACEBOOK_REDIRECT', get_option("FBAppRedirect") );

// GOOGLE

define( 'GOOGLE_CLIENT_ID', get_option("GAppID") );
define( 'GOOGLE_CLIENT_SECRET',  get_option("GAppSecret") );
define( 'GOOGLE_REDIRECT', get_option("GAppRedirect") );
define( 'GOOGLE_DEVELOPER_KEY', get_option("GAppDevKey") );

// LINKEDIN

define( 'LINKEDIN_APP_KEY', get_option("LIAppID") );
define( 'LINKEDIN_APP_SECRET', get_option("LIAppSecret") );		
define( 'LINKEDIN_REDIRECT', get_option("LIAppRedirect") );

// LINKEDIN

define( 'LINKEDIN_APP_KEY',  get_option("TwtAppID") );
define( 'LINKEDIN_APP_SECRET',  get_option("TwtAppSecret") );		
define( 'LINKEDIN_REDIRECT',  get_option("TwtAppRedirect") );

