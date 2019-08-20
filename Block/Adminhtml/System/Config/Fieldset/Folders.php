<?php
/**
 * Magento - Cleanup
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to license that is bundled with
 * this package in the file LICENSE.txt.
 *
 * @category   Phoenix
 * @package	   Phoenix_Cleanup
 * @copyright  Copyright (c) 2013-2016 Phoenix Media GmbH (http://www.phoenix-media.eu)
 */
namespace Phoenix\Cleanup\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Folders extends AbstractFieldArray
{
    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'path',
            [
                'label' => __('Path'),
                'style' => 'width:260px'
            ]
        );

        $this->addColumn(
            'days',
            [
                'label' => __('Cleanup Days'),
                'style' => 'width:80px'
            ]
        );

        $this->addColumn(
            'mask',
            [
                'label' => __('File Mask'),
                'style' => 'width:40px'
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Folder');
    }
}
