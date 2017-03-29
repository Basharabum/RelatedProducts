<?php
namespace Catalipsis\RelatedProducts\Model;

use Magento\Framework\Model\AbstractModel;
 
class Related extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Catalipsis\RelatedProducts\Model\ResourceModel\Related');
    }
}