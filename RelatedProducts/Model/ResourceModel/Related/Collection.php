<?php
 
namespace Catalipsis\RelatedProducts\Model\ResourceModel\Related;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Catalipsis\RelatedProducts\Model\Related',
            'Catalipsis\RelatedProducts\Model\ResourceModel\Related'
        );
    }
}