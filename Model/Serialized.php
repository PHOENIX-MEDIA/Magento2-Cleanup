<?php
namespace Phoenix\Cleanup\Model;

use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Serialized
 * @package Phoenix\Cleanup\Model
 */
class Serialized
{
    /**
     * @var ProductMetadataInterface $productMetadata
     */
    private $productMetadata;

    /**
     * Serialized constructor.
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value)
    {
        return $this->isAtLeastMagento22() ? json_encode($value) : serialize($value);
    }

    /**
     * @param String $value
     * @return array|mixed
     */
    public function unserialize($value)
    {
        $result = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // not in json format, try serialized array as fallback for pre magento 2.2 versions
            $result = @unserialize($value);
        }

        if (!is_array($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function isAtLeastMagento22()
    {
        return version_compare($this->productMetadata->getVersion(), '2.2.0', '>=');
    }
}
