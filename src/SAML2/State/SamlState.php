<?php

namespace ArieTimmerman\Laravel\SAML\SAML2\State;

use Symfony\Component\HttpFoundation\Response;

class SamlState
{
    const STATE_INITIAL = "initial";
    const STATE_SSO_STARTED = "sso_start";
    const STATE_SSO_AUTHENTICATING_START = "sso_authenticating_start";
    const STATE_SSO_AUTHENTICATING_FAILED = "sso_authenticating_failed";
    const STATE_SSO_AUTHENTICATING_SUCCESS = "sso_authenticating_success";
    const STATE_SSO_RESPONDING = "sso_responding";
    const STATE_SLS_STARTED = "sls_start";
    const STATE_SLS_DISPATCH_START = "sls_dispatch_start";
    const STATE_SLS_DISPATCH_END = "sls_dispatch_end";
    const STATE_SLS_PROPAGATE_START = "sls_propagate_start";
    const STATE_SLS_PROPAGATE_END = "sls_propagate_end";
    const STATE_SLS_RESPONDING = "sls_responding";
    
    /**
     * Unique identifier
     *
     * @var unknown
     */
    public $id;
    
    /**
     *
     * @var \SAML2\Request
     */
    protected $request;
    
    /**
     *
     * @var string
     */
    protected $state;
    
    /**
     *
     * @var array
     */
    protected $serviceProvidersIds;
    
    /**
     *
     * @var string
     */
    protected $userName;
    
    /**
     *
     * @var Response|null
     */
    protected $originalLogoutResponse;
    
    /**
     *
     * @var int
     */
    protected $loginRetryCount;
    
    /**
     *
     * @var null|string
     */
    protected $authnContext = null;
    
    protected function guidv4()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data [6] = chr(ord($data [6]) & 0x0f | 0x40); // set version to 0100
        $data [8] = chr(ord($data [8]) & 0x3f | 0x80); // set bits 6-7 to 10
        
        $result = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        
        return $result;
    }
    
    /**
     * SamlState constructor.
     */
    public function __construct()
    {
        $this->id = $this->guidv4();
        
        $this->state = self::STATE_INITIAL;
        $this->serviceProvidersIds = [ ];
        $this->loginRetryCount = 0;
    }
    
    /**
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     *
     * @param string $state
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        
        return $this;
    }
    
    /**
     *
     * @return \SAML2\Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     *
     * @param \SAML2\Request $request
     * @return $this
     */
    public function setRequest(\SAML2\Request $request = null)
    {
        $this->request = $request;
        
        return $this;
    }
    
    /**
     *
     * @return array
     */
    public function getServiceProvidersIds()
    {
        return $this->serviceProvidersIds;
    }
    
    /**
     *
     * @param
     *        	$id
     * @return $this
     */
    public function addServiceProviderId($id)
    {
        if (! $id) {
            throw new \RuntimeException();
        }
        
        if (! $this->hasServiceProviderId($id)) {
            $this->serviceProvidersIds [] = $id;
        }
        
        return $this;
    }
    
    /**
     *
     * @param
     *        	$id
     * @return $this
     */
    public function removeServiceProviderId($id)
    {
        $key = array_search($id, $this->serviceProvidersIds);
        
        if ($key !== false) {
            unset($this->serviceProvidersIds [$key]);
            
            $this->serviceProvidersIds = array_values($this->serviceProvidersIds);
        }
        
        return $this;
    }
    
    /**
     *
     * @param
     *        	$id
     * @return bool
     */
    public function hasServiceProviderId($id)
    {
        return in_array($id, $this->serviceProvidersIds);
    }
    
    /**
     *
     * @return bool
     */
    public function hasServiceProviderIds()
    {
        return ! empty($this->serviceProvidersIds);
    }
    
    /**
     *
     * @return mixed
     */
    public function popServiceProviderIds()
    {
        return array_pop($this->serviceProvidersIds);
    }
    
    /**
     *
     * @return null|string
     */
    public function getAuthnContext()
    {
        return $this->authnContext;
    }
    
    /**
     *
     * @param null|string $authnContext
     * @return SamlState
     */
    public function setAuthnContext($authnContext)
    {
        $this->authnContext = $authnContext;
        return $this;
    }
}
