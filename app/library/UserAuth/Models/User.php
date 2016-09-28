<?php

namespace UserAuth\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

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
		$validator = new Validation();
		$validator->add(
			'email',
			new EmailValidator()
		);

		return $this->validate($validator);
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

	public function getUserId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getEmail()
	{
		return $this->email;;
	}

	public function setEmail($email)
	{
		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		$this->email = $email;
		return true;
	}

	public function setPassword($password)
	{
		if (strlen($password) < 8) {
			return false;
		}
		if ( ! preg_match('/[A-Z]+/', $password)) {
			return false;
		}
		if ( ! preg_match('/[a-z]+/', $password)) {
			return false;
		}
		if ( ! preg_match('/[0-9]/', $password)) {
			return false;
		}
		if ( ! preg_match('/[\+\-!_#\$]+/', $password)) {
			return false;
		}
		$security = $this->getDI()->getSecurity();
		$this->password = $security->hash($password);
		return true;
	}

	public function setName($name)
	{
		$this->name = filter_var($name, FILTER_SANITIZE_STRING);
		return true;
	}

	public function setUserDetails($name, $email, $password, $passwordVerify)
	{
		if ($password != $passwordVerify) {
			return 'Passwords do not match!';
		}
		if ( ! $this->setEmail($email)) {
			return 'Invalid email address!';
		}

		if ( ! $this->setPassword($password)) {
			return 'Provided password is too weak!';
		}

		if ( ! $this->setName($name)) {
			return 'Name contains invalid characters';
		}

		return true;
	}
}
