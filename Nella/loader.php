<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

use Nette\Diagnostics\Debugger;

if (!defined('LIBS_DIR')) {
    define ('LIBS_DIR', __DIR__ . "/../");
}

// Load and init Nette Framework
if (!defined('NETTE')) {
	require_once LIBS_DIR . "/Nette/loader.php";
}

// Set debug options
Debugger::$strictMode = TRUE;
Debugger::$maxLen = 4096;

/**
 * Load and configure Nella Framework
 */
define('NELLA_FRAMEWORK', TRUE);
define('NELLA_FRAMEWORK_DIR', __DIR__);
define('NELLA_FRAMEWORK_VERSION_ID', 20000); // v2.0.0

require_once __DIR__ . "/SplClassLoader.php";
Nella\SplClassLoader::getInstance(array(
	'Nella' => NELLA_FRAMEWORK_DIR,
	'Doctrine' => LIBS_DIR . "/Doctrine",
	'Symfony' => LIBS_DIR . "/Symfony",
))->register();

require_once __DIR__ . "/shortcuts.php";
require_once __DIR__ . "/Localization/shortcuts.php";
