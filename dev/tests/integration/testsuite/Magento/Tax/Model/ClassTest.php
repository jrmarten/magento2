<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model;

class ClassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testCheckClassCanBeDeletedCustomerClassAssertException()
    {
        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create(
            'Magento\Tax\Model\ClassModel'
        )->getCollection()->setClassTypeFilter(
            \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER
        )->getFirstItem();

        $this->setExpectedException('Magento\Framework\Model\Exception');
        $model->checkClassCanBeDeleted();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCheckClassCanBeDeletedProductClassAssertException()
    {
        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create(
            'Magento\Tax\Model\ClassModel'
        )->getCollection()->setClassTypeFilter(
            \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
        )->getFirstItem();

        $this->_objectManager->create(
            'Magento\Catalog\Model\Product'
        )->setTypeId(
            'simple'
        )->setAttributeSetId(
            4
        )->setName(
            'Simple Product'
        )->setSku(
            uniqid()
        )->setPrice(
            10
        )->setMetaTitle(
            'meta title'
        )->setMetaKeyword(
            'meta keyword'
        )->setMetaDescription(
            'meta description'
        )->setVisibility(
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
        )->setStatus(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        )->setTaxClassId(
            $model->getId()
        )->save();

        $this->setExpectedException('Magento\Framework\Model\Exception');
        $model->checkClassCanBeDeleted();
    }

    /**
     * @dataProvider classesDataProvider
     */
    public function testCheckClassCanBeDeletedPositiveResult($classType)
    {
        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create('Magento\Tax\Model\ClassModel');
        $model->setClassName('TaxClass' . uniqid())->setClassType($classType)->isObjectNew(true);
        $model->save();

        $this->assertTrue($model->checkClassCanBeDeleted());
    }

    public function classesDataProvider()
    {
        return array(
            array(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER),
            array(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT)
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testCheckClassCanBeDeletedCustomerClassUsedInTaxRule()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->_objectManager->get('Magento\Framework\Registry');
        /** @var $taxRule \Magento\Tax\Model\Calculation\Rule */
        $taxRule = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $customerClasses = $taxRule->getCustomerTaxClasses();

        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create('Magento\Tax\Model\ClassModel')->load($customerClasses[0]);
        $this->setExpectedException(
            'Magento\Framework\Model\Exception',
            'You cannot delete this tax class because it is used in' .
            ' Tax Rules. You have to delete the rules it is used in first.'
        );
        $model->checkClassCanBeDeleted();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testCheckClassCanBeDeletedProductClassUsedInTaxRule()
    {
        /** @var $registry \Magento\Framework\Registry */
        $registry = $this->_objectManager->get('Magento\Framework\Registry');
        /** @var $taxRule \Magento\Tax\Model\Calculation\Rule */
        $taxRule = $registry->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $productClasses = $taxRule->getProductTaxClasses();

        /** @var $model \Magento\Tax\Model\ClassModel */
        $model = $this->_objectManager->create('Magento\Tax\Model\ClassModel')->load($productClasses[0]);
        $this->setExpectedException(
            'Magento\Framework\Model\Exception',
            'You cannot delete this tax class because it is used in' .
            ' Tax Rules. You have to delete the rules it is used in first.'
        );
        $model->checkClassCanBeDeleted();
    }
}
