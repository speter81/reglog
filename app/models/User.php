<?php

use Phalcon\Mvc\Model\Validator\Email as Email;

class User extends \Phalcon\Mvc\Model
{

	/**
	 *
	 * @var integer
	 * @Primary
	 * @Identity
	 * @Column(type="integer", length=10, nullable=false)
	 */
	public $id;

	/**
	 *
	 * @var string
	 * @Column(type="string", length=24, nullable=false)
	 */
	public $email;

	/**
	 *
	 * @var string
	 * @Column(type="string", length=40, nullable=false)
	 */
	public $password;

	/**
	 *
	 * @var string
	 * @Column(type="string", length=64, nullable=false)
	 */
	public $name;

	/**
	 * Validations and business logic
	 *
	 * @return boolean
	 */
	public function validation()
	{
		$this->validate(
			new Email(
				[
					'field'    => 'email',
					'required' => true,
				]
			)
		);

		if ($this->validationHasFailed() == true) {
			return false;
		}

		return true;
	}

	/**
	 * Returns table name mapped in the model.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return 'users';
	}

	/**
	 * Allows to query a set of records that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return Users[]
	 */
	public static function find($parameters = null)
	{
		return parent::find($parameters);
	}

	/**
	 * Allows to query the first record that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return Users
	 */
	public static function findFirst($parameters = null)
	{
		return parent::findFirst($parameters);
	}

	public function isLoggedIn()
	{
		if ($this->email) {
			return true;
		}
		return false;
	}

	public function getName()
	{
		return $this->name;
	}

}
