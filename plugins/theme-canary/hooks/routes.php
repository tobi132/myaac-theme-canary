<?php
defined('MYAAC') or die('Direct access not allowed!');

global $template_name;

if ($template_name === 'canary') {
	$args[] = [['get', 'post'], 'characters[/{name:[A-Za-z0-9-_%+\' \[\]]+}]', 'plugins/theme-canary/themes/canary/pages/characters.php', 500];
}
