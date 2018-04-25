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
			'core.mcp_global_f_read_auth_after'
				=> 'core_mcp_global_f_read_auth_after',
			'core.modify_quickmod_options'
				=> 'core_modify_quickmod_options',
			'core.modify_quickmod_actions'
				=> 'core_modify_quickmod_actions',
		];
	}

/** mcp_main.php
* This event allows you to handle custom quickmod options
*
* @event core.modify_quickmod_actions
* @var	string	action		Topic quick moderation action name
* @var	bool	quickmod	Flag indicating whether MCP is in quick moderation mode
* @since 3.1.0-a4
* @changed 3.1.0-RC4 Added variables: action, quickmod
*/
/*
$vars = array('action', 'quickmod');
extract($phpbb_dispatcher->trigger_event('core.modify_quickmod_actions', compact($vars)));
*/

	public function core_modify_quickmod_actions(event $event)
	{
		$action = $event['action'];
		$quickmod = $event['quickmod'];

		$this->language->add_lang('mcp', 'marttiphpbb/archiveforum');

		if (!$quickmod)
		{
			return;
		}

		if (!in_array($action, [
			'marttiphpbb_archiveforum_archive', 
			'marttiphpbb_archiveforum_restore',
		]))
		{
			return;
		}

		$s_archive = $action === 'marttiphpbb_archiveforum_archive';

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
			$to_forum_id = $this->config['marttiphpbb_archiveforum_id'];
		}
		else
		{
			$sql = 'select marrtiphpbb_archived_from_id 
				from ' . TOPICS_TABLE . '
				where topic_id = ' . $topic_id;

			$to_forum_id = $this->db->fetchfield('marttiphpbb_archived_from_id');

			if (!$to_forum_id)
			{			
				trigger_error('MARTTIPHPBB_ARCHIVEFORUM_TOPIC_NOT_ARCHIVED');				
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

/*
		$this->template->assign_vars([,
			'S_CAN_LEAVE_SHADOW'	=> true,
		]);
*/

		$message = 'MCP_MARTTIPHPBB_ARCHIVEFORUM_';
		$message .= $s_archive ? 'ARCHIVE' : 'RESTORE';
		$message .= '_TOPIC';
		$message .= count([$topic_id]) === 1 ? '' : 'S';

		confirm_box(false, $message, $s_hidden_fields, '@marttiphpbb_archiveforum/confirm.html');
	}

/** mcp.php
* This event allows you to add custom quickmod options
*
* @event core.modify_quickmod_options
* @var	object	module			Instance of module system class
* @var	string	action			Quickmod option
* @var	bool	is_valid_action	Flag indicating if the action was handled properly
* @since 3.1.0-a4
*/
/*
$vars = array('module', 'action', 'is_valid_action');
extract($phpbb_dispatcher->trigger_event('core.modify_quickmod_options', compact($vars)));
*/


	public function core_modify_quickmod_options(event $event)
	{
		$module = $event['module'];
		$action = $event['action'];
		$is_valid_action = $event['is_valid_action'];

		if ($action === 'marttiphpbb_archiveforum_archive')
		{
			$is_valid_action = true;
			$module->load('mcp', 'main', 'quickmod');
		}

		if ($action === 'marttiphpbb_archiveforum_restore')
		{
			$is_valid_action = true;
			$module->load('mcp', 'main', 'quickmod');
		}

		$event['is_valid_action'] = $is_valid_action;
	}



	public function core_mcp_global_f_read_auth_after(event $event)
	{
		$action = $event['action'];
		$forum_id = $event['forum_id'];

		if ($action === 'marttiphpbb_archiveforum_restore')
		{
		//	$action = 'move';
		}

		if ($action === 'marttiphpbb_archiveforum_archive')
		{
//			$action = 'move';
//			$forum_id = $this->config['marttiphpbb_archiveforum_id'];
		}

		$event['action'] = $action;
		$event['forum_id'] = $forum_id;
	}







	public function core_viewtopic_add_quickmod_option_before(event $event)
	{
		$quickmod_array = $event['quickmod_array'];
		$forum_id = $event['forum_id'];
		$topic_data = $event['topic_data'];

		$quickmod_array['marttiphpbb_archiveforum_restore'] = [
			'MARTTIPHPBB_ARCHIVEFORUM_QUICKMOD_RESTORE', 
			$forum_id == $this->config['marttiphpbb_archiveforum_id']
				&& $this->auth->acl_get('m_move', $forum_id) 
				&& $topic_data['marttiphpbb_archived_from_fid'],
		];

		$quickmod_array['marttiphpbb_archiveforum_archive'] = [
			'MARTTIPHPBB_ARCHIVEFORUM_QUICKMOD_ARCHIVE',
			$forum_id != $this->config['marttiphpbb_archiveforum_id']
				&& $this->auth->acl_get('m_move', $forum_id),
		];

		$event['quickmod_array'] = $quickmod_array;
	}
}
