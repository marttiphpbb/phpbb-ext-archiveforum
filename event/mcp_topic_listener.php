<?php
/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\event;

use phpbb\event\data as event;
use phpbb\db\driver\factory as db;
use phpbb\config\config;
use phpbb\auth\auth;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use marttiphpbb\archiveforum\util\cnst;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class mcp_topic_listener implements EventSubscriberInterface
{
	/** @var db */
	private $db;

	/** @var string */
	private $topics_table;

	/** @var config */
	private $config;

	/** @var auth */
	private $auth;

	/** @var language */
	private $language;

	/** @var request */
	private $request;

	/** @var template */
	private $template;

	/**
	 * @param db
	 * @param string
	 * @param config
	 * @param auth
	 * @param language
	 * @param template
	*/
	public function __construct(
		db $db, 
		string $topics_table, 
		config $config, 
		auth $auth,
		language $language,
		request $request,
		template $template
	)
	{
		$this->db = $db;
		$this->topics_table = $topics_table;
		$this->config = $config;
		$this->auth = $auth;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.viewtopic_add_quickmod_option_before'
				=> 'core_viewtopic_add_quickmod_option_before',
			'core.modify_quickmod_options'
				=> 'core_modify_quickmod_options',
			'core.modify_quickmod_actions'
				=> 'core_modify_quickmod_actions',
		];
	}

	public function core_modify_quickmod_actions(event $event)
	{
		$action = $event['action'];
		$quickmod = $event['quickmod'];

		$this->language->add_lang('mcp', cnst::FOLDER);

		if (!$quickmod)
		{
			return;
		}

		if (!in_array($action, [
			cnst::ARCHIVE_ACTION,
			cnst::RESTORE_ACTION,
		]))
		{
			return;
		}

		$archive_id = $this->config[cnst::CONFIG_ARCHIVE_ID];

		if (!$archive_id)
		{
			trigger_error('MCP_MARTTIPHPBB_ARCHIVEFORUM_NO_ARCHIVE_SET');
		}

		$s_archive = $action === cnst::ARCHIVE_ACTION;

		/** cancel button */
/*
		if ($this->request->variable('cancel', ''))
		{
			return;
		}
*/
		/* adapted from mpc.php */	

		$topic_id = $this->request->variable('t', 0);

		if (!$topic_id)
		{
			$this->language->add_lang('viewtopic');			
			trigger_error('NO_TOPIC_SELECTED');
		}

		if ($this->request->variable('confirm', ''))
		{
			mcp_move_topic([$topic_id]);
		}

		/* adapted from mcp_main.php move_topics() */

		if ($s_archive)
		{
			$to_forum_id = $archive_id;
		}
		else
		{
			$sql = 'select ' . cnst::FROM_FORUM_ID_COLUMN . ' 
				from ' . $this->topics_table . '
				where topic_id = ' . $topic_id;
		
			$result = $this->db->sql_query($sql);
	
			$to_forum_id = $this->db->sql_fetchfield(cnst::FROM_FORUM_ID_COLUMN);

			$this->db->sql_freeresult($result);

			if (!$to_forum_id)
			{			
				trigger_error('MCP_MARTTIPHPBB_ARCHIVEFORUM_TOPIC_NOT_RESTORABLE');				
			}
		}

		$redirect = $this->request->variable('redirect', build_url(['action', 'quickmod']));

		$s_hidden_fields = build_hidden_fields([
			'topic_id_list'	=> [$topic_id],
			'to_forum_id'	=> $to_forum_id,
			'f'				=> $forum_id,
			'action'		=> 'move',
			'redirect'		=> $redirect,
		]);

		if ($s_archive)
		{
			$this->template->assign_vars([
				'S_CAN_LEAVE_SHADOW'	=> true,
			]);
		}

		$message = 'MCP_MARTTIPHPBB_ARCHIVEFORUM_';
		$message .= $s_archive ? 'ARCHIVE' : 'RESTORE';
		$message .= '_TOPIC';
		$message .= count([$topic_id]) === 1 ? '' : 'S';

		confirm_box(false, $message, $s_hidden_fields, '@marttiphpbb_archiveforum/confirm.html');
	}

	public function core_modify_quickmod_options(event $event)
	{
		$module = $event['module'];
		$action = $event['action'];
		$is_valid_action = $event['is_valid_action'];

		if ($action === cnst::ARCHIVE_ACTION)
		{
			$is_valid_action = true;
			$module->load('mcp', 'main', 'quickmod');
		}

		if ($action === cnst::RESTORE_ACTION)
		{
			$is_valid_action = true;
			$module->load('mcp', 'main', 'quickmod');
		}

		$event['is_valid_action'] = $is_valid_action;
	}

	public function core_viewtopic_add_quickmod_option_before(event $event)
	{
		$quickmod_array = $event['quickmod_array'];
		$forum_id = $event['forum_id'];
		$topic_data = $event['topic_data'];

		$archive_id = $this->config[cnst::CONFIG_ARCHIVE_ID];

		if (!$archive_id)
		{
			return;
		}

		$quickmod_array[cnst::RESTORE_ACTION] = [
			'MARTTIPHPBB_ARCHIVEFORUM_QUICKMOD_RESTORE', 
			$forum_id == $archive_id
				&& $this->auth->acl_get('m_move', $forum_id) 
				&& $topic_data[cnst::FROM_FORUM_ID_COLUMN],
		];

		$quickmod_array[cnst::ARCHIVE_ACTION] = [
			'MARTTIPHPBB_ARCHIVEFORUM_QUICKMOD_ARCHIVE',
			$forum_id != $archive_id
				&& $this->auth->acl_get('m_move', $forum_id),
		];

		$event['quickmod_array'] = $quickmod_array;
	}
}
