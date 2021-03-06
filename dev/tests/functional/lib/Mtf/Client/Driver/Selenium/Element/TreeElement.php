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

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Element as ElementInterface;
use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Class TreeElement
 * Typified element class for Tree elements
 *
 */
class TreeElement extends Element
{
    /**
     * Css class for finding tree nodes
     *
     * @var string
     */
    protected $nodeCssClass = '.x-tree-node > .x-tree-node-ct';

    /**
     * Drag'n'drop method is not accessible in this class.
     * Throws exception if used.
     *
     * @param ElementInterface $target
     * @throws \BadMethodCallException
     */
    public function dragAndDrop(ElementInterface $target)
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * setValue method is not accessible in this class.
     * Throws exception if used.
     *
     * @param string|array $value
     * @throws \BadMethodCallException
     */
    public function setValue($value)
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * getValue method is not accessible in this class.
     * Throws exception if used.
     *
     * @throws \BadMethodCallException
     */
    public function getValue()
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');

    }

    /**
     * keys method is not accessible in this class.
     * Throws exception if used.
     *
     * @param array $keys
     * @throws \BadMethodCallException
     */
    public function keys(array $keys)
    {
        throw new \BadMethodCallException('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * Click a tree element by its path (Node names) in tree
     *
     * @param string $path
     * @throws \InvalidArgumentException
     */
    public function clickByPath($path)
    {
        $pathChunkCounter = 0;
        $pathArray = explode('/', $path);
        $pathArrayLength = count($pathArray);
        $structureChunk = $this->getStructure(); //Set the root of a structure as a first structure chunk
        foreach ($pathArray as $pathChunk) {
            $structureChunk = $this->deep($pathChunk, $structureChunk);
            $structureChunk = ($pathChunkCounter == $pathArrayLength - 1) ?
                $structureChunk['element'] : $structureChunk['subnodes'];
            ++$pathChunkCounter;
        }
        if ($structureChunk) {
            $needleElement = $structureChunk->find('div > a');
            $needleElement->click();
        } else {
            throw new \InvalidArgumentException('The path specified for tree is invalid');
        }
    }

    /**
     * Internal function for deeping in hierarchy of the tree structure
     * Return the nested array if it exists or object of class Element if this is the final part of structure
     *
     * @param string $pathChunk
     * @param array $structureChunk
     * @return array|Element||false
     */
    protected function deep($pathChunk, $structureChunk)
    {
        foreach ($structureChunk as $structureNode) {
            if (isset($structureNode) && preg_match('/' . $pathChunk . ' \(\d+\)/', $structureNode['name'])) {
                return $structureNode;
            }
        }
        return false;
    }

    /**
     * Get structure of the tree element
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->_getNodeContent($this, '.x-tree-root-node');
    }

    /**
     * Get recursive structure of the tree content
     *
     * @param Element $node
     * @param string $parentCssClass
     * @return array
     */
    protected function _getNodeContent($node, $parentCssClass)
    {
        $nodeArray = array();
        $nodeList = array();
        $counter = 1;

        $newNode = $node->find($parentCssClass .' > .x-tree-node:nth-of-type(' . $counter . ')' );

        //Get list of all children nodes to work with
        while ($newNode->isVisible()) {
            $nodeList[] = $newNode;
            ++$counter;
            $newNode = $node->find($parentCssClass .' > .x-tree-node:nth-of-type(' . $counter . ')' );
        }

        //Write to array values of current node
        foreach ($nodeList as $currentNode) {
            /** @var Element $currentNode */
            $nodesNames = $currentNode->find('div > a > span');
            $nodesContents = $currentNode->find($this->nodeCssClass);
            $text = $nodesNames->getText();
            $nodeArray[] = array(
                'name' => $text,
                'element' => $currentNode,
                'subnodes' => $nodesContents->isVisible() ?
                        $this->_getNodeContent($nodesContents, '.x-tree-node > .x-tree-node-ct') : null
            );
        }

        return $nodeArray;
    }
}
