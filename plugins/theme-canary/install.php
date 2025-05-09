<?php

use MyAAC\Plugins;

defined('MYAAC') or die('Direct access not allowed!');

Plugins::installMenus('canary', require PLUGINS . 'theme-canary/themes/canary/menus.php');
