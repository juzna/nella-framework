<?php

namespace Nella\Security;

/**
 * Credentials using OpenId
 *
 * @entity
 * @table(name="acl_user_credentials_openid")
 * @author Jan Dolecek <juzna.cz@gmail.com>
 *
 * @property string $openid
 */
class OpenIdEntity extends CredentialsEntity {
	/**
	 * @column
	 * @var string
	 */
	private $openid;

	public function __construct(IdentityEntity $identity, $openid = null) {
		parent::__construct($identity);
		$this->openid = $openid;
	}

	/**
	 * @param string $openid
	 */
	public function setOpenid($openid) {
		$this->openid = $openid;
	}

	/**
	 * @return string
	 */
	public function getOpenid() {
		return $this->openid;
	}
}
