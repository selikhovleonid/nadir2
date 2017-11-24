<?php

namespace nadir2\core;

/**
 * This is the array collection interface.
 * @author Leonid Selikhov
 */
interface ArrayCollectionInterface
{

    /**
     * It adds an item to the collection.
     * @param string $key The item name.
     * @param mixed $value The item value.
     * @return void
     */
    public function add(string $key, $value): void;

    /**
     * It adds the array of items to the collection.
     * @param array $aPairs The name-value pairs array.
     * @return void
     */
    public function addAll(array $aPairs): void;

    /**
     * It returns the keys of the collection (the iterator analog).
     * @return string[]
     */
    public function getKeys(): array;

    /**
     * It returns the item of the collection by the passed key.
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key);

    /**
     * It returns all elements of the collection.
     * @return mixed[]
     */
    public function getAll(): array;

    /**
     * It removes the collection item by a key.
     * @param string $key
     * @return mixed|null The deleted item value.
     */
    public function remove(string $key);

    /**
     * It removes all items of the collection.
     * @return mixed[] The deleted items values.
     */
    public function removeAll(): array;

    /**
     * It checks if the collection contains the element with passed key.
     * @param string $key The item name.
     * @return boolean
     */
    public function contains(string $key): bool;

    /**
     * It checks if the collection is empty.
     * @return boolean
     */
    public function isEmpty(): bool;

    /**
     * It returns the size of the collection.
     * @return int
     */
    public function size(): int;
}
