<?php

/**
 * This file is part of the Nextras\ORM library.
 *
 * @license    MIT
 * @link       https://github.com/nextras/orm
 * @author     Jan Skrasek
 */

namespace Nextras\Orm\Relationships;

use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Entity\IPropertyContainer;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\InvalidArgumentException;


abstract class HasOne implements IPropertyContainer
{
	/** @var IEntity */
	protected $parent;

	/** @var PropertyMetadata */
	protected $propertyMeta;

	/** @var mixed */
	protected $primaryValue;

	/** @var string */
	protected $primaryKey;

	/** @var IEntity|NULL|bool */
	protected $value = FALSE;


	public function __construct(IEntity $parent, PropertyMetadata $propertyMeta, $value)
	{
		$this->parent = $parent;
		$this->propertyMeta = $propertyMeta;
		$this->primaryValue = $value;
		$this->primaryKey = $parent->getRepository()->getMapper()->getStorageReflection()->getEntityPrimaryKey()[0];
	}


	public function setInjectedValue($value)
	{
		$this->primaryValue = $value;
	}


	public function getPrimaryValue()
	{
		return $this->primaryValue;
	}


	public function getInjectedValue()
	{
		return $this->get();
	}


	public function set($value)
	{
		$value = $this->createEntity($value);

		if ($this->value !== FALSE && $this->isChanged($value)) {
			$oldValue = $this->primaryValue !== NULL ? $this->parent->getRepository()->getModel()->getRepository($this->propertyMeta->args[0])->getById($this->primaryValue) : NULL;
			$this->updateRelationship($oldValue, $value);
		}

		$this->primaryValue = $value ? $value->{$this->primaryKey} : NULL;
		$this->value = $value;
	}


	public function get()
	{
		if ($this->value === FALSE) {
			$this->set($this->primaryValue);
		}

		return $this->value;
	}


	protected function createEntity($value)
	{
		if ($value instanceof IEntity) {
			if ($model = $this->parent->getModel(FALSE)) {
				$repo = $model->getRepository($this->propertyMeta->args[0]);
				$repo->attach($value);

			} elseif ($model = $value->getModel(FALSE)) {
				$repository = $model->getRepositoryForEntity($this->parent);
				$this->parent->fireEvent('onAttach', array($repository));
			}

		} elseif ($value === NULL) {

		} elseif (is_scalar($value)) {
			$mapper = $this->parent->getRepository()->getMapper()->getCollectionMapperHasOne($this->propertyMeta);
			$value = $mapper->getItem($this->parent);

		} else {
			throw new InvalidArgumentException('Value is not a valid entity representation.');
		}

		return $value;
	}


	protected function isChanged($newValue)
	{
		return (string) $this->primaryValue !== (string) ($newValue instanceof IEntity ? $newValue->{$this->primaryKey} : $newValue);
	}


	abstract protected function updateRelationship($oldEntity, $newEntity);

}
