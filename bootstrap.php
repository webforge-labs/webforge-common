<?php
/**
 * Bootstrap and Autoload whole application
 *
 * you can use this file to bootstrap for tests or bootstrap for scripts / others
 */
$ds = DIRECTORY_SEPARATOR;

// autoload project dependencies and self autoloading for the library
require_once __DIR__.$ds.'vendor'.$ds.'autoload.php';

ini_set('mbstring.internal_encoding', 'UTF-8');

return $GLOBALS['env']['root'] = new \Webforge\Common\System\Dir(__DIR__.DIRECTORY_SEPARATOR);
?>