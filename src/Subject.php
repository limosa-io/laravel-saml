<?php

namespace ArieTimmerman\Laravel\SAML;

use Illuminate\Auth\Authenticatable;

class Subject
{

    protected $user;

    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }

    public function getNameIdValue()
    {
        return @$this->user->name ?: $this->user->identifier;
    }

    public function getAttributes(\SAML2\AuthnRequest $authnRequest)
    {
        return [];
    }
}
