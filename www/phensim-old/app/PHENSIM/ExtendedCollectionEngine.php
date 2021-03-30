<?php

namespace App\PHENSIM;


use Yajra\Datatables\Engines\CollectionEngine;

/**
 * Class ExtendedCollectionEngine.
 *
 * @package App\PHENSIM
 */
class ExtendedCollectionEngine extends CollectionEngine
{

    /**
     * Perform sorting of columns.
     *
     * @return void
     */
    public function ordering()
    {
        if ($this->orderCallback) {
            call_user_func($this->orderCallback, $this, function ($column, $wantsAlias = false) {
                return $this->getColumnName($column, $wantsAlias);
            });
        } else {
            parent::ordering();
        }
    }
}
