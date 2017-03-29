<?php
namespace Catalipsis\RelatedProducts\Block;

use Catalipsis\RelatedProducts\Model\RelatedFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory; 

class RelatedBlock extends \Magento\Framework\View\Element\Template
{
	protected $_relatedFactory;
	protected $_productCollectionFactory;
    protected $_categoryCollectionFactory;
 	protected $_logger;
 	protected $_request;
 	protected $_urlinterface;

 	public function __construct (
       RelatedFactory $relatedFactory,
       LoggerInterface $logger,
       Http $request,
       Context $context,
       UrlInterface $urlinterface,
       CollectionFactory $productCollectionFactory,
       CategoryCollectionFactory $categoryCollectionFactory,
       array $data = []
    )
    {
        $this->_relatedFactory = $relatedFactory;
        $this->_logger = $logger;
        $this->_request = $request;
        $this->_urlinterface = $urlinterface;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;

        parent::__construct($context, $data);
    }

    public function makeRelativeUrl($absoluteUrl)
    {
        $relativeUrlStartPos = strrpos($absoluteUrl, "/");
    	$relativeUrlEndPos = strpos($absoluteUrl, ".html");
    	$relativeUrl = substr($absoluteUrl, $relativeUrlStartPos+1, $relativeUrlEndPos - $relativeUrlStartPos - 1);
    	return $relativeUrl;
    }

    public function getCurrentProductId()
    {
    	$collection = $this->_productCollectionFactory->create();
    	$collection->addAttributeToSelect('*');
    	//$collection->setPageSize(100); 

        $currentUrl = $this->getRelativeCurrentUrl();

        foreach ($collection as $product) {
           $productInfo = $product->getData();
           
           //some products doesn't have url_key in array, which was returned by getData()
            if (empty($productInfo['url_key'])) {
                $productUrl = $product->getUrlModel()->getUrl($product);
                $productRelativeUrl = $this->makeRelativeUrl($productUrl);
                if ($productRelativeUrl == $currentUrl) {
                    return $productInfo['entity_id'];
                }
                else {
                    continue;
                }
         	}
         			
           	if ($productInfo['url_key'] == $currentUrl) {
             		return $productInfo['entity_id'];
         	}
        }
    }

    public function getRelatedCollection()
    {
        $collectionObject = $this->_relatedFactory->create();
        $collection = $collectionObject->getCollection();

        return $collection;
    }

    public function getRelatedProduct() 
    {
        $currentProductId = $this->getCurrentProductId();
        $collection = $this->getRelatedCollection();
        
        foreach ($collection as $product) {
            $productInfo = $product->getData();

            if ( ($productInfo['parent_item_id'] == $currentProductId) &&
                 ($productInfo['type'] == 'product') ) {

                //get product with id == related_item_id from product collection
                $productCollection = $this->_productCollectionFactory->create();
                $productCollection->addAttributeToSelect('*')
                                  ->addFieldToFilter('entity_id', $productInfo['related_item_id']);
                $relatedProduct = $productCollection->getFirstItem();

                //get related product Name and URL
                $relatedProductUrl = $relatedProduct->getUrlModel()->getUrl($relatedProduct);
                $relatedProductName = $relatedProduct->getName();
                 
                $relatedProductInfo = array(
                        "name" => $relatedProductName,
                        "url" => $relatedProductUrl,
                );
                return $relatedProductInfo;
            }
        }
        return "Item doesn't have a related products";
    }

    public function getRelatedCategories()
    {
        $currentProductId = $this->getCurrentProductId();
        $collection = $this->getRelatedCollection();
        $arrayOfRelatedCategories = array();
        
        foreach ($collection as $relatedEntity) {
            
            $relatedEntityData = $relatedEntity->getData();
            if ( 
                 ($relatedEntityData['parent_item_id'] == $currentProductId) &&
                 (($relatedEntityData['type'] == 'category1') || ($relatedEntityData['type'] == 'category2'))
            ) {

                //get category with id == related_item_id from category collection
                $categoryCollection = $this->_categoryCollectionFactory->create();
                $categoryCollection->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', $relatedEntityData['related_item_id']);
                $relatedCategory = $categoryCollection->getFirstItem();

                //get category info
                $relatedCategoryInfo = $relatedCategory->getData();
                $relatedCategoryUrl = $relatedCategory->getUrl();
               
                $arrayOfRelatedCategories[] = array(
                    "name" => $relatedCategoryInfo['name'],
                    "url" => $relatedCategoryUrl,
                    "type" => $relatedEntityData['type'],
                );
            }
        }
        return $arrayOfRelatedCategories;
    }
    
    //return current product edit page url
    public function getCurrentUrl()
    {
    	return $this->_urlinterface->getCurrentUrl();
    }

    //return relative current product edit page url
    public function getRelativeCurrentUrl() 
    {
    	$absoluteUrl = $this->_urlinterface->getCurrentUrl();
    	$relativeUrl = $this->makeRelativeUrl($absoluteUrl);
    	return $relativeUrl;
    }
}