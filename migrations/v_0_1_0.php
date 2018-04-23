<?php
/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\migrations;

class v_0_1_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return [
			['config.add', ['marttiphpbb_archiveforum_id', 0]],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_MARTTIPHPBB_ARCHIVEFORUM',
			]],

			['module.add', [
				'acp',
				'ACP_MARTTIPHPBB_ARCHIVEFORUM',
				[
					'module_basename'	=> '\marttiphpbb\archiveforum\acp\main_module',
					'modes'				=> [
						'select_forum',
					],
				],
			]],

			
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'        => [
				$this->table_prefix . 'topics' => [
					'marttiphpbb_archived_from_fid'  => ['UINT', NULL],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'        => [
				$this->table_prefix . 'topics'	=> [
					'marttiphpbb_archived_from_fid',
				],
			],
		];
	}
}
