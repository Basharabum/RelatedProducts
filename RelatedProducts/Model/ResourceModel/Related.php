<?php
 
namespace Catalipsis\RelatedProducts\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Related extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('catalog_product_relatedproducts', 'entity_id');
    }
}