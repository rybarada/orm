<?php declare(strict_types = 1);

namespace Nextras\Orm\Relationships;


use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use function assert;


/**
 * @template E of IEntity
 * @extends HasOne<E>
 */
class OneHasOne extends HasOne
{
	public function __construct(PropertyMetadata $metadata)
	{
		parent::__construct($metadata);
		$this->isValueFromStorage = !$this->metadataRelationship->isMain;
	}


	protected function createCollection(): ICollection
	{
		/** @var ICollection<E> $collection */
		$collection = $this->getTargetRepository()->getMapper()->createCollectionOneHasOne($this->metadata);
		return $collection->setRelationshipParent($this->parent);
	}


	public function setRawValue($value): void
	{
		parent::setRawValue($value);
		if (!$this->metadataRelationship->isMain) {
			$this->isValueValidated = false;
		}
	}


	public function getRawValue()
	{
		if ($this->isValueFromStorage && !$this->metadataRelationship->isMain) {
			$this->initValue();
		}
		return parent::getRawValue();
	}


	public function hasInjectedValue(): bool
	{
		if ($this->isValueFromStorage && !$this->metadataRelationship->isMain) {
			$this->initValue();
		}
		return parent::hasInjectedValue();
	}


	protected function modify(): void
	{
		$this->isModified = true;
		if ($this->metadataRelationship->isMain) {
			$this->parent->setAsModified($this->metadata->name);
		}
	}


	protected function updateRelationship(?IEntity $oldEntity, ?IEntity $newEntity, bool $allowNull): void
	{
		$key = $this->metadataRelationship->property;
		if ($key === null) {
			return;
		}

		$this->updatingReverseRelationship = true;
		if ($oldEntity !== null) {
			$oldProperty = $oldEntity->getProperty($key);
			assert($oldProperty instanceof OneHasOne);
			$oldProperty->set(null, $allowNull);
		}
		if ($newEntity !== null) {
			$newProperty = $newEntity->getProperty($key);
			assert($newProperty instanceof OneHasOne);
			$newProperty->set($this->parent, $allowNull);
		}
		$this->updatingReverseRelationship = false;
	}


	protected function initReverseRelationship(?IEntity $entity): void
	{
		$key = $this->metadataRelationship->property;
		if ($key === null || $entity === null) {
			return;
		}

		$this->updatingReverseRelationship = true;
		$property = $entity->getProperty($key);
		assert($property instanceof OneHasOne);
		$property->set($this->parent);
		$this->updatingReverseRelationship = false;
	}


	protected function isImmediateEntityForPersistence(?IEntity $entity): bool
	{
		return $entity !== null && !$entity->isPersisted() && $this->metadataRelationship->isMain;
	}
}
