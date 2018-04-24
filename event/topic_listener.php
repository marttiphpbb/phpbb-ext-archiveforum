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
use phpbb\template\template;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class topic_listener implements EventSubscriberInterface
{
	/** @var db */
	private $db;

	/** @var string */
	private $topics_table;

	/** @var string */
	private $forums_table;

	/** @var config */
	private $config;

	/** @var auth */
	private $auth;

	/** @var string */
	private $phpbb_root_path;

	/** @var string */
	private $php_ext;

	/** @var template */
	private $template;

	/** @var bool */
	private $already_triggered = false;

	/**
	 * @param db
	 * @param string
	 * @param string
	 * @param config
	 * @param auth
	 * @param string
	 * @param string 
	 * @param template
	*/
	public function __construct(
		db $db, 
		string $topics_table, 
		string $forums_table,
		config $config, 
		auth $auth,
		string $phpbb_root_path,
		string $php_ext,
		template $template
	)
	{
		$this->db = $db;
		$this->topics_table = $topics_table;
		$this->forums_table = $forums_table;
		$this->config = $config;
		$this->auth = $auth;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->template = $template;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.viewtopic_assign_template_vars_before'
				=> 'core_viewtopic_assign_template_vars_before',
			'core.mcp_topic_modify_post_data'
				=> 'core_mcp_topic_modify_post_data',
			'core.mcp_topic_review_modify_row'
				=> 'core_mcp_topic_review_modify_row',
		];
	}

	public function core_mcp_topic_modify_post_data(event $event)
	{
		/*
		$topic_data is sadly not included here. Therefore using 
		core.mcp_topic_review_modify_row
		*/
	}

	public function core_mcp_topic_review_modify_row(event $event)
	{
		if ($this->already_triggered)
		{
			return;
		}

		$this->already_triggered = true;

		$this->set_template_vars($event['topic_info']);
	}

	public function core_viewtopic_assign_template_vars_before(event $event)
	{
		$this->set_template_vars($event['topic_data']);
	}

	private function set_template_vars(array $topic_data)
	{
		$forum_id = $topic_data['forum_id'];
		$topic_id = $topic_data['topic_id'];
		
		$archive_id = $this->config['marttiphpbb_archiveforum_id'];

		if (!$archive_id || $forum_id != $archive_id)
		{
			return;
		}

		$org_forum_id = $topic_data['marttiphpbb_archived_from_fid'];

		if (!$org_forum_id)
		{
			return;
		}

		$sql = 'select forum_name
			from ' . $this->forums_table . '
			where forum_id = ' . $org_forum_id;

		$result = $this->db->sql_query($sql);
			
		$forum_name = $this->db->sql_fetchfield('forum_name');

		if (!$forum_name)
		{
			$sql = 'update ' . $this->topics_table . '
				set marttiphpbb_archived_from_fid = 0 
				where marttiphpbb_archived_from_fid = ' . $org_forum_id;
	
			$this->db->sql_query($sql);

			error_log('marttiphpbb/archiveforum: deleted forum with id ' . $org_forum_id . ' was removed from archive index (topic view)');

			return;
		}

		if (!$this->auth->acl_get('f_list', $org_forum_id))
		{
			return;
		}

		$this->template->assign_vars([
			'MARTTIPHPBB_ARCHIVEFORUM_NAME'	=> $forum_name,
			'U_MARTTIPHPBB_ARCHIVEFORUM'	=> append_sid($this->phpbb_root_path . 'viewforum.' . $this->php_ext, 'f=' . $org_forum_id),
		]);	
	}
}
