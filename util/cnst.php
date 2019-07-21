<?php
/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2019 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\util;

class cnst
{
	const FOLDER = 'marttiphpbb/archiveforum';
	const ID = 'marttiphpbb_archiveforum';
	const PREFIX = self::ID . '_';
	const CONFIG_ARCHIVE_ID = self::PREFIX . 'id';
	const FROM_FORUM_ID_COLUMN = 'marttiphpbb_from_forum_id';
	const ARCHIVE_ACTION = self::PREFIX . 'archive';
	const RESTORE_ACTION = self::PREFIX . 'restore';
	const L = 'MARTTIPHPBB_ARCHIVEFORUM';
	const L_ACP = 'ACP_' . self::L;
	const L_MCP = 'MCP_' . self::L;
	const TPL = '@' . self::ID . '/';
}
