<?php
/**
* phpBB Extension - marttiphpbb archiveforum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\acp;

class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$request = $phpbb_container->get('request');
		$template = $phpbb_container->get('template');
		$config = $phpbb_container->get('config');
		$language = $phpbb_container->get('language');
	
		$language->add_lang('acp', 'marttiphpbb/archiveforum');
		add_form_key('marttiphpbb/archiveforum');

		switch($mode)
		{
			case 'select_forum':

				$this->tpl_name = 'select_forum';
				$this->page_title = $language->lang('ACP_MARTTIPHPBB_ARCHIVEFORUM_SELECT');

				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('marttiphpbb/archiveforum'))
					{
						trigger_error('FORM_INVALID');
					}

					$config->set('marttiphpbb_archiveforum_id', $request->variable('marttiphpbb_archiveforum_id', 0));				

					trigger_error($language->lang('ACP_MARTTIPHPBB_ARCHIVEFORUM_SETTING_SAVED') . adm_back_link($this->u_action));
				}

				$cforums = make_forum_select(false, false, false, false, true, false, true);

				foreach ($cforums as $forum)
				{
					$forum_id = $forum['forum_id'];

					$template->assign_block_vars('cforums', [
						'NAME'		=> $forum['padding'] . $forum['forum_name'],
						'ID'		=> $forum_id,
					]);
				}

				$template->assign_var('MARTTIPHPBB_ARCHIVEFORUM_ID', $config['marttiphpbb_archiveforum_id']);
	
				break;
		}

		$template->assign_var('U_ACTION', $this->u_action);
	}
}
