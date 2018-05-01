<?php
/**
* phpBB Extension - marttiphpbb archiveforum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\acp;

use marttiphpbb\archiveforum\util\cnst;

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
	
		$language->add_lang('acp', cnst::FOLDER);
		add_form_key(cnst::FOLDER);

		switch($mode)
		{
			case 'select_forum':

				$this->tpl_name = 'select_forum';
				$this->page_title = $language->lang(cnst::L_ACP . '_SELECT');

				if ($request->is_set_post('submit'))
				{
					if (!check_form_key(cnst::FOLDER))
					{
						trigger_error('FORM_INVALID');
					}

					$config->set(cnst::CONFIG_ARCHIVE_ID, $request->variable(cnst::CONFIG_ARCHIVE_ID, 0));				

					trigger_error($language->lang(cnst::L_ACP . '_SETTING_SAVED') . adm_back_link($this->u_action));
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

				$template->assign_var(cnst::L . '_ID', $config[cnst::CONFIG_ARCHIVE_ID]);
	
				break;
		}

		$template->assign_var('U_ACTION', $this->u_action);
	}
}
