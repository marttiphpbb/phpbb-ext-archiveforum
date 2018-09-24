<?php
/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\event;

use phpbb\event\data as event;
use phpbb\config\config;
use marttiphpbb\archiveforum\util\cnst;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class calendarautoarchive_listener implements EventSubscriberInterface
{
	protected $config;

	public function __construct(
		config $config
	)
	{
		$this->config = $config;
	}

	static public function getSubscribedEvents()
	{
		return [
			'marttiphpbb.calendarautoarchive.get_archive_id'
				=> 'get_archive_id',
		];
	}

	public function get_archive_id(event $event)
	{
		$archive_id = $this->config[cnst::CONFIG_ARCHIVE_ID];

		$event['archive_id'] = $archive_id;
	}
}
