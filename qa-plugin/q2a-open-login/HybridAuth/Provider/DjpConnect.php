<?php

/* !
 * Hybridauth
 * https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
 *  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
 */

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * DjpConnect OAuth2 provider adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback' => Hybridauth\HttpClient\Util::getCurrentUrl(),
 *       'keys'     => [ 'id' => '', 'secret' => '' ],
 *       'scope'    => 'https://www.googleapis.com/auth/userinfo.profile',
 *
 *        // google's custom auth url params
 *       'authorize_url_parameters' => [
 *              'approval_prompt' => 'force', // to pass only when you need to acquire a new refresh token.
 *              'access_type'     => ..,      // is set to 'offline' by default
 *              'hd'              => ..,
 *              'state'           => ..,
 *              // etc.
 *       ]
 *   ];
 *
 *   $adapter = new Hybridauth\Provider\DjpConnect( $config );
 *
 *   try {
 *       $adapter->authenticate();
 *
 *       $userProfile = $adapter->getUserProfile();
 *       $tokens = $adapter->getAccessToken();
 *       $contacts = $adapter->getUserContacts(['max-results' => 75]);
 *   }
 *   catch( Exception $e ){
 *       echo $e->getMessage() ;
 *   }
 */
class DjpConnect extends OAuth2 {

    /**
     * {@inheritdoc}
     */
    public $scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://www.googleapis.com/';

    /**
     * {@inheritdoc}
     */
    protected $iamMethod = '';
    protected $checkTokenParameters = [];
    protected $iamParameter = [];
    protected $logoutMethod = '';
    protected $logoutParameter = [];

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = SSO_DJP . '/oauth/authorize';
    protected $logoutUrl = SSO_DJP . '/logout';
    protected $accessTokenUrl = SSO_DJP . '/oauth/token';
    protected $checkTokenUrl = SSO_DJP . "/oauth/check_token";
    var $urlLogout = SSO_DJP . "/logout";
    protected $iamUrl = IAM_DJP . "/iam/api";

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developers.google.com/identity/protocols/OAuth2';

    /**
     * {@inheritdoc}
     */
    protected function initialize() {

        parent::initialize();

        $this->AuthorizeUrlParameters += [
            'access_type' => 'offline'
        ];

        $this->tokenRefreshParameters += [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];

        $this->iamRequestHeader = [
            'Authorization' => 'Bearer ' . $this->getStoredData('iamToken')
        ];
    }

    /**
     * {@inheritdoc}
     *
     * See: https://developers.google.com/identity/protocols/OpenIDConnect#obtainuserinfo
     */
    public function getUserProfile() {
        $response = $this->getUserData();
//        $response = $this->apiRequest('oauth2/v3/userinfo');

        $data = new Data\Collection($response);

        $profile = json_decode($data->get('scalar'), TRUE);
        //var_dump($profile['pegawai']['nama']);
        //die;
        if (!$data->exists('scalar')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

//        $userProfile = new User\Profile();
        $userProfile = new \stdClass();
//        var_dump($profile['pegawai']);die();

        $userProfile->identifier = $profile['pegawai']['nip9'];
        $userProfile->firstName = $profile['pegawai']['nama'];
        $userProfile->displayName = $profile['pegawai']['nama'];

        // Custom User Data
        if(in_array('ROLE_DJPFORUM_AR',$profile['roles']))
            $userProfile->role = QA_JENIS_USER_AR;
        elseif(in_array('ROLE_DJPFORUM_KASI_PENGAWASAN_KPP',$profile['roles']))
            $userProfile->role = QA_JENIS_USER_KASI_PENGAWASAN_KPP;
        elseif(in_array('ROLE_DJPFORUM_KASI_PENGAWASAN_KANWIL',$profile['roles']))
            $userProfile->role = QA_JENIS_USER_KASI_PENGAWASAN_KANWIL;
        elseif(in_array('ROLE_DJPFORUM_PEGAWAI_PENGAWASAN',$profile['roles']))
            $userProfile->role = QA_JENIS_USER_PENGAWASAN;
        else
            $userProfile->role = QA_JENIS_USER_DJP;

        $userProfile->username = $profile['username'];

        return $userProfile;
    }

    protected function getUserData() {

        $urlGetUser = $this->iamUrl . "/users/" . $this->getStoredData('id_user');
        $response = $this->httpClient->request(
                $urlGetUser, $this->iamMethod, $this->iamParameter, $this->iamRequestHeader
        );

        $this->validateApiResponse('Unable to exchange code for API access token');
        //die;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserContacts($parameters = []) {
        $parameters = ['max-results' => 500] + $parameters;

        // Google Gmail and Android contacts
        if (false !== strpos($this->scope, '/m8/feeds/') || false !== strpos($this->scope, '/auth/contacts.readonly')) {
            return $this->getGmailContacts($parameters);
        }
    }

    /**
     * Retrieve Gmail contacts
     */
    protected function getGmailContacts($parameters = []) {
        $url = 'https://www.google.com/m8/feeds/contacts/default/full?'
                . http_build_query(array_replace(['alt' => 'json', 'v' => '3.0'], (array) $parameters));

        $response = $this->apiRequest($url);

        if (!$response) {
            return [];
        }

        $contacts = [];

        if (isset($response->feed->entry)) {
            foreach ($response->feed->entry as $idx => $entry) {
                $uc = new User\Contact();

                $uc->email = isset($entry->{'gd$email'}[0]->address) ? (string) $entry->{'gd$email'}[0]->address : '';

                $uc->displayName = isset($entry->title->{'$t'}) ? (string) $entry->title->{'$t'} : '';
                $uc->identifier = ($uc->email != '') ? $uc->email : '';
                $uc->description = '';

                if (property_exists($response, 'website')) {
                    if (is_array($response->website)) {
                        foreach ($response->website as $w) {
                            if ($w->primary == true) {
                                $uc->webSiteURL = $w->value;
                            }
                        }
                    } else {
                        $uc->webSiteURL = $response->website->value;
                    }
                } else {
                    $uc->webSiteURL = '';
                }

                $contacts[] = $uc;
            }
        }

        return $contacts;
    }

    function logout() {
        //var_dump("kucing");
        //die;
        $response = $this->httpClient->request(
                $urlGetUser, $this->logoutMethod, $this->logoutParameter, $this->iamRequestHeader
        );


        return void;
    }

}
