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

class mcp_forum_listener implements EventSubscriberInterface
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
			'core.mcp_forum_view_before'
				=> 'core_mcp_forum_view_before',
			'core.modify_quickmod_actions'
				=> 'core_modify_quickmod_actions',
		];
	}

	public function core_mcp_forum_view_before(event $event)
	{
		$forum_info = $event['forum_info'];
		$forum_id = $forum_info['forum_id'];

		if (!$this->auth->acl_get('m_move', $forum_id))
		{
			return;
		}

		$archive_id = $this->config[cnst::CONFIG_ARCHIVE_ID];

		if (!$archive_id)
		{
			return;
		}

		$s_archive = $archive_id && $archive_id == $forum_id;

		$this->template->assign_vars([
			'S_MARTTIPHPBB_ARCHIVEFORUM_CAN_ARCHIVE' 	=> !$s_archive,
			'S_MARTTIPHPBB_ARCHIVEFORUM_CAN_RESTORE'	=> $s_archive,
		]);
	}

	public function core_modify_quickmod_actions(event $event)
	{
		$action = $event['action'];
		$quickmod = $event['quickmod'];

		if ($quickmod)
		{
			return;
		}

		if (!in_array($action,[
			cnst::ARCHIVE_ACTION, 
			cnst::RESTORE_ACTION,
		]))
		{
			return;
		}

		$this->language->add_lang('mcp', cnst::FOLDER);

		$archive_id = $this->config[cnst::CONFIG_ARCHIVE_ID];

		if (!$archive_id)
		{
			trigger_error('MCP_MARTTIPHPBB_ARCHIVEFORUM_NO_ARCHIVE_SET');
		}

		$s_archive = $action === cnst::ARCHIVE_ACTION;

		$topic_ids = $this->request->variable('topic_id_list', [0]);
		
		if (!count($topic_ids))
		{
			trigger_error('NO_TOPIC_SELECTED');
		}

		$org_forums = $omit_topics = [];
			
		$sql = 'select topic_id, topic_title, forum_id, ' . cnst::FROM_FORUM_ID_COLUMN . ' 
			from ' . $this->topics_table . '
			where ' . $this->db->sql_in_set('topic_id', $topic_ids);

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$archived_from_forum_id = $row[cnst::FROM_FORUM_ID_COLUMN];
			$forum_id = $row['forum_id'];
			$topic_id = $row['topic_id'];
			$topic_title = $row['topic_title'];
			$topic_url = append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ex, [
				'f' => $forum_id, 
				't' => $topic_id,
			]);		

			if (!$archived_from_forum_id)
			{
				$omit_topics[] = $topic_id;
				continue;
			}

			$org_forums[$forum_id][$topic_id] = $archived_from_forum_id;
 		}

		$this->db->sql_freeresult($result);
 
		trigger_error('OUFTI: ' . $action);  
	}



}
