<?php declare(strict_types = 1);

namespace Nextras\Orm\Relationships;


use Countable;
use IteratorAggregate;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Entity\IEntityAwareProperty;
use Nextras\Orm\Entity\IPropertyContainer;


/**
 * @template E of IEntity
 * @extends IteratorAggregate<int, E>
 * @extends IEntityAwareProperty<E>
 */
interface IRelationshipCollection extends IPropertyContainer, IEntityAwareProperty, IteratorAggregate, Countable
{
	/**
	 * Adds entity.
	 * @param IEntity|string|int $entity
	 * @phpstan-param E|string|int $entity
	 * @phpstan-return E|null
	 */
	public function add($entity): ?IEntity;


	/**
	 * Replaces all entities with given ones.
	 * Returns true if the setter has modified property value.
	 * @param IEntity[]|string[]|int[] $data
	 * @phpstan-param list<E>|list<string>|list<int> $data
	 */
	public function set(array $data): bool;


	/**
	 * Removes entity.
	 * @param IEntity|string|int $entity
	 * @phpstan-param E|string|int $entity
	 * @phpstan-return E|null
	 */
	public function remove($entity): ?IEntity;


	/**
	 * @param IEntity|string|int $entity
	 * @phpstan-param E|string|int $entity
	 */
	public function has($entity): bool;


	/**
	 * Returns collection of all entity.
	 * @phpstan-return ICollection<E>
	 */
	public function toCollection(): ICollection;


	/**
	 * Returns true if collection was loaded.
	 */
	public function isLoaded(): bool;


	/**
	 * Returns true if relationship is modified.
	 */
	public function isModified(): bool;


	/**
	 * Counts collection entities without fetching them from storage.
	 */
	public function countStored(): int;


	/**
	 * @internal
	 * @ignore
	 * @phpstan-param E $entity
	 */
	public function trackEntity(IEntity $entity): void;


	/**
	 * Returns IEntity for persistence.
	 * @return IEntity[]
	 * @phpstan-return array<int, E>
	 * @ignore
	 * @internal
	 */
	public function getEntitiesForPersistence(): array;


	/**
	 * DO NOT CALL THIS METHOD DIRECTLY.
	 * @internal
	 * @ignore
	 */
	public function doPersist(): void;
}
