<?php
global $template_name;

if ($template_name === 'canary') {
	$args[] = [['get', 'post'], 'characters[/{name:string}]', 'plugins/theme-canary/themes/canary/pages/characters.php', 500];
}
