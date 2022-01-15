<?php
namespace ArieTimmerman\Laravel\SAML\Http\Controllers;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\SAML\Repository\RemoteServiceProviderConfigRepositoryInterface;
use ArieTimmerman\Laravel\SAML\Repository\HostedIdentityProviderConfigRepositoryInterface;
use ArieTimmerman\Laravel\SAML\Exceptions\Manage\ApiException;
use ArieTimmerman\Laravel\SAML\SimpleSAML\SAMLParser;

class ManageController extends Controller
{
    protected $rules = [
        'entityid' => 'required',
        'AssertionConsumerService' => 'nullable|array',
        'AssertionConsumerService.*.Binding' => 'required',
        'AssertionConsumerService.*.Location' => 'required|url',
        'AssertionConsumerService.*.index' => 'required|integer',
        'SingleLogoutService' => 'nullable',
        'SingleLogoutService.*.Binding' => 'nullable',
        'SingleLogoutService.*.Location' => 'url',
        'keys' => 'nullable',
        'keys.*.encryption' => 'required|boolean',
        'keys.*.signing' => 'required|boolean',
        'keys.*.type' => 'required',
        'keys.*.X509Certificate' => 'required',
        'wantSignedAuthnResponse' => 'nullable|boolean',


        // TODO: should probably be something like this: saml20.sign.assertion, validate.authnrequest etc
        'wantSignedAssertions' => 'nullable|boolean',
        'wantSignedLogoutResponse' => 'nullable|boolean',
        'wantSignedLogoutRequest' => 'nullable|boolean'
    ];

    protected $rulesIdentityProvider = [
        'PreviousSession' => 'required',
        'sign_authnrequest' => 'required|boolean',
        'metadata_sign_enable' => 'required|boolean',
        'redirect_sign' => 'required|boolean',
        'ssoHttpPostEnabled' => 'required|boolean',
        'ssoHttpRedirectEnabled' => 'required|boolean',
        'sloHttpPostEnabled' => 'required|boolean',
        'sloHttpRedirectEnabled' => 'required|boolean',

        'keys' => 'required|array',
        'keys.*.encryption' => 'required|boolean',
        'keys.*.signing' => 'required|boolean',
        'keys.*.type' => 'required',
        'keys.*.X509Certificate' => 'required',
        'supportedNameIDFormat' => 'array',

        'contacts' => 'array',
        'contacts.*.emailAddress' => 'email',
        'contacts.*.name' => 'nullable',
        'contacts.*.contactType' => 'nullable',

        'organization' => 'array',
        'organization.*.OrganizationName' => 'nullable',
        'organization.*.OrganizationDisplayName' => 'nullable',
        'organization.*.OrganizationURL' => 'url',

    ];

    public function index()
    {
        return resolve(RemoteServiceProviderConfigRepositoryInterface::class)->all();
    }

    public function getIdentityProvider()
    {
        return resolve(HostedIdentityProviderConfigRepositoryInterface::class)->get();
    }

    public function putIdentityProvider(Request $request)
    {
        $input = $request->input();

        $request['sign_authnrequest'] = $input['sign.authnrequest'];
        $request['metadata_sign_enable'] = $input['metadata.sign.enable'];
        $request['redirect_sign'] = $input['redirect.sign'];

        $valid = $this->validate($request, $this->rulesIdentityProvider);
        
        $valid['sign.authnrequest'] = $request['sign_authnrequest'];
        $valid['metadata.sign.enable'] = $request['metadata_sign_enable'];
        $valid['redirect.sign'] = $request['redirect_sign'];

        return resolve(HostedIdentityProviderConfigRepositoryInterface::class)->patch($valid);
    }

    protected function removeUnknownKeys($valid)
    {
        if (isset($valid['AssertionConsumerService'])) {
            foreach ($valid['AssertionConsumerService'] as &$a) {
                $a = array_intersect_key($a, array_flip(['Binding','Location','index']));
            }
        }

        if (isset($valid['SingleLogoutService'])) {
            foreach ($valid['SingleLogoutService'] as &$a) {
                $a = array_intersect_key($a, array_flip(['Binding','Location','index']));
            }
        }

        if (isset($valid['keys'])) {
            foreach ($valid['keys'] as &$a) {
                $a = array_intersect_key($a, array_flip(['encryption','signing','type','X509Certificate']));
            }
        }

        return $valid;
    }

    public function create(Request $request)
    {
        $valid = $this->validate($request, $this->rules);
        $valid = $this->removeUnknownKeys($valid);

        if (resolve(RemoteServiceProviderConfigRepositoryInterface::class)->get($valid['entityid']) != null) {
            throw new ApiException('EntityId already exists!');
        }

        return response(resolve(RemoteServiceProviderConfigRepositoryInterface::class)->add($valid), 201);
    }

    public function importMetadata(Request $request)
    {
        try {
            //TODO: For now, disable asserts due to errors.
            assert_options(ASSERT_ACTIVE, 0);
            
            $parsed = SAMLParser::parseString($request->input('metadata'));
            $parsed = $parsed->getMetadata20SP();

            if (resolve(RemoteServiceProviderConfigRepositoryInterface::class)->get($parsed['entityid']) != null) {
                throw new ApiException('EntityId already exists!');
            }

            return response(resolve(RemoteServiceProviderConfigRepositoryInterface::class)->add($parsed), 201);
        } catch (\Exception $e) {
            return response([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function put(Request $request, $id)
    {
        $valid = $this->validate($request, $this->rules);
        $valid = $this->removeUnknownKeys($valid);

        return resolve(RemoteServiceProviderConfigRepositoryInterface::class)->patch($id, $valid);
    }

    public function createFromXML(Request $request)
    {

        //
    }

    public function get(Request $request, $id)
    {
        return resolve(RemoteServiceProviderConfigRepositoryInterface::class)->getById($id);
    }

    public function delete(Request $request, $id)
    {
        
        // $serviceProviderConfig = resolve(RemoteServiceProviderConfigRepositoryInterface::class)->get($id);

        resolve(RemoteServiceProviderConfigRepositoryInterface::class)->deleteById($id);
    }
}
