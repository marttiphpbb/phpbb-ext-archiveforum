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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var db */
	private $db;

	/** @var string */
	private $topics_table;

	/** @var config */
	private $config;

	/** @var auth */
	private $auth;

	/**
	* @param db
	*/
	public function __construct(db $db, string $topics_table, config $config, auth $auth)
	{
		$this->db = $db;
		$this->topics_table = $topics_table;
		$this->config = $config;
		$this->auth = $auth;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup'					=> 'core_user_setup',
			'core.move_topics_before_query'		=> 'core_move_topics_before_query',
			'core.viewtopic_add_quickmod_option_before'
				=> 'core_viewtopic_add_quickmod_option_before',
			'core.mcp_global_f_read_auth_after'
				=> 'core_mcp_global_f_read_auth_after',
			'core.modify_quickmod_options'
				=> 'core_modify_quickmod_options',
		];
	}

	public function core_modify_quickmod_options(event $event)
	{
		$module = $event['module'];
		$action = $event['action'];
		$is_valid_action = $event['is_valid_action'];



		
		$event['is_valid_action'] = $is_valid_action;
	}

	public function core_mcp_global_f_read_auth_after(event $event)
	{
		$action = $event['action'];
		$forum_id = $event['forum_id'];

		if ($action === 'marttiphpbb_archiveforum_restore')
		{
			$action = 'move';
		}

		if ($action === 'marttiphpbb_archiveforum_archive')
		{
			$action = 'move';
			$forum_id = $this->config['marttiphpbb_archiveforum_id'];
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
			'MARTTIPHPBB_ARCHIVEFORUM_RESTORE', 
			$forum_id === $this->config['marttiphpbb_archiveforum_id']
				&& $this->auth->acl_get('m_move', $forum_id) 
				&& $topic_data['marttiphpbb_archived_from_fid'],
		];

		$quickmod_array['marttiphpbb_archiveforum_archive'] = [
			'MARTTIPHPBB_ARCHIVEFORUM_ARCHIVE',
			$forum_id !== $this->config['marttiphpbb_archiveforum_id']
				&& $this->auth->acl_get('m_move', $forum_id),
		];

		$event['quickmod_array'] = $quickmod_array;
	}

	public function core_user_setup(event $event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'marttiphpbb/archiveforum',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function core_move_topics_before_query(event $event)
	{
		$topic_ids = $event['topic_ids'];

		// we store the forum id where the topic came from.
		$sql = 'update ' . $this->topics_table . '
			set marttiphpbb_archived_from_fid = forum_id 
			where ' . $this->db->sql_in_set('topic_id', $topic_ids);
		$this->db->sql_query($sql);
	}
}
