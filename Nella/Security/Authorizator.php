<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Nella\Security;

use Nette\Reflection;

/**
 * Simple authorizator implementation
 *
 * @author	Patrik Votoček
 */
class Authorizator extends \Nette\Security\Permission
{
	const ROLE = 'role';
	const RESOURCE = 'resource';
	const PRIVILEGE = 'privilege';

	public $defaultResources = array('dashboard', 'security');

	/**
	 * @param \Nella\Doctrine\Container
	 */
	public function __construct(\Nella\Doctrine\Container $container)
	{
		$service = $container->getService('Nella\Security\RoleEntity');
		$roles = $service->repository->findAll();

		foreach ($this->defaultResources as $resource) {
			$this->addResource($resource);
		}

		foreach ($roles as $role) {
			$this->addRole($role->name);
			foreach ($role->permissions as $permission) {
				if ($permission->resource && !$this->hasResource($permission->resource)) {
					$this->addResource($permission->resource);
				}

				if ($permission->allow) {
					$this->allow($role->name, $permission->resource, $permission->privilege);
				} else {
					$this->deny($role->name, $permission->resource, $permission->privilege);
				}
			}
		}
	}

	/**
	 * @param string|\Reflector
	 * @param string
	 * @return array
	 */
	public static function parseAnnotations($class, $method = NULL)
	{
		/** @var \Nette\Reflection\ClassType|\Nette\Reflection\Method $pRef */

		// First, find reflector and it's parent
		if ($class instanceof \Reflector) { // reflector given, check what it is
			$what = $class;

			if ($what instanceof \ReflectionMethod) {
				$ref = ($what instanceof Reflection\Method) ? $what : new Reflection\Method($what);
				$pRef = $ref->getDeclaringClass();
			} elseif ($what instanceof \ReflectionClass) {
				$ref = $what;
				$pRef = null;
			} else throw new \Nette\InvalidArgumentException;

		} elseif (is_string($class)) { // we have method name
			if (strpos($class, '::') !== FALSE && !$method) {
				list($class, $method) = explode('::', $class);
			}

			if (empty($method)) {
				$ref = new Reflection\ClassType($class);
				$pRef = null;
			} else {
				$ref = new Reflection\Method($class, $method);
				$pRef = $ref->getDeclaringClass();
			}
		} else throw new \Nette\InvalidArgumentException;

		$annotations = (array) $ref->getAnnotation('allowed');

		$ret = array();
		foreach (array(static::ROLE, static::RESOURCE, static::PRIVILEGE) as $type) {
			if (isset($annotations[$type])) $ret[$type] = $annotations[$type];
			elseif ($ref && $ref->hasAnnotation($type)) $ret[$type] = $ref->getAnnotation($type);
			elseif ($pRef && $pRef->hasAnnotation($type)) $ret[$type] = $pRef->getAnnotation($type);
			else $ret[$type] = null;
		}

		return $ret;
	}
}
