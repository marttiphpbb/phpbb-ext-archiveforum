<?php

/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
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

	'ACP_MARTTIPHPBB_ARCHIVEFORUM_ARCHIVE'		=> 'Archive',
	'ACP_MARTTIPHPBB_ARCHIVEFORUM_RESTORE'		=> 'Restore',
	'ACP_MARTTIPHPBB_ARCHIVEFORUM_FROM'			=> 'From %s',
]);
