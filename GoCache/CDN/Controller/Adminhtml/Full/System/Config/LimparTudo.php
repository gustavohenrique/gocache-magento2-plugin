<?php
namespace GoCache\CDN\Controller\Adminhtml\Full\System\Config;

use GoCache\CDN\Model\Api;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filter\StripTags;

class LimparTudo extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var StripTags
     */
    private $tagFilter;
    /**
     * @var Api
     */
    private $api;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StripTags $tagFilter
     * @param Api $api
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StripTags $tagFilter,
        Api $api
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagFilter = $tagFilter;
        $this->api = $api;
    }

    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => 'Falha ao tentar limpar o cache das URLs.',
        ];
        $options = $this->getRequest()->getParams();

        try {
            

            $response = $this->api->purgeAll();
            if($response){
                $result['success'] = true;
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($result);
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $message = __($e->getMessage());
            $result['errorMessage'] = $this->tagFilter->filter($message);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
