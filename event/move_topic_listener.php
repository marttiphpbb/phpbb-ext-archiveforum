<?php
/**
* phpBB Extension - marttiphpbb Archive Forum
* @copyright (c) 2015 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\archiveforum\event;

use phpbb\event\data as event;
use phpbb\db\driver\factory as db;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class move_topic_listener implements EventSubscriberInterface
{
	/** @var db */
	private $db;

	/** @var string */
	private $topics_table;

	/**
	 * @param db
	 * @param string
	*/
	public function __construct(
		db $db, 
		string $topics_table
	)
	{
		$this->db = $db;
		$this->topics_table = $topics_table;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.move_topics_before_query'		=> 'core_move_topics_before_query',
		];
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
