<?php
/**
* phpBB Extension - marttiphpbb archiveforum
* @copyright (c) 2015 - 2019 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\acp;

use marttiphpbb\archiveforum\util\cnst;

class main_info
{
	function module()
	{
		return [
			'filename'	=> '\marttiphpbb\archiveforum\acp\main_module',
			'title'		=> cnst::L_ACP,
			'modes'		=> [			
				'select_forum'	=> [
					'title'	=> cnst::L_ACP . '_SELECT',
					'auth'	=> 'ext_marttiphpbb/archiveforum && acl_a_board',
					'cat'	=> [cnst::L_ACP],
				],			
			],
		];
	}
}
