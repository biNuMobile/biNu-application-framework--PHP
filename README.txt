This PHP class is designed to hide the developer from the nuances of Binu Markup Language (BML).

In its initial form it does not support all BML schema options. Full documentation on BML schema is available here:

http://developer.binu.com/wp-content/xml_doc/index.html

Example use of PHP helper class below:

<?php
require_once('class.biNu.php');

// Assign application configuration variables during constructor
$app_config = array (
	'dev_id' => 999999,								// Your DevCentral developer ID goes here
	'app_id' => 999999,								// Your DevCentral application ID goes here
	'app_name' => 'My First biNu App',				// Your application name goes here
	'app_home' => 'http://yourdomain.com/my_app/',	// Publically accessible URI
	'ttl' => 1										// Your page "time to live" parameter here
);

try {
	// Construct biNu object
	$binu_app = new biNu_app($app_config);

	if (TESTING) {
		// Show test info
		add_test_info();

		/* Override TTL for testing purposes */
		$binu_app->time_to_live = 1;
	}
	
	$binu_app->add_style( array('name' => 'body_text', 'color' => '#1540eb') );
	$binu_app->add_text('Hello world', 'body_text');

	/* Process menu options */
	$binu_app->add_menu_item( '8', 'My App Home', $binu_app->application_URL  );
	$binu_app->add_menu_item( '9', 'biNu Home', 'http://apps.binu.net/apps/mybinu/index.php' );

	/* Show biNu page */
	$binu_app->generate_BML();

} catch (Exception $e) {
	app_error('Error: '.$e->getMessage());
}

/* Test function declarations */
function add_test_info() {
	global $$binu_app;
	$binu_app->add_text('Your device id is :'.$binu_app->device_id, 'body');
	$binu_app->add_text('Your Width is :'.$binu_app->screen_width, 'body');
	$binu_app->add_text('Your Height is :'.$binu_app->screen_height, 'body');
	$binu_app->add_text('Your Orientation is :'.$binu_app->orientation, 'body');
}

?>