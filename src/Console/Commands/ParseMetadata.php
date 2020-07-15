<?php

namespace ArieTimmerman\Laravel\SAML\Console\Commands;

use Illuminate\Console\Command;
use ArieTimmerman\Laravel\SAML\Helper;

class ParseMetadata extends Command
{
    protected $signature = 'saml:parsemetadata {url}';

    protected $description = 'Parses metadata for use in config/saml_sp.php, as used by ArieTimmerman\Laravel\SAML\SAML2\Entity\DefaultServiceProviderRepository';

    public function handle()
    {
        $url = $this->argument('url');

        echo var_export(Helper::parseMetaData($url), true) . "\n";
    }
}
