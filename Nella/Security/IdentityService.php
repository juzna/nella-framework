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
 * Identity model servicem
 *
 * @author	Patrik Votoček
 */
class IdentityService extends \Nella\Doctrine\Service
{
	/**
	 * @param array|\Traversable
	 * @param bool
	 * @return \Nella\Models\IEntity
	 * @throws \Nette\InvalidArgumentException
	 */
	public function create($values, $withoutFlush = FALSE)
	{
		try {
			$em = $this->getEntityManager();

			if (!$values['role'] instanceof \Nella\Security\RoleEntity) {
				$roleService = $this->getContainer()->getService(RoleEntity::getClassName());
				$values['role'] = $roleService->repository->find($values['role']);
				if (!$values['role']) throw new \InvalidArgumentException("Given role does not exist");
			}

			if (!empty($values['username']) && !empty($values['password'])) {
				$credService = $this->getContainer()->getService(PasswordEntity::getClassName());
				$cred = $credService->create($values, TRUE);
				$em->persist($cred);
			}

			$entity = parent::create($values, TRUE);
			$em->persist($entity);
			if (!$withoutFlush) {
				$em->flush();
			}
			return $entity;
		} catch (\PDOException $e) {
			$this->processPDOException($e);
		}
	}
}
