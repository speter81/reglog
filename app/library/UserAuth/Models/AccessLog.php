<?php

namespace UserAuth\Models;

use \Phalcon\Mvc\Model\Query;

class AccessLog extends \Phalcon\Mvc\Model
{

	/**
	 *
	 * @var integer
	 * @Column(type="integer", length=10, nullable=false)
	 */
	public $user_id;

	/**
	 *
	 * @var integer
	 * @Primary
	 * @Column(type="integer", length=10, nullable=false)
	 */
	public $ip;

	/**
	 *
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	public $first_attempt;

	/**
	 *
	 * @var string
	 * @Column(type="string", nullable=false)
	 */
	public $last_attempt;

	/**
	 *
	 * @var integer
	 * @Column(type="integer", length=11, nullable=false)
	 */
	public $expire_time;

	/**
	 *
	 * @var integer
	 * @Column(type="integer", length=5, nullable=false)
	 */
	public $count;

	/**
	 * Returns table name mapped in the model.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return 'access_log';
	}

	/**
	 * Allows to query a set of records that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return AccessLog[]
	 */
	public static function find($parameters = null)
	{
		return parent::find($parameters);
	}

	/**
	 * Allows to query the first record that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return AccessLog
	 */
	public static function findFirst($parameters = null)
	{
		return parent::findFirst($parameters);
	}

	private function getLastAttemptFromIP($longIp)
	{
		return self::findFirst([
			'conditions' => 'ip = ?1 AND ?2 < last_attempt',
			'bind' => [
				1 => $longIp,
				2 => date('Y-m-d H:i:s', strtotime('-5 minutes', time()))
			]
		]);
	}

	public function logAccessFailure($ip, $type = 32, $user_id = 0)
	{
		$longIp = ip2long($ip);
		$time = time();
		$now = date('Y-m-d H:i:s', $time);
		$accessLog = $this->getLastAttemptFromIP($longIp);
		if ( ! $accessLog) {
			$accessLog = $this;
		}
		$accessLog->user_id = $user_id;
		$accessLog->ip = $longIp;

		if ( ! isset($accessLog->first_attempt)) {
			$accessLog->first_attempt = $now;
			$accessLog->expire_time = $time + 3600;
			$accessLog->count = 1;
		} else {
			$accessLog->count++;
		}
		$accessLog->last_attempt = $now;
		$accessLog->save();
	}

	public static function getFailedAttemptsFromIP($ip)
	{
		$sum = AccessLog::sum([
			'column' => 'count',
			'conditions' => "ip = ?0 AND expire_time < NOW()",
			'bind' => [
				ip2long($ip)
			]
		]);
		return $sum;
	}

	public static function getFailedAttemptsFromNetwork($ip, $cidr)
	{
		$sum = AccessLog::sum([
			'column' => 'count',
			'conditions' => "ip = ?0 AND expire_time < NOW()",
			'bind' => [
				static::getBroadcastFromNetwork($ip, $cidr)
			]
		]);
		return $sum;
	}

	public static function getFailedAttemptsForUser($userId)
	{
		$sum = AccessLog::sum([
			'column' => 'count',
			'conditions' => 'expire_time < NOW() AND `user_id` = ?0',
			'bind' => [
				$userId
			]
		]);
		return $sum;
	}

	private static function getBroadcastFromNetwork($network, $cidr)
	{
		return long2ip(ip2long($network) + pow(2, (32 - $cidr)) - 1);
	}
}
