<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */

namespace Nella\Security;

/**
 * Identity credentials entity
 *
 * @entity
 * @table(name="acl_user_credentials_pass")
 *
 * @author	Patrik Votoček
 *
 * @property string $username
 * @property string $password
 */
class PasswordEntity extends CredentialsEntity
{
	const PASSWORD_DELIMITER = "$";

	/**
	 * @column(length=128, unique=true)
	 * @var string
	 */
	private $username;

	/**
	 * @column(length=256)
	 * @var string
	 */
	private $password;


	public function __construct(IdentityEntity $identity, $username, $pass) {
		parent::__construct($identity);
		$this->setUsername($username);
		$this->setPassword($pass);
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param string
	 * @return IdentityEntity
	 */
	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}


	/**
	 * @param bool return as string
	 * @return string
	 */
	public function getPassword($string = TRUE)
	{
		if ($string || !$this->password) {
			return $this->password;
		}

		return explode(self::PASSWORD_DELIMITER, $this->password);
	}

	/**
	 * @param string
	 * @param string
	 * @return IdentityEntity
	 */
	public function setPassword($password, $algo = "sha256")
	{
		$salt = \Nette\Utils\Strings::random();

		$this->password = $algo . self::PASSWORD_DELIMITER;
		$this->password .= $salt . self::PASSWORD_DELIMITER;
		$this->password .= hash($algo, $salt . $password);

		return $this;
	}

	/**
	 * @param string plaintext password
	 * @return bool
	 */
	public function verifyPassword($password)
	{
		list($algo, $salt, $hash) = $this->getPassword(FALSE);
		if (hash($algo, $salt . $password) == $hash) {
			return TRUE;
		}

		return FALSE;
	}
}
