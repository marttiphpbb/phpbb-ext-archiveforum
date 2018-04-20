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
			'filename'	=> '\marttiphpbb\archiveforum\acp\main_module',
			'title'		=> 'ACP_MARTTIPHPBB_ARCHIVEFORUM',
			'modes'		=> [			
				'select_forum'	=> [
					'title'	=> 'ACP_MARTTIPHPBB_ARCHIVEFORUM_SELECT',
					'auth'	=> 'ext_marttiphpbb/archiveforum && acl_a_board',
					'cat'	=> ['ACP_MARTTIPHPBB_ARCHIVEFORUM'],
				],			
			],
		];
	}
}
