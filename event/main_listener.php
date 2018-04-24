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

class main_listener implements EventSubscriberInterface
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
			'core.user_setup'					=> 'core_user_setup',
			'core.viewforum_modify_topics_data'	=> 'core_viewforum_modify_topics_data',
		];
	}

	public function core_viewforum_modify_topics_data(event $event)
	{
		$forum_id = $event['forum_id'];

		if (!$this->config['marttiphpbb_archiveforum_id']
			|| $forum_id !== $this->config['marttiphpbb_archiveforum_id'])
		{
			return;
		}

		$rowset = $event['rowset'];
		$archive_id = $this->config['marttiphpbb_archiveforum_id'];
		$topics_archived = [];
		$org_forums = [];
		$forum_names = [];

		foreach ($rowset as $topic_id => $topic_data)
		{
			if (!$topic_data['marttiphpbb_archived_from_fid']
				|| $topic_data['marttiphpbb_archived_from_fid'] === $archive_id)
			{
				continue;
			}

			$org_forum_id = $topic_data['marttiphpbb_archived_from_fid'];

			$org_forums[$org_forum_id] = true;

			if (!$this->auth->acl_get('f_list', $org_forum_id))
			{
				continue;
			}

			$topics_archived[$org_forum_id][] = $topic_id;
		}

		$sql = 'select forum_id, forum_name
			from ' . FORUMS_TABLE . '
			where ' . $this->db->sql_in_set('forum_id', array_keys($org_forums));

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->fetchrow('forum_id'))
		{
			unset($org_forums[$row['forum_id']]);
			$forum_names[$row['forum_id']] = $row['forum_name'];
		}

		$this->db->sql_freeresult($result);

		$deleted_forums = array_keys($org_forums);

		if (count($deleted_forums))
		{
			$sql = 'update ' . TOPICS_TABLE . '
				set marttiphpbb_archived_from_fid = 0 
				where ' . $this->db->sql_in_set('marttiphpbb_archived_from_fid', $deleted_forums);
	
			$result = $this->db->sql_query($sql);

			foreach($deleted_forums as $deleted_forum_id)
			{
				unset($topics_archived[$deleted_forum_id]);

				error_log('marttiphpbb/archiveforum: deleted forum with id ' . $deleted_forum_id . ' was removed from archive index');
			}
		}

		foreach ($topics_archived as $org_forum_id => $topic_ids)
		{
			foreach ($topic_ids as $topic_id)
			{
				$forum_names[$org_forum_id];
			}
		}




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
}
