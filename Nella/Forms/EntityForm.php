<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Nella\Forms;

/**
 * Nella entity forms
 *
 * @author	Patrik Votoček
 */
class EntityForm extends Form
{
	/**
	 * @param \Nella\Models\IEntity
	 * @param bool
	 * @return EntityForm
	 */
	public function setDefaults($entity, $erase = FALSE)
	{
		if ($entity instanceof \Nella\Models\IEntity) {
			$supportedComponents = array(
				'Nette\Forms\Controls\TextBase',
				'Nette\Forms\Controls\RadioList',
				'Nette\Forms\Controls\Checkbox',
				'Nette\Forms\Controls\Selectbox',
			);
			$arr = array();
			foreach ($this->getComponents() as $component) {
				foreach ($supportedComponents as $class) {
					if (!$component instanceof $class) {
						continue;
					}
				}
				$name = $component->getName();
				$method = 'get' . ucfirst($name);
				if (method_exists($entity, $method)) {
					if ($component instanceof \Nette\Forms\Controls\Selectbox) {
						$value = $entity->$method();
						$arr[$name] = $value ? (is_string($value) ? $value : $value->getId()) : NULL;
					} else {
						$arr[$name] = $entity->$method();
					}
				}
			}
		} else {
			$arr = $entity;
		}
		return parent::setDefaults($arr, $erase);
	}

	/**
	 * Populate entity with data from this form
	 *
	 * @param \Nella\Models\IEntity $object
	 */
	function populateEntity(\Nella\Models\IEntity $object)
	{
		foreach ($this->controls as /** @var \Nette\Forms\Controls\BaseControl $control */ $control) {
			if ($binding = $control->getDataBinding()) {
				$this->populateEntityField($object, $binding, $control->getValue());
			}
		}
	}

	/**
	 * Populate one field of entity with a given value
	 *
	 * @param \Nella\Models\IEntity $object
	 * @param string $property
	 * @param mixed $value
	 */
	function populateEntityField(\Nella\Models\IEntity $object, $property, $value)
	{
		$ident = '[a-zA-Z0-9_]+';

		if (preg_match("/^$ident$/i", $property)) { // simple property
			$object->$property = $value;

		} elseif (preg_match("/^($ident)(?:\\[($ident)\\])?(?:\\.(.+))?$/i", $property, $match)) { // property[index].x
			$baseName = $match[1];
			$index = isset($match[2]) ? $match[2] : null;
			$hasSubProperty = isset($match[3]);

			if ($hasSubProperty) { // recurse
				$subProperty = $match[3];

				$ref = $object->$baseName;
				if ($index !== null) $ref = $ref[$index];

				$this->populateEntityField($ref, $subProperty, $value);
			} else { // no sub-property, assign directly to index
				if ($index === null) throw new \Nette\InvalidStateException('Should never happen!');
				$object->$property[$index] = $value;
			}
		}
	}
}
