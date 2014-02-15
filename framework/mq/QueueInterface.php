<?php

namespace yii\mq;

interface QueueInterface
{
	/**
	 * @event Event an event that is triggered before a message is put in a queue.
	 */
	const EVENT_BEFORE_PUT = 'beforePut';
	/**
	 * @event Event an event that is triggered after a message is put in a queue.
	 */
	const EVENT_AFTER_PUT = 'afterPut';

	/**
	 * Determines if message can be put in a queue.
	 * The default implementation should raise the {@link onBeforePut} event.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @param mixed $message the actual format depends on the implementation
	 * @return boolean
	 */
    public function beforePut($message);
	/**
	 * Called after putting the message in the queue. 
	 * The default implementation should raise the {@link onAfterPut} event.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @param mixed $message the actual format depends on the implementation
	 */
    public function afterPut($message);
	/**
	 * Puts message to the queue.
	 *
	 * @param mixed $message the actual format depends on the implementation
	 */
	public function put($message);
	/**
	 * Gets messages from the queue, but neither reserves or removes them.
	 * Messages are sorted by date and time of creation.
	 * @param integer $limit number of available messages that will be fetched from the queue, defaults to -1 which means no limit
	 * @param integer|array $status allows peeking at reserved or removed messages (not yet permanently)
	 * @param boolean $blocking should this method wait until a message is available if the queue is empty
	 * @return Message[]
	 */
	public function peek($limit=-1, $status=Message::AVAILABLE, $blocking=false);
	/**
	 * Gets available messages from the queue and removes them from the queue.
	 * @param integer $limit number of available messages that will be fetched from the queue, defaults to -1 which means no limit
	 * @param integer $timeout if not null and the message is not deleted after this much seconds it is returned to the queue
	 * @param boolean $blocking should this method wait until a message is available if the queue is empty
	 * @return Message[]
	 */
	public function pull($limit=-1, $timeout=null, $blocking=false);
	/**
	 * Deletes reserved messages from the queue.
	 * @param integer|array $message_id one or many message ids
	 * @return integer|array one or more ids of deleted message, some could have timed out and had been released automatically
	 */
	public function delete($message_id);
	/**
	 * Releases reserved messages.
	 * @param integer|array $message_id one or many message ids
	 * @return integer|array one or more ids of released message, some could have timed out and had been released automatically
	 */
	public function release($message_id);
	/**
	 * Releases all timed-out reserved messages.
	 * @return array of released message ids
	 */
	public function releaseTimedout();
}
