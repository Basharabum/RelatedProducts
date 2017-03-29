<?php
namespace Catalipsis\RelatedProducts\Observer;
 
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use Catalipsis\RelatedProducts\Ui\DataProvider\Product\Form\Modifier\CustomFieldset;
use Catalipsis\RelatedProducts\Model\RelatedFactory;
use Psr\Log\LoggerInterface;

class ProductSaveAfter implements ObserverInterface
{
 	protected $_relatedFactory;
 	protected $_logger;

 	public function __construct(
       RelatedFactory $relatedFactory,
       LoggerInterface $logger
    )
    {
        $this->_relatedFactory = $relatedFactory;
        $this->_logger = $logger;
    }

    public function addRelatedProductToDatabase($parentProductId, $relatedProductId)
    {
    	$model = $this->_relatedFactory->create();
        $collection = $model->getCollection();
        $collection->addFieldToFilter('parent_item_id', $parentProductId)
                   ->addFieldToFilter('type', 'product');

        $item = $collection->getFirstItem();
        $itemInfo = $item->getData();

        if (!empty($itemInfo)) {
            //update existing record in table of related products
            $model->load($itemInfo['entity_id']);
            $model->setData('related_item_id', $relatedProductId)
                    ->save();
        } else {
            //add new record to table of related products
            $model->setData('parent_item_id', $parentProductId)
              ->setData('type', 'product')
              ->setData('related_item_id', $relatedProductId)
              ->save();
        }
    }

    public function addRelatedCategoryToDatabase($parentProductId, $relatedCategoryId, $selectNumber)
    {
        $typeName = 'category'.$selectNumber;   

        $model = $this->_relatedFactory->create();
        $collection = $model->getCollection();
        $collection->addFieldToFilter('parent_item_id', $parentProductId)
                   ->addFieldToFilter('type', $typeName);

        $item = $collection->getFirstItem();
        $itemInfo = $item->getData();

        if (!empty($itemInfo)) {
            //update existing record in table of related products
            $model->load($itemInfo['entity_id']);
            $model->setData('related_item_id', $relatedCategoryId)
                  ->save();
        } else {
            //add new record to table of related products
            $model->setData('parent_item_id', $parentProductId)
                  ->setData('type', $typeName)
                  ->setData('related_item_id', $relatedCategoryId)
                  ->save();
        }
    }

    public function deleteRelatedProductFromDatabase($parentProductId)
    {
        $model = $this->_relatedFactory->create();
        $collection = $model->getCollection();
        $collection->addFieldToFilter('parent_item_id', $parentProductId)
                    ->addFieldToFilter('type', 'product');

        $item = $collection->getFirstItem();
        $itemInfo = $item->getData();

        if (!empty($itemInfo)) {
            //delete existing record in table of related products
            $model->load($itemInfo['entity_id'])->delete();
            $model->save();
        }
    }

    public function deleteRelatedCategoryFromDatabase($parentProductId, $selectNumber)
    {
        $typeName = 'category'.$selectNumber;       
        $model = $this->_relatedFactory->create();
        $collection = $model->getCollection();
        $collection->addFieldToFilter('parent_item_id', $parentProductId)
                   ->addFieldToFilter('type', $typeName);

        $item = $collection->getFirstItem();
        $itemInfo = $item->getData();

        if (!empty($itemInfo)) {
            //delete existing record in table of related products
            $model->load($itemInfo['entity_id'])->delete();
            $model->save();
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product) {
            return;
        }

        $parentProductId= $product->getId();

        //get chosen value from product select
        $relatedProductId = $product->getData(CustomFieldset::FIELD_NAME_SELECT_PRODUCT);
        if ($relatedProductId == -1) {
            //product select value == '-> Remove Related Product'
            $this->deleteRelatedProductFromDatabase($parentProductId);
        } else if ($relatedProductId != 0) {
            //product select value != 'Select...'
        	$this->addRelatedProductToDatabase($parentProductId, $relatedProductId);
        }

        //get chosen value from first category select
        $firstRelatedCategoryId = $product->getData(CustomFieldset::FIELD_NAME_SELECT_FIRST_CATEGORY);
         if ($firstRelatedCategoryId == -1) {
            //product select value == '-> Remove Related Product'
            $this->deleteRelatedCategoryFromDatabase($parentProductId, 1);
        } else if ($firstRelatedCategoryId != 0) {
             //product select value != 'Select...'
        	$this->addRelatedCategoryToDatabase($parentProductId, $firstRelatedCategoryId, 1);
        }

        //get chosen value from second category select
        $secondRelatedCategoryId = $product->getData(CustomFieldset::FIELD_NAME_SELECT_SECOND_CATEGORY);
        if ($secondRelatedCategoryId == -1) {
            //product select value == '-> Remove Related Product'
            $this->deleteRelatedCategoryFromDatabase($parentProductId, 2);
        } else if ($secondRelatedCategoryId != 0) {
            //product select value != 'Select...'
        	$this->addRelatedCategoryToDatabase($parentProductId, $secondRelatedCategoryId, 2);
        }
    }
}
?>   
