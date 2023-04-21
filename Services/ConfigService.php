<?php
declare(strict_types=1);

namespace Develo\Typesense\Services;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as ScopeConfig;
use Magento\Framework\Encryption\EncryptorInterface;
use Develo\Typesense\Model\Config\Source\TypeSenseIndexMethod;

class ConfigService
{
    /**
     * Config paths
     */
    private const TYPESENSE_ENABLED = 'typesense_general/settings/enabled';
    private const TYPESENSE_CLOUD_ID = 'typesense_general/settings/cloud_id';
    private const TYPESENSE_API_KEY = 'typesense_general/settings/admin_api_key';
    private const TYPESENSE_SEARCH_ONLY_KEY_KEY = 'typesense_general/settings/search_only_key';
    private const TYPESENSE_NODES = 'typesense_general/settings/nodes';
    private const TYPESENSE_PATH = 'typesense_general/settings/path';
    private const TYPESENSE_PORT = 'typesense_general/settings/port';
    private const TYPESENSE_PROTOCOL = 'typesense_general/settings/protocol';
    private const TYPESENSE_INDEX_METHOD = 'typesense_general/settings/index_method';

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface $encryptor
     */
    protected EncryptorInterface $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        EncryptorInterface   $encryptor,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @return int|null
     */
    public function isEnabled(): ?int
    {
        return (int) $this->scopeConfig->getValue(self::TYPESENSE_ENABLED, ScopeConfig::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getCloudId(): ?string
    {
        return $this->scopeConfig->getValue(self::TYPESENSE_CLOUD_ID, ScopeConfig::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        $value = $this->scopeConfig->getValue(self::TYPESENSE_API_KEY, ScopeConfig::SCOPE_STORE);

        return $this->encryptor->decrypt($value);
    }

    /**
     * @return string|null
     */
    public function getSearchOnlyKey(): ?string
    {
        return $this->scopeConfig->getValue(self::TYPESENSE_SEARCH_ONLY_KEY_KEY, ScopeConfig::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getNodes(): ?string
    {
        return $this->scopeConfig->getValue(self::TYPESENSE_NODES, ScopeConfig::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->scopeConfig->getValue(self::TYPESENSE_PATH, ScopeConfig::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getPort(): ?string
    {
        return $this->scopeConfig->getValue(self::TYPESENSE_PORT, ScopeConfig::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getProtocol(): ?string
    {
        return $this->scopeConfig->getValue(self::TYPESENSE_PROTOCOL, ScopeConfig::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getIndexMethod(): ?string
    {
        return $this->scopeConfig->getValue(self::TYPESENSE_INDEX_METHOD, ScopeConfig::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isIndexModeTypeSenseOnly(){
        return  $this->getIndexMethod() === TypeSenseIndexMethod::METHOD_TYPESENSE;
    }

    /**
     * @return bool
     */
    public function isIndexModeBoth(){
        return  $this->getIndexMethod() === TypeSenseIndexMethod::METHOD_BOTH;
    }

    /**
     * @return bool
     */
    public function isTypeSenseEnabled()
    {
        return $this->isEnabled() && ($this->isIndexModeTypeSenseOnly() || $this->isIndexModeBoth());
    }
}
