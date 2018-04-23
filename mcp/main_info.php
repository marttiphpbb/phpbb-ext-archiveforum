<?php
/**
* phpBB Extension - marttiphpbb archiveforum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\acp;

class main_info
{
	function module()
	{
		return [
			'filename'	=> '\marttiphpbb\archiveforum\mcp\main_module',
			'title'		=> 'MCP_MARTTIPHPBB_ARCHIVEFORUM',
			'modes'		=> [			
				'marttiphpbb_archiveforum_archive'	=> [
					'title'	=> 'MCP_MARTTIPHPBB_ARCHIVEFORUM_ARCHIVE',
					'auth'	=> 'ext_marttiphpbb/archiveforum && acl_a_board',
					'cat'	=> ['MCP_MARTTIPHPBB_ARCHIVEFORUM'],
				],					
				'marttiphpbb_archiveforum_restore'	=> [
					'title'	=> 'MCP_MARTTIPHPBB_ARCHIVEFORUM_RESTORE',
					'auth'	=> 'ext_marttiphpbb/archiveforum && acl_a_board',
					'cat'	=> ['MCP_MARTTIPHPBB_ARCHIVEFORUM'],
				],			
			],
		];
	}
}
