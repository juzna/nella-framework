<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Nella\Security;

/**
 * Identity entity
 *
 * @author	Patrik Votoček
 * @author  Jan Dolecek
 *
 * @entity
 * @table(name="acl_users")
 * @service(class="Nella\Security\IdentityService")
 * @hasLifecycleCallbacks
 *
 * @property string $lang
 * @property string $displayName
 * @property string $email
 * @property RoleEntity[] $roleEntities
 * @property string[] $roles
 * @property CredentialsEntity[] $credentials
 */
class IdentityEntity extends \Nette\Object implements \Nella\Models\IEntity, \Nette\Security\IIdentity, \Serializable
{
	/**
	 * @id
	 * @generatedValue
	 * @column(type="integer")
	 */
	private $id;

	/**
	 * @column(length=5)
	 * @var string
	 */
	private $lang;

	/**
	 * @column
	 * @var string
	 */
	private $displayName;

	/**
	 * @column
	 * @var string
	 */
	private $email;

	/**
	 * @manyToMany(targetEntity="RoleEntity", fetch="EAGER")
	 * @joinTable(name="acl_user_roles")
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	private $roles;

	/**
	 * @oneToMany(targetEntity="CredentialsEntity", mappedBy="identity")
	 */
	private $credentials;

	/**
	 * @internal
	 * @var bool
	 */
	private $loaded = FALSE;


	public function __construct() {
		$this->roles = new \Doctrine\Common\Collections\ArrayCollection;
		$this->credentials = new \Doctrine\Common\Collections\ArrayCollection;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return RoleEntity[]
	 */
	public function getRoleEntities()
	{
		return $this->roles;
	}

	/**
	 * @param RoleEntity
	 * @return IdentityEntity
	 */
	public function addRole(RoleEntity $role)
	{
		$this->roles[] = $role;
		return $this;
	}

	/**
	 * @internal
	 * @return array
	 */
	public function getRoles()
	{
		return array_map(function(RoleEntity $role) {
			return $role->name;
		}, $this->roles->toArray());
	}

	/**
	 * @return string
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * @param string
	 * @return IdentityEntity
	 */
	public function setLang($lang)
	{
		$this->lang = $this->sanitizeString($lang);
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getDisplayName()
	{
		return $this->displayName;
	}
	
	/**
	 * @param string
	 * @return IdentityEntity
	 */
	public function setDisplayName($displayName)
	{
		$this->displayName = $this->sanitizeString($displayName);
		return $this;
	}

	/**
	 * @param string $email
	 * @return IdentityEntity
	 */
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function serialize()
	{
		return serialize($this->getId());
	}

	/**
	 * @param string
	 * @throws \Nette\InvalidStateException
	 */
	public function unserialize($serialized)
	{
		$this->id = unserialize($serialized);
		$this->loaded = FALSE;
	}

	/**
	 * @param \Nella\Doctrine\Container
	 * @return IdentityEntity
	 */
	public function load(\Nella\Doctrine\Container $container)
	{
		if (!$this->loaded) {
			$service = $container->getService(__CLASS__);
			$entity = $service->repository->find($this->getId());
			$entity->loaded = TRUE;
			return $entity;
		} else {
			return $this;
		}
	}

	/**
	 * @param string
	 * @return string
	 */
	protected function sanitizeString($s)
	{
		$s = trim($s);
		return $s === "" ? NULL : $s;
	}

	/**
	 * @return CredentialsEntity[]
	 */
	public function getCredentials() {
		return $this->credentials;
	}

	public function addCredentials(CredentialsEntity $cred) {
		$this->credentials[] = $cred;
	}

	public function removeCredentials(CredentialsEntity $cred) {
		$this->credentials->removeElement($cred);
	}

	/**
	 * @param CredentialsEntity[] $cred
	 */
	public function setCredentials(array $cred) {
		$this->credentials = new \Doctrine\Common\Collections\ArrayCollection($cred);
	}
}
