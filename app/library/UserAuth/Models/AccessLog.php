<?php

namespace UserAuth\Models;

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
	 * @var string
	 * @Primary
	 * @Column(type="string", length=39, nullable=false)
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

	public static function getLastAccessByIP($ip)
	{
		return self::findFirst([
					"conditions" => "ip = ?1",
					"order" =>	"first_attempt DESC",
					"bind"       => [
						1 => $ip
						]
				]);
	}

	public function getBlockTime()
	{
		return strtotime($this->last_attempt) - strtotime($this->first_attempt);
	}

	public function logAccessFailure($ip, $user_id = NULL)
	{
		$now = date('Y-m-d H:i:s');
		$this->user_id = 0;
		if (isset($user_id)) {
			$this->user_id = $user_id;
		}
		$this->ip = $ip;
		if ( ! isset($this->first_attempt)) {
			$this->first_attempt = $now;
		}
		$this->last_attempt = $now;
		$this->count += 1;
		$this->save();
	}

}
