<?php
/**
 * Server info
 *
 * @package   MyAAC
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @author    whiteblXK
 * @author    OpenTibiaBR
 * @copyright 2023 MyAAC
 * @link      https://github.com/opentibiabr/myaac
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Server Info';

$rent = trim(strtolower(configLua('houseRentPeriod')));
if ($rent != 'yearly' && $rent != 'monthly' && $rent != 'weekly' && $rent != 'daily')
	$rent = 'never';

$houseLevel = configLua('houseBuyLevel');
$cleanOld = null;

if ($pzLocked = configLua('pzLocked') ?? null)
	$pzLocked = eval('return ' . $pzLocked . ';');

if ($whiteSkullTime = configLua('whiteSkullTime') ?? null)
	$whiteSkullTime = eval('return ' . $whiteSkullTime . ';');

if ($redSkullDuration = configLua('redSkullDuration') ?? null)
	$redSkullDuration = eval('return ' . $redSkullDuration . ';');

if ($blackSkullDuration = configLua('blackSkullDuration') ?? null)
	$blackSkullDuration = eval('return ' . $blackSkullDuration . ';');

$explodeServerSave = explode(':', configLua('globalServerSaveTime') ?? '05:00:00');
$hours_ServerSave = $explodeServerSave[0];
$minutes_ServerSave = $explodeServerSave[1];
$seconds_ServerSave = $explodeServerSave[2];

$now = new DateTime();
$serverSaveTime = new DateTime();
$serverSaveTime->setTime($hours_ServerSave, $minutes_ServerSave, $seconds_ServerSave);

if ($now > $serverSaveTime) {
	$serverSaveTime->modify('+1 day');
}

$config['lua']['rateStages'] = loadStagesData($config['server_path'] . 'data/stages.lua');

$twig->display('server-info.html.twig', [
	'serverSave' => $explodeServerSave,
	'serverSaveTime' => $serverSaveTime->format('Y, n-1, j, G, i, s'),
	'rateUseStages' => $rateUseStages = getBoolean(configLua('rateUseStages')),
	'rateStages' => $rateUseStages && isset($config['lua']['rateStages']) ? $config['lua']['rateStages'] : [],
	'serverIp' => str_replace(['http://', 'https://', '/'], '', configLua('url')),
	'clientVersion' => $status['clientVersion'] ?? null,
	'protectionLevel' => configLua('protectionLevel'),
	'houseRent' => $rent == 'never' ? 'disabled' : $rent,
	'houseOld' => $cleanOld ?? null, // in progressing
	'rateExp' => configLua('rateExp'),
	'rateMagic' => configLua('rateMagic'),
	'rateSkill' => configLua('rateSkill'),
	'rateLoot' => configLua('rateLoot'),
	'rateSpawn' => configLua('rateSpawn'),
	'houseLevel' => $houseLevel,
	'pzLocked' => $pzLocked,
	'whiteSkullTime' => $whiteSkullTime,
	'redSkullDuration' => $redSkullDuration,
	'blackSkullDuration' => $blackSkullDuration,
	'dailyFragsToRedSkull' => configLua('dayKillsToRedSkull') ?? null,
	'weeklyFragsToRedSkull' => configLua('weekKillsToRedSkull') ?? null,
	'monthlyFragsToRedSkull' => configLua('monthKillsToRedSkull') ?? null,
]);

/**
 * @param $configFile
 * @return array
 *
 * Function to get stages.lua from canary server.
 */
function loadStagesData($configFile)
{
	if (!@file_exists($configFile)) {
		log_append('error.log', "[loadStagesData] Fatal error: Cannot load stages.lua ($configFile).");
		throw new RuntimeException("ERROR: Cannot find $configFile file.");
	}

	$result = [];
	$config_string = str_replace(["\r\n", "\r"], "\n", file_get_contents($configFile));
	$lines = explode("\n", $config_string);

	$lastKey = '';
	if (count($lines) > 0) {
		for ($ln = 0; $ln < count($lines); $ln++) {
			$line = str_replace(' ', '', trim($lines[$ln]));
			if (strpos($line, '--') !== false || empty($line)) {
				continue;
			}

			if (strpos($line, 'experienceStages') !== false) {
				$lastKey = 'experienceStages';
				$result[$lastKey] = [];
			} elseif (strpos($line, 'skillsStages') !== false) {
				$lastKey = 'skillsStages';
				$result[$lastKey] = [];
			} elseif (strpos($line, 'magicLevelStages') !== false) {
				$lastKey = 'magicLevelStages';
				$result[$lastKey] = [];
			}

			if (strpos($line, '{') !== false) {
				$checks = [
					'min' => @explode('=', $lines[$ln + 1]),
					'max' => @explode('=', $lines[$ln + 2]),
					'mul' => @explode('=', $lines[$ln + 3]),
				];
				$minlevel =
					isset($checks['min'][0]) && trim($checks['min'][0]) == 'minlevel'
						? $checks['min'][1]
						: null;
				$maxlevel = !isset($checks['mul'][1])
					? null
					: (trim($checks['max'][0]) == 'maxlevel'
						? $checks['max'][1]
						: null);
				$multiplier =
					isset($checks['mul'][0]) && trim($checks['mul'][0]) == 'multiplier'
						? $checks['mul'][1]
						: (trim($checks['max'][0]) == 'multiplier'
						? $checks['max'][1]
						: null);

				if (!$minlevel && !$maxlevel && !$multiplier) {
					continue;
				}

				$result[$lastKey][] = [
					'minlevel' => $minlevel ? (int) str_replace([' ', ','], '', $minlevel) : null,
					'maxlevel' => $maxlevel ? (int) str_replace([' ', ','], '', $maxlevel) : null,
					'multiplier' => $multiplier ? (int) str_replace([' ', ','], '', $multiplier) : null,
				];
			}
		}
	}
	return $result;
}
