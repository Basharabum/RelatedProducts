<?php

namespace Catalipsis\RelatedProducts\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
 
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('catalog_product_relatedproducts')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('catalog_product_relatedproducts')
            )
            ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'product ID'
                )
                ->addColumn(
                    'parent_item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    0,
                    [],
                    'Parent Item Id'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Type product/category'
                )
                ->addColumn(
                    'related_item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    0,
                    [],
                    'Related Item Id'
                )
            ->setComment('Related products and categories table');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
