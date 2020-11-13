<?php

namespace YAPF\Junk;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;

// Do not edit this file, rerun gen.php to update!
class WeirdtableSet extends CollectionSet
{
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Weirdtable
    {
        return parent::getObjectByID($id);
    }
}
