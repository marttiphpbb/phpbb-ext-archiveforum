<?php
/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\event;

use phpbb\event\data as event;
use phpbb\db\driver\factory as db;
use phpbb\request\request;
use phpbb\language\language;
use marttiphpbb\archiveforum\service\store;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class acp_listener implements EventSubscriberInterface
{
	/** @var db **/
	private $db;

	/** @var request */
	private $request;

	/** @var language */
	private $language;

	/** @var store */
	private $store;

	/** @var string */
	private $forums_table;

	/**
	* @param db	
	* @param request
	* @param language
	* @param store
	* @param string
	*/
	public function __construct(
			db $db,
			request $request,
			language $language,
			store $store,
			string $forums_table
	)
	{
		$this->db = $db;
		$this->request = $request;
		$this->language = $language;
		$this->store = $store;
		$this->forums_table = $forums_table;
	}

	static public function getSubscribedEvents()
	{
		return [			
			'core.acp_manage_forums_initialise_data'	=> 'core_acp_manage_forums_initialise_data',
			'core.acp_manage_forums_update_data_after'	=> 'core_acp_manage_forums_update_data_after',
			'core.acp_manage_forums_display_form'		=> 'core_acp_manage_forums_display_form',
		];
	}

	public function core_acp_manage_forums_initialise_data(event $event)
	{

	}

	public function core_acp_manage_forums_update_data_after(event $event)
	{
		$forum_data = $event['forum_data'];
		$forum_id = $forum_data['forum_id'];

		$topic_template = utf8_normalize_nfc($this->request->variable('forum_marttiphpbb_archiveforum', '', true));
		$this->store->set_template($forum_id, $topic_template);
	}

	public function core_acp_manage_forums_display_form(event $event)
	{
		$action = $event['action'];
		$forum_id = $event['forum_id'];
		$template_data = $event['template_data'];

		$topic_template = $action === 'add' ? '' : $this->store->get_template($forum_id);

		$template_data['FORUM_MARTTIPHPBB_ARCHIVEFORUM'] = $topic_template;

		$event['template_data'] = $template_data;

		$this->language->add_lang('acp', 'marttiphpbb/archiveforum');
	}
}
