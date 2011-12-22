<?php

namespace Nella\Security;

/**
 * Identity credentials entity
 *
 * @entity
 * @table(name="acl_user_credentials")
 * @inheritanceType("JOINED")
 * @discriminatorColumn(name="cred_type", type="string")
 * @discriminatorMap({"base" = "CredentialsEntity", "pass" = "PasswordEntity", "openid" = "OpenIdEntity"})
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 *
 * @property IdentityEntity $identity
 */
class CredentialsEntity extends \Nella\Doctrine\Entity {
	/**
	 * @manyToOne(targetEntity="IdentityEntity", inversedBy="credentials", fetch="EAGER")
	 * @var \Nella\Security\IdentityEntity
	 */
	private $identity;


	public function __construct(IdentityEntity $identity) {
		parent::__construct();
		$this->identity = $identity;
	}

	/**
	 * @return \Nella\Security\IdentityEntity
	 */
	public function getIdentity() {
		return $this->identity;
	}
}
