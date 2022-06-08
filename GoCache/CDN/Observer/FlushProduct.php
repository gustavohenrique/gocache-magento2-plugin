<?php

namespace GoCache\CDN\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FlushProduct implements ObserverInterface
{
    /**
     * @var \GoCache\CDN\Model\Purge
     */
    private $purge;
    protected $_storeManager;
    protected $messageManager;

    /**
     * FlushCatalogImage constructor.
     * @param \GoCache\CDN\Model\Purge $purge
     */
    public function __construct(
        \GoCache\CDN\Model\Purge $purge, 
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface, 
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->purge = $purge;
        $this->_storeManager = $storeManagerInterface;
        $this->messageManager = $messageManager;
    }


    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $urlKey = $this->_storeManager->getStore(1)->getBaseUrl() . $product->getUrlKey() . '.html';

        $tags = explode(" ", $product->getName());
        
        $this->purge->purgeByTags($tags);
        $this->purge->purgeByUrl($urlKey);

        $this->messageManager->addSuccess(__("O cache do produto " . $product->getName() . " foi limpado na GoCache."  ));
    }
}
