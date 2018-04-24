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
		];
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
