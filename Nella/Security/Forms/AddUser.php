<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */

namespace Nella\Security\Forms;

/**
 * Add user form
 *
 * @author	Patrik Votoček
 */
class AddUser extends \Nella\Forms\EntityForm
{
	public $successLink = ":Security:Backend:";

	protected function setup()
	{
		parent::setup();

		$roles = $this->getDoctrineContainer()->getService(\Nella\Security\RoleEntity::getClassName())->repository->fetchPairs('id', "name");

		$this->addText('displayName', 'Name')->setRequired()->bind('displayName');
		$this->addText('username', "Username")->setRequired()->bind('credentials[0].username');
		$this->addEmail('email', "E-mail")->setRequired()->bind('email');
		$this->addPassword('password', "Password")->bind('credentials[0].password');
		$this->addPassword('password2', "Re-Password")->addCondition(static::FILLED)
			->addRule(static::EQUAL, NULL, $this['password']);
		$this->addMultiSelect('roles', "Roles", $roles);//->bind('roles');
		$this->addSelect('lang', "Lang", array('en' => "English"))->setRequired()->bind('lang'); // @todo

		$this->addSubmit('sub', "Add");

		$this->onSuccess[] = callback($this, 'process');
	}


	public function process()
	{
		// Create empty model
		$user = new \Nella\Security\IdentityEntity;
		$user->addCredentials($cred = new \Nella\Security\PasswordEntity($user));

		// Populate it
		$this->populateEntity($user);

		// Store roles
		$roleRepository = $this->getDoctrineContainer()->getService(\Nella\Security\RoleEntity::getClassName())->getRepository();
		foreach ($this['roles']->getValue() as /** @var int $role */ $role) {
			$user->addRole($roleRepository->find($role));
		}

		try {
			$em = $this->getDoctrineContainer()->getEntityManager();
			$em->persist($user);
			$em->persist($cred);
			$em->flush();

			$presenter = $this->getPresenter();
			$presenter->logAction("Security", \Nella\Utils\IActionLogger::CREATE, "Created user '{$user->displayName}'");
			$presenter->flashMessage(__("User '%s' successfully added", $user->displayName), 'success');
			$presenter->redirect($this->successLink);

		} catch (\Nella\Models\InvalidEntityException $e) { // FIXME: will not get caught because PDOException is not mapped
			$this->processErrors($e->getErrors());
		} catch (\Nella\Models\DuplicateEntryException $e) {
			$this['username']->addError("Username %value already exist");
		}
	}
}
