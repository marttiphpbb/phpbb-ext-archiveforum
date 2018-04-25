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

class topiclist_listener implements EventSubscriberInterface
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

	/** @var array */
	private $topics_org_forums = [];

	/** @var array */
	private $forum_names = [];

	/**
	 * @param db
	 * @param string
	 * @param string
	 * @param config
	 * @param auth
	 * @param string
	 * @param string 
	*/
	public function __construct(
		db $db, 
		string $topics_table, 
		string $forums_table,
		config $config, 
		auth $auth,
		string $phpbb_root_path,
		string $php_ext
	)
	{
		$this->db = $db;
		$this->topics_table = $topics_table;
		$this->forums_table = $forums_table;
		$this->config = $config;
		$this->auth = $auth;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.viewforum_modify_topics_data'		=> 'core_viewforum_modify_topics_data',
			'core.viewforum_modify_topicrow' 		=> 'core_viewforum_modify_topicrow',
			'core.mcp_view_forum_modify_sql'		=> 'core_mcp_view_forum_modify_sql',
			'core.mcp_view_forum_modify_topicrow'	=> 'core_mcp_view_forum_modify_topicrow',
			'core.search_modify_rowset'				=> 'core_search_modify_rowset',
			'core.search_modify_tpl_ary'			=> 'core_search_modify_tpl_ary',
		];
	}

	public function core_search_modify_rowset(event $event)
	{
		$rowset = $event['rowset'];

		$archive_id = $this->config['marttiphpbb_archiveforum_id'];

		if (!$archive_id)
		{
			return;
		}

		$org_forums = [];

		foreach ($rowset as $data)
		{
			$forum_id = $data['forum_id'];

			if ($forum_id != $archive_id)
			{
				continue;
			}

			$org_forum_id = $data['marttiphpbb_archived_from_fid'];

			if (!$org_forum_id || $org_forum_id == $archive_id)
			{
				continue;
			}

			$org_forums[$org_forum_id] = true;

			if (!$this->auth->acl_get('f_list', $org_forum_id))
			{
				continue;
			}

			$this->topics_org_forums[$data['topic_id']] = $org_forum_id;
		}

		$this->forum_names = $this->get_forum_names_and_cleanup_deleted($org_forums);
	}

	public function core_search_modify_tpl_ary(event $event)
	{
		$row = $event['row'];
		$tpl_ary = $event['tpl_ary'];

		$event['tpl_ary'] = array_merge($tpl_ary, $this->get_row_template_vars($row));		
	}

	public function core_mcp_view_forum_modify_sql(event $event)
	{
		$forum_id = $event['forum_id'];
		$archive_id = $this->config['marttiphpbb_archiveforum_id'];

		if (!$archive_id || $forum_id != $archive_id)
		{
			return;
		}

		$sql = $event['sql'];
		$topics_per_page = $event['topics_per_page'];
		$start = $event['start'];

		$search = 't.topic_id';
		$replace = 't.topic_id, t.marttiphpbb_archived_from_fid';

		// only replace first instance, so no str_replace()
		$sql = substr_replace($sql, $replace, strpos($sql, $search), strlen($search));

		$result = $this->db->sql_query_limit($sql, $topics_per_page, $start);

		$org_forums = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_id = $row['topic_id'];
			$org_forum_id = $row['marttiphpbb_archived_from_fid'];

			if (!$org_forum_id || $org_forum_id == $archive_id)
			{
				continue;
			}

			$org_forums[$org_forum_id] = true;

			if (!$this->auth->acl_get('f_list', $org_forum_id))
			{
				continue;
			}			

			$this->topics_org_forums[$topic_id] = $org_forum_id;
		}
		$this->db->sql_freeresult($result);

		$this->forum_names = $this->get_forum_names_and_cleanup_deleted($org_forums);
	} 

	public function core_mcp_view_forum_modify_topicrow(event $event)
	{
		$row = $event['row'];
		$topic_row = $event['topic_row'];

		$event['topic_row'] = array_merge($topic_row, $this->get_row_template_vars($row));
	}

	public function core_viewforum_modify_topics_data(event $event)
	{
		$forum_id = $event['forum_id'];
		$archive_id = $this->config['marttiphpbb_archiveforum_id'];

		if (!$archive_id || $forum_id != $archive_id)
		{
			return;
		}

		$rowset = $event['rowset'];

		$org_forums = [];

		foreach ($rowset as $topic_id => $topic_data)
		{
			$org_forum_id = $topic_data['marttiphpbb_archived_from_fid'];

			if (!$org_forum_id || $org_forum_id == $archive_id)
			{
				continue;
			}

			$org_forums[$org_forum_id] = true;

			if (!$this->auth->acl_get('f_list', $org_forum_id))
			{
				continue;
			}

			$this->topics_org_forums[$topic_id] = $org_forum_id;
		}

		$this->forum_names = $this->get_forum_names_and_cleanup_deleted($org_forums);
	}

	public function core_viewforum_modify_topicrow(event $event)
	{
		$row = $event['row'];
		$topic_row = $event['topic_row'];
		$s_type_switch = $event['s_type_switch'];
		$s_type_switch_test = $event['s_type_switch_test'];

		if ($s_type_switch)
		{
			return;
		}

		if ($s_type_switch_test)
		{
			return;
		}

		$event['topic_row'] = array_merge($topic_row, $this->get_row_template_vars($row));
	}

	private function get_forum_names_and_cleanup_deleted(array $org_forums):array
	{
		if (!count($org_forums))
		{
			return [];
		}

		$forum_names = [];

		$sql = 'select forum_id, forum_name
			from ' . $this->forums_table . '
			where ' . $this->db->sql_in_set('forum_id', array_keys($org_forums));

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow())
		{
			unset($org_forums[$row['forum_id']]);
			$forum_names[$row['forum_id']] = $row['forum_name'];
		}

		$this->db->sql_freeresult($result);

		$deleted_forums = array_keys($org_forums);

		if (count($deleted_forums))
		{
			$sql = 'update ' . $this->topics_table . '
				set marttiphpbb_archived_from_fid = 0 
				where ' . $this->db->sql_in_set('marttiphpbb_archived_from_fid', $deleted_forums);
	
			$this->db->sql_query($sql);

			foreach($deleted_forums as $deleted_forum_id)
			{
				error_log('marttiphpbb/archiveforum: deleted forum with id ' . $deleted_forum_id . ' was removed from archive index');
			}
		}

		return $forum_names;
	}

	private function get_row_template_vars(array $row):array
	{
		$forum_id = $row['forum_id'];
		$topic_id = $row['topic_id'];
		$archive_id = $this->config['marttiphpbb_archiveforum_id'];

		if (!$archive_id || $forum_id != $archive_id)
		{
			return [];
		}

		if (!isset($this->topics_org_forums[$topic_id]))
		{
			return [];
		}

		$org_forum_id = $this->topics_org_forums[$topic_id];

		if (!isset($this->forum_names[$org_forum_id]))
		{
			return [];
		}

		return [
			'MARTTIPHPBB_ARCHIVEFORUM_NAME'	=> $this->forum_names[$org_forum_id],
			'U_MARTTIPHPBB_ARCHIVEFORUM'	=> append_sid($this->phpbb_root_path . 'viewforum.' . $this->php_ext, 'f=' . $org_forum_id),
		];		
	}
}
