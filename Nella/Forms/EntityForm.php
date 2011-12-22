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
				$isOfSupportedClass = FALSE;
				foreach ($supportedComponents as $class) {
					if ($component instanceof $class) {
						$isOfSupportedClass = TRUE;
						break;
					}
				}
				if (!$isOfSupportedClass) continue;

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
	 * Bind entity to this form and set default values from it
	 *
	 * @param \Nella\Models\IEntity $entity
	 * @throws \Nette\InvalidStateException
	 */
	public function bind(\Nella\Models\IEntity $entity)
	{
		if (!$this->isAnchored()) throw new \Nette\InvalidStateException("Form is not attached to a presenter, unable to set");
		if (!$this->isSubmitted()) $this->populateFormControls($entity);
	}


	/**
	 * Populate values from entity
	 *
	 * @param \Nella\Models\IEntity $object
	 */
	public function populateFormControls(\Nella\Models\IEntity $object)
	{
		foreach ($this->controls as /** @var \Nette\Forms\Controls\BaseControl $control */ $control) {
			if ($binding = $control->getDataBinding()) {
				$control->setValue($this->getEntityField($object, $binding));
			}
		}
	}

	/**
	 * Populate one field of entity with a given value
	 *
	 * @param \Nella\Models\IEntity $object
	 * @param string $property
	 * @return mixed
	 */
	protected function getEntityField(\Nella\Models\IEntity $object, $property)
	{
		$ident = '[a-zA-Z0-9_]+';

		if (preg_match("/^$ident$/i", $property)) { // simple property
			return $object->$property;

		} elseif (preg_match("/^($ident)(?:\\[($ident)\\])?(?:\\.(.+))?$/i", $property, $match)) { // property[index].x
			$baseName = $match[1];
			$index = isset($match[2]) ? $match[2] : null;
			$hasSubProperty = isset($match[3]);

			if ($hasSubProperty) { // recurse
				$subProperty = $match[3];

				$ref = $object->$baseName;
				if ($index !== null) $ref = $ref[$index];

				return $this->getEntityField($ref, $subProperty);
			} else { // no sub-property, assign directly to index
				if ($index === null) throw new \Nette\InvalidStateException('Should never happen!');
				return $object->$property[$index];
			}
		} else {
			return NULL; // Unknown
		}
	}


	/**
	 * Populate entity with data from this form
	 *
	 * @param \Nella\Models\IEntity $object
	 */
	public function populateEntity(\Nella\Models\IEntity $object)
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
	protected function populateEntityField(\Nella\Models\IEntity $object, $property, $value)
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
