<?php

namespace GoCache\CDN\Block\Adminhtml\System\Config;

class LimparJS extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('GoCache_CDN::system/config/limparjs.phtml');
        return $this;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('gocache/full_system_config/limparjs'),
                'field_mapping' => str_replace('"', '\\"', json_encode($this->_getFieldMapping()))
            ]
        );

        return $this->_toHtml();
    }

    protected function _getFieldMapping()
    {
        $fields = [
            'textarea_urls' => 'system_full_page_cache_gocache_textarea_urls',
        ];
        return $fields;
    }
}
