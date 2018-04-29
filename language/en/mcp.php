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
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_TOPIC_NOT_RESTORABLE'
		=> 'The topic can not be restored from archive because the original forum is not known.',
	'MCP_FORUM_MARTTIPHPBB_ARCHIVEFORUM_ARCHIVE'		=> 'Archive',
	'MCP_FORUM_MARTTIPHPBB_ARCHIVEFORUM_RESTORE'		=> 'Restore from archive',
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_ARCHIVE_TOPIC'		=> 'Archive topic',
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_ARCHIVE_TOPICS'		=> 'Archive topics',
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_RESTORE_TOPIC'		=> 'Restore topic from archive',
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_RESTORE_TOPICS'		=> 'Restore topics from archive',
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_ARCHIVE_TOPIC_CONFIRM'
		=> 'Do you want to move the topic to the archive forum?',
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_ARCHIVE_TOPICS_CONFIRM'
		=> 'Do you want to move the topics to the archive forum?',		
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_RESTORE_TOPIC_CONFIRM'
		=> 'Do you want to restore the archived topic back to its original forum?',
	'MCP_MARTTIPHPBB_ARCHIVEFORUM_RESTORE_TOPICS_CONFIRM'
		=> 'Do you want to restore the archived topics back to their original forum?',
]);
