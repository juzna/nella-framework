<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Nella\Forms\Controls;

/**
 * Form date field item
 *
 * @internal
 *
 * @author	Patrik Votoček
 *
 * @property \DateTime $value
 */
abstract class BaseDateTime extends \Nette\Forms\Controls\TextInput
{
	/** @var string */
	public static $format = "Y-n-j";

	/** @var array */
	public static $formatPhpToJs = array(
		'd' => "dd",
		'j' => "d",
		'm' => "mm",
		'n' => "m",
		//'z' => "oo", ???
		'z' => "o",
		'Y' => "yy",
		'y' => "y",
		'U' => "@",
		'h' => "h",
		'H' => "hh",
		'g' => "g",
		'A' => "TT",
		'i' => "mm",
		's' => "ss",
		'G' => "h",
	);

	/**
	 * @param string
	 * @return string
	 */
	protected function translateFormatToJs($format)
	{
		return str_replace(array_keys(static::$formatPhpToJs), array_values(static::$formatPhpToJs), $this->translate($format));
	}

	/**
	 * @return \DateTime|NULL
	 */
	public function getValue()
	{
		$value = parent::getValue();
		$value = \DateTime::createFromFormat(static::$format, $value);
		$err = \DateTime::getLastErrors();
		if ($err['error_count']) {
			$value = FALSE;
		}
		return $value ?: NULL;
	}

	/**
	 * @param \DateTime
	 * @return BaseDateTime
	 */
	public function setValue($value = NULL)
	{
		try {
			if ($value instanceof \DateTime) {
				return parent::setValue($value->format(static::$format));
			} else {
				return parent::setValue($value);
			}
		} catch (\Exception $e) {
			return parent::setValue(NULL);
		}
	}

	/**
	 * @param BaseDateTime
	 * @return bool
	 */
	public static function validateValid(\Nette\Forms\IControl $control)
	{
		$value = $control->getValue();
		return (is_null($value) || $value instanceof \DateTime);
	}

	/**
	 * @return bool
	 */
	public function isFilled()
	{
		return (bool) $this->getValue();
	}
}