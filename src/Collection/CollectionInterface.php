<?php

namespace Whirlpool\Collection;


interface CollectionInterface
{

    /**
     * Add an item to the collection.
     *
     * @param $item
     * @param null $id
     * @return mixed
     */
    public function add($item, $id = null);


    /**
     * Clear the collection.
     *
     * @return mixed
     */
    public function clear();


    /**
     * Get a specific item from the collection.
     *
     * @param $id
     * @return mixed
     */
    public function get($id);


    /**
     * Remove a specific item from the collection.
     *
     * @param $id
     * @return mixed
     */
    public function remove($id);


    /**
     * Return all of the items in the collections.
     *
     * @return mixed[]
     */
    public function getAll();


    /**
     * Returns the number of items in the collection.
     *
     * @return int
     */
    public function count();

}