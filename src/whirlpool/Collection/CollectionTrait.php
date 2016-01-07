<?php

namespace Whirlpool\Collection;


trait CollectionTrait
{

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

}