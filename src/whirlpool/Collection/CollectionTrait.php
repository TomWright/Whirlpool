<?php

namespace Whirlpool\Collection;


trait CollectionTrait
{

    protected $requiredObjectType = null;

    /**
     * @var array
     */
    protected $collectionItems = [];


    /**
     * Add an item to the collection.
     *
     * @param $item
     * @param null $id
     * @return mixed
     */
    public function add($item, $id = null)
    {
        $this->checkRequiredObjectType($item);
        if ($id === null) {
            $this->collectionItems[] = $item;
        } else {
            $this->collectionItems[$id] = $item;
        }
    }


    /**
     * Clear the collection.
     *
     * @return mixed
     */
    public function clear()
    {
        $this->collectionItems = [];
    }


    /**
     * Get a specific item from the collection.
     *
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        $result = null;

        if (array_key_exists($id, $this->collectionItems)) {
            $result = $this->collectionItems[$id];
        }

        return $result;
    }


    /**
     * Remove a specific item from the collection.
     *
     * @param $id
     * @return mixed
     */
    public function remove($id)
    {
        if (array_key_exists($id, $this->collectionItems)) {
            unset($this->collectionItems[$id]);
        }
    }


    /**
     * Return all of the items in the collections.
     *
     * @return mixed[]
     */
    public function getAll()
    {
        return $this->collectionItems;
    }


    /**
     * Returns the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->collectionItems);
    }


    /**
     * @return null
     */
    public function getRequiredObjectType()
    {
        return $this->requiredObjectType;
    }


    /**
     * @param null $requiredObjectType
     */
    public function setRequiredObjectType($requiredObjectType)
    {
        $this->requiredObjectType = $requiredObjectType;
    }


    /**
     * @param $object
     */
    protected function checkRequiredObjectType($object)
    {
        if ($this->requiredObjectType !== null && $object !== null) {
            if (! is_a($object, $this->requiredObjectType)) {
                throw new \InvalidArgumentException("Collection Object must be of type {$this->requiredObjectType}.");
            }
        }
    }

}