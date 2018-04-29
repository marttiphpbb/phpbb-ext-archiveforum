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

		$archive_id = $this->config['marttiphpbb_archiveforum_id'];

		$s_archive = $archive_id && $archive_id == $forum_id;

		$this->template->assign_vars([
			'S_MARTTIPHPBB_ARCHIVEFORUM_CAN_ARCHIVE' 	=> !$s_archive,
			'S_MARTTIPHPBB_ARCHIVEFORUM_CAN_RESTORE'	=> $s_archive,
		]);
	}



}
