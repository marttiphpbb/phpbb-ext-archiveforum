<?php

/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2019 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [

	'ACP_MARTTIPHPBB_ARCHIVEFORUM'						=> 'Archive Forum',
	'ACP_MARTTIPHPBB_ARCHIVEFORUM_SELECT'				=> 'Select archive forum',
	'ACP_MARTTIPHPBB_ARCHIVEFORUM_EXPLAIN'				=> '',
	'ACP_MARTTIPHPBB_ARCHIVEFORUM_SETTING_SAVED'		=> 'The setting was saved.'
]);
