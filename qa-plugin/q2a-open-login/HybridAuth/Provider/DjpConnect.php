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
    protected $authorizeUrl = 'http://10.254.208.134:8081/oauth/authorize';
    
    protected $logoutUrl = 'http://10.254.208.134:8081/logout';
    
    
    protected $iamMethod = '';
    protected $checkTokenParameters = [];
    protected $iamParameter = [];
    
    protected $logoutMethod = '';
    protected $logoutParameter = [];
    
    var $urlLogout = "http://10.254.208.134:8081/logout";

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'http://10.254.208.134:8081/oauth/token';
    protected $checkTokenUrl = "http://10.254.208.134:8081/oauth/check_token";
    protected $iamUrl = "http://10.244.66.37/api";

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
        var_dump("getUserProfile");
        $response = $this->getUserData();



//        $response = $this->apiRequest('oauth2/v3/userinfo');

        $data = new Data\Collection($response);

        $profile = json_decode($data->get('scalar'), TRUE);
//        var_dump($profile['pegawai']['nama']);

        if (!$data->exists('scalar')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();
//amri tambah
        //mapping role 
        //mapping role level di Q2A
//        Registered User	= 0
//        Expert		= 20
//        Editor		= 50
//        Moderator 		= 80
//        Administrator		= 100
//        Super Administrator	= 120
        
        $role =$profile['pegawai']['jabatan'];
        $level = null;
        switch ($role) {
            case "Pelaksana":
                $level=100;
                break;
            case "Kasubdit":
                
                break;
            case "Kasi":
                
                break;
            default:
                echo "Your favorite color is neither red, blue, nor green!";
        }
        
        var_dump($profile);
        var_dump($role);
       
        $userProfile->identifier = $profile['pegawai']['nip9'];
        $userProfile->firstName = $profile['pegawai']['nama'];
//        $userProfile->lastName = $data->get('family_name');
        $userProfile->displayName = $profile['pegawai']['nama'];
//        $userProfile->photoURL = $data->get('picture');
//        $userProfile->profileURL = $data->get('profile');
        $userProfile->gender = $data->get('gender');
        $userProfile->language = $data->get('locale');
        $userProfile->email = $data->get('email');

        $userProfile->emailVerified = ($data->get('email_verified') === true || $data->get('email_verified') === 1) ? $userProfile->email : '';

        if ($this->config->get('photo_size')) {
            $userProfile->photoURL .= '?sz=' . $this->config->get('photo_size');
        }

        return $userProfile;
    }

    protected function getUserData() {

        $urlGetUser = $this->iamUrl . "/users/" . $this->getStoredData('id_user');
        var_dump("urlGetUser", $urlGetUser);

        $response = $this->httpClient->request(
                $urlGetUser, $this->iamMethod, $this->iamParameter, $this->iamRequestHeader
        );

        $this->validateApiResponse('Unable to exchange code for API access token');

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
          var_dump("kucing");
          die;
           $response = $this->httpClient->request(
                $urlGetUser, $this->logoutMethod, $this->logoutParameter, $this->iamRequestHeader
        );
      
        
        return void;
    }

}
