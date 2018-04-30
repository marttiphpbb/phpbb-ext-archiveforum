<?php
/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\util;

class cnst
{
	const FOLDER = 'marttiphbb/archiveforum';
	const ID = 'marttiphpbb_archiveforum';
	const PREFIX = self::ID . '_';
	const CONFIG_ARCHIVE_ID = self::PREFIX . 'id';
	const FROM_FORUM_ID_COLUMN = 'marttiphpbb_archived_from_fid';
	const ARCHIVE_ACTION = self::PREFIX . 'archive';
	const RESTORE_ACTION = self::PREFIX . 'restore';
	const LANG = 'MARTTIPHPBB_ARCHIVEFORUM';
	const LANG_ACP = 'ACP_' . self::LANG;
	const LANG_MCP = 'MCP_' . self::LANG;
}
