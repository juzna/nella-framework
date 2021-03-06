<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */

namespace Nella;

/**
 * Freezable array object
 *
 * @author	Patrik Votoček
 */
class FreezableArray extends FreezableObject implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/** @var array */
	private $list = array();

	/**
 	 * @param  array to wrap
 	 * @param  bool
	 * @return FreezableArray
 	 */
 	public static function from($arr, $recursive = TRUE)
 	{
		$obj = new static;
 		foreach ($arr as $key => $value) {
 			if ($recursive && is_array($value)) {
 				$obj->list[$key] = static::from($value, TRUE);
 			} else {
 				$obj->list[$key] = $value;
 			}
 		}
 		return $obj;
 	}

	/**
	 * Returns an iterator over all items
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->list);
	}

	/**
	 * Returns an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = array();
		foreach ($this->list as $key => $value) {
			if ($value instanceof static) {
				$arr[$key] = $value->toArray();
			} else {
				$arr[$key] = $value;
			}
		}
		return $arr;
	}

	/**
	 * Returns items count
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->list);
	}

	/**
	 * Replaces or appends a item
	 *
	 * @param  string
	 * @param  mixed
	 * @return FreezableArray
	 */
	public function offsetSet($key, $value)
	{
		$this->updating();
		$this->list[$key] = $value;
		return $this;
	}

	/**
	 * Returns a item
	 *
	 * @param string
	 * @return mixed
	 * @throws \Nette\MemberAccessException
	 */
	public function offsetGet($key)
	{
		if (!$this->offsetExists($key)) {
			$class = get_called_class();
			throw new \Nette\MemberAccessException("Cannot read an undeclared item {$class}['{$key}'].");
		}
		return $this->list[$key];
	}

	/**
	 * Determines whether a item exists
	 *
	 * @param string
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->list);
	}

	/**
	 * Removes the element at the specified position in this list
	 *
	 * @param string
	 * @return FreezableArray
	 */
	public function offsetUnset($key)
	{
		$this->updating();
		unset($this->list[$key]);
		return $this;
	}
}
