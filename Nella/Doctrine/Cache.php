<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Nella\Doctrine;

/**
 * Nette cache driver for doctrine
 *
 * @author	Patrik Votoček
 */
class Cache implements \Doctrine\Common\Cache\Cache
{
	/** @var \Nette\Caching\Cache */
	private $data = array();

	/**
	 * @param \Nette\Caching\IStorage
	 */
	public function  __construct(\Nette\Caching\IStorage $cacheStorage)
	{
		$this->data = new \Nette\Caching\Cache($cacheStorage, "Nella.Doctrine");
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIds()
	{
		throw new \Nette\NotImplementedException; // wait fot $cache->getIds() in Nette\Caching\Cache
	}

    /**
     * {@inheritdoc}
     */
	public function fetch($id)
    {
        $data = $this->data->load($id);
		return $data ?: FALSE;
    }

    /**
     * {@inheritdoc}
     */
	public function contains($id)
    {
        return $this->data->load($id) !== NULL;
    }

    /**
     * {@inheritdoc}
     */
	public function save($id, $data, $lifeTime = 0)
    {
		$files = array();
		if ($data instanceof \Doctrine\ORM\Mapping\ClassMetadata) {
			$ref = \Nette\Reflection\ClassType::from($data->name);
			$files[] = $ref->getFileName();
			foreach ($data->parentClasses as $class) {
				$ref = \Nette\Reflection\ClassType::from($class);
				$files[] = $ref->getFileName();
			}
		}

		if ($lifeTime != 0) {
			$this->data->save($id, $data, array('expire' => time() + $lifeTime, 'tags' => array("doctrine"), 'files' => $files));
		} else {
			$this->data->save($id, $data, array('tags' => array("doctrine"), 'files' => $files));
		}

		return TRUE;
    }

    /**
     * {@inheritdoc}
     */
	public function delete($id)
    {
        $this->data->save($id, NULL);
        return TRUE;
    }

	/**
	 * Retrieves cached information from data store
	 *
	 * The server's statistics array has the following values:
	 *
	 * - <b>hits</b>
	 * Number of keys that have been requested and found present.
	 *
	 * - <b>misses</b>
	 * Number of items that have been requested and not found.
	 *
	 * - <b>uptime</b>
	 * Time that the server is running.
	 *
	 * - <b>memory_usage</b>
	 * Memory used by this server to store items.
	 *
	 * - <b>memory_available</b>
	 * Memory allowed to use for storage.
	 *
	 * @since   2.2
	 * @var     array Associative array with server's statistics if available, NULL otherwise.
	 */
	function getStats()
	{
		// TODO: Implement getStats() method.
	}
}
