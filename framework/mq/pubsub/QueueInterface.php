<?php

namespace yii\mq\pubsub;

use yii\mq\Message;

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
	 * @event Event an event that is triggered before a message is put in a subscription queue.
	 */
	const EVENT_BEFORE_PUT_SUBSCRIPTION = 'beforePutSubscription';
	/**
	 * @event Event an event that is triggered after a message is put in a subscription queue.
	 */
	const EVENT_AFTER_PUT_SUBSCRIPTION = 'afterPutSubscription';

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
	 * Determines if message can be put in the specified subscription queue.
	 * The default implementation should raise the {@link onBeforePutSubscription} event.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @param mixed $message the actual format depends on the implementation
	 * @param mixed $subscriber_id the actual format depends on the implementation
	 * @return boolean
	 */
    public function beforePutSubscription($message, $subscriber_id);
	/**
	 * Called after putting the message in a subscription queue. 
	 * The default implementation should raise the {@link onAfterPutSubscription} event.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @param mixed $message the actual format depends on the implementation
	 * @param mixed $subscriber_id the actual format depends on the implementation
	 */
    public function afterPutSubscription($message, $subscriber_id);
	/**
	 * Puts message to the queue. If there are any subscriptions, it will be delivered to those matching specified category.
	 *
	 * @param mixed $message the actual format depends on the implementation
	 * @param string $category category of the message (e.g. 'system.web'). It is case-insensitive.
	 */
	public function put($message, $category=null);
	/**
	 * Gets messages from the queue, but neither reserves or removes them.
	 * Messages are sorted by date and time of creation.
	 * @param mixed $subscriber_id the actual format depends on the implementation
	 * @param integer $limit number of available messages that will be fetched from the queue, defaults to -1 which means no limit
	 * @param integer|array $status allows peeking at reserved or removed messages (not yet permanently)
	 * @param boolean $blocking should this method wait until a message is available if the queue is empty
	 * @return yii\mq\Message[]
	 */
	public function peek($subscriber_id=null, $limit=-1, $status=Message::AVAILABLE, $blocking=false);
	/**
	 * Gets available messages from the queue and removes them from the queue.
	 * @param mixed $subscriber_id the actual format depends on the implementation
	 * @param integer $limit number of available messages that will be fetched from the queue, defaults to -1 which means no limit
	 * @param integer $timeout if not null and the message is not deleted after this much seconds it is returned to the queue
	 * @param boolean $blocking should this method wait until a message is available if the queue is empty
	 * @return yii\mq\Message[]
	 */
	public function pull($subscriber_id=null, $limit=-1, $timeout=null, $blocking=false);
	/**
	 * Deletes reserved messages from the queue.
	 * @param integer|array $message_id one or many message ids
	 * @param mixed $subscriber_id if not null, only this subscriber's messages will be affected, the actual format depends on the implementation
	 * @return integer|array one or more ids of deleted message, some could have timed out and had been released automatically
	 */
	public function delete($message_id, $subscriber_id=null);
	/**
	 * Releases reserved messages.
	 * @param integer|array $message_id one or many message ids
	 * @param mixed $subscriber_id if not null, only this subscriber's messages will be affected, the actual format depends on the implementation
	 * @return integer|array one or more ids of released message, some could have timed out and had been released automatically
	 */
	public function release($message_id, $subscriber_id=null);
	/**
	 * Releases all timed-out reserved messages.
	 * @return array of released message ids
	 */
	public function releaseTimedout();
	/**
	 * Subscribes a recipient to this queue. If categories are specified, only matching messages will be delivered.
	 * Categories can end with an wildcard (asterisk).
	 * @param mixed $subscriber_id the actual format depends on the implementation
	 * @param string $label optional, human readable label to distinguish subscriptions of the same user
	 * @param array $categories optional, list of categories of messages (e.g. 'system.web') that should be delivered to this subscription
	 * @param array $exceptions optional, list of categories of messages (e.g. 'system.web') that should NOT be delivered to this subscription
	 */
	public function subscribe($subscriber_id, $label=null, $categories=null, $exceptions=null);
	/**
	 * Unsubscribes a recipient from this queue.
	 * @param mixed $subscriber_id the actual format depends on the implementation
	 */
	public function unsubscribe($subscriber_id);
	/**
	 * Checkes if recipient is subscribed to this queue.
	 * @param mixed $subscriber_id the actual format depends on the implementation
	 * @return boolean
	 */
	public function isSubscribed($subscriber_id);
	/**
	 * Returns all subscriptions or one for specified subscriber, if it exists.
	 * @param mixed $subscriber_id
	 * @return yii\mq\Subscription[]
	 */
	public function getSubscriptions($subscriber_id=null);
}
