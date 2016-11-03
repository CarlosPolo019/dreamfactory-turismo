<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Additional Compiled Classes
	|--------------------------------------------------------------------------
	|
	| Here you may specify additional classes to include in the compiled file
	| generated by the `artisan optimize` command. These should be classes
	| that are included on basically every request into the application.
	|
	*/

	'files' => [
		realpath(__DIR__.'/../app/Providers/AppServiceProvider.php'),
		realpath(__DIR__.'/../app/Providers/EventServiceProvider.php'),
		realpath(__DIR__.'/../app/Providers/RouteServiceProvider.php'),
		realpath(__DIR__.'/../app/Http/Controllers/RestController.php'),
		realpath(__DIR__.'/../app/Http/Controllers/SplashController.php'),
		realpath(__DIR__.'/../app/Http/Controllers/StorageController.php'),
		realpath(__DIR__.'/../app/Http/Middleware/AccessCheck.php'),
		realpath(__DIR__.'/../app/Http/Middleware/Cors.php'),
		realpath(__DIR__.'/../app/Http/Middleware/FirstUserCheck.php'),
	],

	/*
	|--------------------------------------------------------------------------
	| Compiled File Providers
	|--------------------------------------------------------------------------
	|
	| Here you may list service providers which define a "compiles" function
	| that returns additional files that should be compiled, providing an
	| easy way to get common files from any packages you are utilizing.
	|
	*/

	'providers' => [
		//
	],

];
