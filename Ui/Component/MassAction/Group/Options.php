<?php
namespace Hgati\ChangeAttributeSet\Ui\Component\MassAction\Group;

use Magento\Framework\UrlInterface;
//use Zend\Stdlib\JsonSerializable;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product;

class Options extends \Magento\Ui\DataProvider\AbstractDataProvider implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var Product
     */
    protected $product;
    
    /**
     * @var $data
     */
    protected $data;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var $urlPath
     */
    protected $urlPath;

    /**
     * @var $paramName
     */
    protected $paramName;

    /**
     * @var $additionalData
     */
    protected $additionalData = [];

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param Product $product
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        Product $product,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
        $this->product = $product;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize():mixed
    {
        if ($this->options === null) {
            $options = $this->collectionFactory->create()
            ->setEntityTypeFilter($this->product->getEntityType()->getEntityTypeId())
            ->toOptionArray();
            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type' => 'customer_group_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];
                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value']]
                    );
                }
                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
            }
            $this->options = array_values($this->options);
        }
        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
