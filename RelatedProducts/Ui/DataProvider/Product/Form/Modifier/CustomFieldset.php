<?php
namespace Catalipsis\RelatedProducts\Ui\DataProvider\Product\Form\Modifier;
 

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Catalipsis\RelatedProducts\Model\RelatedFactory;


class CustomFieldset extends AbstractModifier
{
    // Components indexes
    const CUSTOM_FIELDSET_INDEX = 'custom_fieldset';
    const CUSTOM_FIELDSET_CONTENT = 'custom_fieldset_content';
    const CONTAINER_HEADER_NAME = 'custom_fieldset_content_header';
 
    // Fields names
    const FIELD_NAME_SELECT_PRODUCT = 'product_select_field';
    const FIELD_NAME_SELECT_FIRST_CATEGORY = 'example_select_first_category_field';
    const FIELD_NAME_SELECT_SECOND_CATEGORY = 'example_select_second_category_field';

    protected $meta = [];

    protected $_urlinterface;

    protected $_productCollectionFactory;
  
    protected $_categoryCollectionFactory;
       
    protected $_relatedFactory;

    
    public function __construct(
        UrlInterface $urlinterface,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        RelatedFactory $relatedFactory
    ) {
        $this->_urlinterface = $urlinterface;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_relatedFactory = $relatedFactory;
    }
    
    public function modifyData(array $data)
    {
        return $data;
    }
 
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->addCustomFieldset();
 
        return $this->meta;
    }
   
    protected function addCustomFieldset()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::CUSTOM_FIELDSET_INDEX => $this->getFieldsetConfig(),
            ]
        );
    }

    //configuration settings of Fieldset
    protected function getFieldsetConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Related Products Settings'),
                        'componentType' => Fieldset::NAME,
                        'dataScope' => static::DATA_SCOPE_PRODUCT, // save data in the product data
                        'provider' => static::DATA_SCOPE_PRODUCT . '_data_source',
                        'ns' => static::FORM_NAME,
                        'collapsible' => true,
                        'sortOrder' => 10,
                        'opened' => true,
                    ],
                ],
            ],
            'children' => [
                static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                static::FIELD_NAME_SELECT_PRODUCT => $this->getSelectProductFieldConfig(20),
                static::FIELD_NAME_SELECT_FIRST_CATEGORY => $this->getSelectCategoryFieldConfig(30,1),
                static::FIELD_NAME_SELECT_SECOND_CATEGORY => $this->getSelectCategoryFieldConfig(40,2),
                
            ],
        ];
    }
 
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        //'content' => __('Add related product and related categories:'),
                    ],
                ],
            ],
            'children' => [],
        ];
    }

    //get current product id from URL
    protected function getCurrentProductId()
    {
        $currentUrl = $this->_urlinterface->getCurrentUrl();
        $idStartPos = strrpos($currentUrl, "id");
        $idEndPos = strpos($currentUrl, "/",$idStartPos+3);
        $currentItemId = substr($currentUrl, $idStartPos+3, $idEndPos - $idStartPos - 3);
        return $currentItemId;
    }

    public function getRelatedCollection()
    {
        $collectionObject = $this->_relatedFactory->create();
        $collection = $collectionObject->getCollection();

        return $collection;
    }

    //get current related product name
    protected function getRelatedProductName() 
    {
        $currentProductId = $this->getCurrentProductId();
         $collection = $this->getRelatedCollection();

         foreach ($collection as $product) {
            $productArray = $product->getData();

            if ( ($productArray['parent_item_id'] == $currentProductId) &&
                 ($productArray['type'] == 'product') ) {

                $productCollection = $this->_productCollectionFactory->create();
                $productCollection->addAttributeToSelect('*')
                                  ->addFieldToFilter('entity_id',$productArray['related_item_id']);

                 $relatedProduct = $productCollection->getFirstItem();
                 $relatedProductName = $relatedProduct->getName();

                return $relatedProductName;
            }
        }
        return "Select...";
    }

    //get current related categories names
    protected function getRelatedCategoriesNames() 
    {
        $currentProductId = $this->getCurrentProductId();
        $collection = $this->getRelatedCollection();
        $arrayOfRelatedCategoriesNames = array();
         foreach ($collection as $relatedEntity) {
            
            $relatedEntityData = $relatedEntity->getData();
            if ( 
                 ($relatedEntityData['parent_item_id'] == $currentProductId) &&
                 (($relatedEntityData['type'] == 'category1') || ($relatedEntityData['type'] == 'category2'))
            ) {

                //get category collection with entity_id == related_item_id
                $categoryCollection = $this->_categoryCollectionFactory->create();
                $categoryCollection->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', $relatedEntityData['related_item_id']);

                //get category
                $relatedCategory = $categoryCollection->getFirstItem();

                //get category info
                $relatedCategoryInfo = $relatedCategory->getData();
               
                $arrayOfRelatedCategoriesNames [] = array("name" => $relatedCategoryInfo['name'],
                    "type" => $relatedEntityData['type'],
                    );
            }
        }
        return $arrayOfRelatedCategoriesNames;
    }

    protected function getSelectProductFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Related Product'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_NAME_SELECT_PRODUCT,
                        'sortOrder' => $sortOrder,
                        'caption' => $this->getRelatedProductName(),
                        'options' => $this->_getProductOptions(),
                        'visible' => true,
                        'disabled' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getSelectCategoryFieldConfig($sortOrder,$categoryNumber)
    {
        $arrayOfRelatedCategoriesInfo = $this->getRelatedCategoriesNames();
        $relatedCategoryName="Select...";
        $categoryString = 'category'.$categoryNumber;
        
        foreach ($arrayOfRelatedCategoriesInfo as $relatedCategoryInfo)
        {
            if ($relatedCategoryInfo['type'] == $categoryString)
                $relatedCategoryName = $relatedCategoryInfo['name'];
        }
        
        $categoryLabel = 'Related Category '.$categoryNumber;

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __($categoryLabel),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'caption' => $relatedCategoryName,
                        'options' => $this->_getCategoryOptions(),
                        'visible' => true,
                        'disabled' => false,
                    ],
                ],
            ],
        ];
    }

    protected function _getProductOptions()
    {
        $options = array();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        //$collection->setPageSize(5); 
       
        //add remove option to select
        $options[] = ['label' => __("-> Remove Related Product"),
                        'value' => __("-1")];

        foreach ($collection as $product) {
            $productArray = $product->getData();
            $options[] = ['label' => __($productArray['name']),
                        'value' => __($productArray['entity_id'])];
        }
        return $options;
    }

    protected function _getCategoryOptions()
    {
        $options = array();
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        //$collection->setPageSize(5); 

        //add remove option to select
        $options[] = ['label' => __("-> Remove Related Category"),
                        'value' => __("-1")];

        foreach ($collection as $category) {
            $categoryArray = $category->getData();
            $options[] = ['label' => __($categoryArray['name']),
                        'value' => __($categoryArray['entity_id'])];
        }
        return $options;
    }
}
