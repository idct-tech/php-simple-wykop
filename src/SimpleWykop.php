<?php

namespace IDCT\Wykop;

use IDCT\Wykop\Entity\Profile;
use IDCT\Wykop\Entity\WykopConnect;
use IDCT\Wykop\Exception\CurlException;
use IDCT\Wykop\Exception\InvalidWykopCredentials;
use IDCT\Wykop\Exception\SimpleWykopStateException;
use IDCT\Wykop\Exception\WykopException;
use IDCT\Wykop\Exception\WykopGeneralException;
use IDCT\Wykop\Response\GenericResponse;
use IDCT\Wykop\Response\Notifications;
use InvalidArgumentException;
use RuntimeException;

class SimpleWykop
{
    /**
     * Url of the API's endpoint.
     */
    protected const ENDPOINT = 'https://a2.wykop.pl/';
    /**
     * App's key.
     *
     * On apps' page under "Klucz" column:
     * https://www.wykop.pl/dla-programistow/twoje-aplikacje/
     *
     * @var string
     */
    protected $key;

    /**
     * App's secret.
     *
     * On apps' page under "Sekret" column:
     * https://www.wykop.pl/dla-programistow/twoje-aplikacje/
     *
     * @var string
     */
    protected $secret;

    /**
     * Connection key between user and the app.
     * On user's apps pages under "Połączenie" column:
     * https://www.wykop.pl/dla-programistow/twoje-aplikacje/
     *
     * Can be generated manually by the user in the panel from the link above or by linking user to an app using `getConnectionUrl`.
     *
     * @var string
     */
    protected $connectionKey;

    /**
     * Temporary login key assigned after a successful login using the webservice.
     * Required for most user operations.
     *
     * @var string
     */
    protected $loginKey;

    /**
     * Signed-in user's username.
     *
     * @var string
     */
    protected $login;

    /**
     * Format of the response.
     *
     * Defaults to JSON.
     *
     * @var Format
     */
    protected $responseFormat;

    /**
     * User agent.
     *
     * @var string
     */
    protected $userAgent = 'PHP Application based on idct/simple-wykop library.';

    /**
     * Output of the text fields: clear: without html, both: version with html is also returned.
     *
     * Defaults to BOTH.
     *
     * @var HtmlOutput
     */
    protected $htmlOutput;
    
    /**
     * Last response from Wykop's webservice.
     *
     * @param string
     */
    protected $lastResponse;

    /**
     * Creates an instance of the connector.
     *
     * You can provide connection key here already in case you operate fully server-side.
     *
     * @param string $key
     * @param string $secret
     * @param string $connectionKey Optional
     */
    public function __construct(string $key, string $secret, string $connectionKey = null)
    {
        if (empty($key)) {
            throw new InvalidArgumentException("Missing key.");
        }

        if (empty($secret)) {
            throw new InvalidArgumentException("Missing secret.");
        }
        
        $this->key = $key;
        $this->secret = $secret;
        $this->connectionKey = $connectionKey;
    }

    /**
     * Returns signed-in user's username or null in no user is signed in.
     *
     * @var string|null
     */
    public function getLogin() : ?string
    {
        return $this->login;
    }

    /**
     * Returns the format of the response.
     *
     * Defaults to JSON
     *
     * @return Format
     */
    public function getResponseFormat() : Format
    {
        if ($this->responseFormat === null) {
            return Format::JSON();
        }

        return $this->responseFormat;
    }

    /**
     * Sets the user agent.
     *
     * @param string
     * @return $this
     */
    public function setUserAgent(string $userAgent)
    {
        $this->userAgent  = $userAgent;

        return $this;
    }

    /**
     * Returns the user agent.
     *
     * @return string
     */
    public function getUserAgent() : string
    {
        return $this->userAgent;
    }

    /**
     * Returns user's login key or null if not signed in.
     *
     * @return string|null
     */
    public function getLoginKey() : ?string
    {
        return $this->loginKey;
    }

    /**
     * Returns how the output of the text fields should be handled
     * - clear: without html
     * - both: version with html is also returned.
     *
     * Defaults to BOTH.
     *
     * @var HtmlOutput
     */
    public function getHtmlOutput() : HtmlOutput
    {
        if ($this->htmlOutput === null) {
            return HtmlOutput::BOTH();
        }

        return $this->htmlOutput;
    }

    /**
     * Returns app's key.
     *
     * @return string
     */
    public function getAppKey() : string
    {
        return $this->key;
    }

    /**
     * Returns app's secret.
     *
     * @return string
     */
    public function getSecret() : string
    {
        return $this->secret;
    }

    /**
     * Returns the last raw response string from Wykop's webservice.
     *
     * @param string
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Generates "Wykop Connect" url which allows user to link account with app.
     * Returns connectionKey.
     *
     * @param string $returnUrl Optional return redirect URL
     * @return string Wykop Connect url
     */
    public function getConnectUrl($returnUrl = null)
    {
        $url = self::ENDPOINT . 'login/connect/appkey/' . $this->getAppKey();
        if ($returnUrl !== null) {
            $url .= 'redirect/' . urlencode(base64_encode($returnUrl)) . '/';
            $url .= 'secure/' . md5($this->secret . $returnUrl);
        }

        return $url;
    }

    /*
     * Parses the data returned by Wykop Connect.
     *
     * Fallbacks to $_GET['connectData'] if first argument is not present.
     *
     * @param string $wykopConnectData Optional data returned by Wykop Connect.
     * @return array - tablica z danymi connecta (appkey, login, token) - wykorzystywane później do logowania
     */
    public function parseWykopConnectData(string $wykopConnectData = null)
    {
        if (empty($wykopConnectData) && !empty($_GET['connectData'])) {
            $wykopConnectData = $_GET['connectData'];
            //$data = base64_decode($_GET['connectData']);
            //return json_decode($data, true);
        }

        $data = base64_decode($wykopConnectData);
        if ($data === false) {
            throw new InvalidArgumentException("Could not decode Wykop Connect data.");
        }

        if (!isset($data['appkey']) || !isset($data['login']) || !isset($data['token']) || !isset($data['sign'])) {
            throw new InvalidArgumentException("Missing required arguments.");
        }

        if ($data['appkey'] !== $this->getAppKey()) {
            throw new InvalidArgumentException("Invalid App Key in Wykop Connect data: Wykop's error or bad input passed.");
        }

        if ($data['sign'] !== md5($this->getSecret() . $this->getAppKey() . $data['login'] . $data['token'])) {
            throw new RuntimeException("Invalid signature in Wykop Connect data: bad input passed to function?");
        }

        throw new InvalidArgumentException("Missing Wykop Connect data.");
    }

    /**
     * Generic method for execution of actions. To be used whenever a dedicated method for a particular call is not implemented yet.
     *
     * @param string $action for example "notifications/index"
     * @param array $postData array of input data passed to the webservice - ie. ['login' => 'artpopo']
     * @param array $fielesData array of files information passed to the webservice ie. ['embed' => "@plik.jpg;type=image/jpeg"]
     * @return GenericResponse
     */
    public function execute(string $action, array $postData, array $filesData = null, int $page = null) : GenericResponse
    {
        return new GenericResponse($this->call($action, $postData, $filesData, $page));
    }

    /**
     * Sets the connection key between user and the app.
     * On user's apps pages under "Połączenie" column:
     * https://www.wykop.pl/dla-programistow/twoje-aplikacje/
     *
     * Can be generated manually by the user in the panel from the link above or by linking user to an app using `getConnectionUrl`.
     *
     * @param string $connectionKey
     * @return $this
     */
    public function setConnectionKey(string $connectionKey)
    {
        if (empty($connectionKey)) {
            throw new InvalidArgumentException("connetionKey must be a non-empty string.");
        }

        $this->connectionKey = $connectionKey;
    }

    /**
     * Gets the connection key between user and the app.
     * On user's apps pages under "Połączenie" column:
     * https://www.wykop.pl/dla-programistow/twoje-aplikacje/
     *
     * Can be generated manually by the user in the panel from the link above or by linking user to an app using `getConnectionUrl`.
     * @return string|null
     */
    public function getConnectionKey() : ?string
    {
        return $this->connectionKey;
    }

    /**
     * Sets the connection key between user and the app based on the data from WykopConnect.
     * On user's apps pages under "Połączenie" column:
     * https://www.wykop.pl/dla-programistow/twoje-aplikacje/
     *
     * Can be generated manually by the user in the panel from the link above or by linking user to an app using `getConnectionUrl`.
     *
     * @param WykopConnect $wykopConnect Check getConnectionUrl and parseWykopConnectData
     * @return $this
     */
    public function setConnectionKeyByWykopConnect(WykopConnect $wykopConnect)
    {
        $this->connectionKey = $wykopConnect->getToken();

        return $this;
    }

    /**
     * Attempts to sign in as user identifier with $login.
     *
     * @param string $login username.
     * @throws RuntimeException
     * @return Profile
     */
    public function login(string $login)
    {
        if (empty($this->connectionKey)) {
            throw new RuntimeException("connectionKey not set.");
        }

        if (empty($login)) {
            throw new InvalidArgumentException("Login cannot be empty.");
        };

        $response = $this->execute('login/index', ['accountkey' => $this->getConnectionKey(), 'login' => $login]);
        $this->loginKey = $response->getData()['userkey'];
        $this->login = $login;

        return new Profile($response);
    }

    /**
     * Assumes that you have signed in before and attempt to re-login with the same authorization details.
     * No check with Wykop's API is commenced.
     *
     * @param string $login username.
     * @param string $loginKey Previously obtained login key.
     * @throws RuntimeException
     * @return this
     */
    public function loginManually(string $login, string $loginKey)
    {
        if (empty($this->connectionKey)) {
            throw new RuntimeException("connectionKey not set.");
        }

        if (empty($login)) {
            throw new InvalidArgumentException("Login cannot be empty.");
        };

        if (empty($loginKey)) {
            throw new InvalidArgumentException("LoginKey (userkey) cannot be empty.");
        };

        $this->loginKey = $loginKey;
        $this->login = $login;

        return $this;
    }

    /**
     * Removes login information from the instance.
     *
     * WARNING: does not actually destroy login data in Wykop's API as there is no such method.
     * @return $this
     */
    public function logout()
    {
        $this->login = null;
        $this->loginKey = null;
    }

    public function retrieveNotifications(int $page = null)
    {
        $this->verifyLoginState();

        return new Notifications($this->call("notifications/index", ['login' => $this->getLogin()], null, $page));
    }

    public function retrieveHashtagNotifications(int $page = null)
    {
        $this->verifyLoginState();

        return new Notifications($this->call("notifications/HashTags", ['login' => $this->getLogin()], null, $page));
    }    

    /**
     * Builds the url based on desired action, endpoint and other parameters.
     *
     * @param string $action
     * @param string $appKey
     * @param Format $format
     * @param HtmlOutput $output
     * @param string $loginKey Optional
     * @throws InvalidArgumentException
     * @return string
     */
    protected function buildQueryUrl(string $action, string $appKey, Format $format, HtmlOutput $output, string $loginKey = null, $page = null)
    {
        if (empty($action) || empty($appKey) || empty($format) || empty($output)) {
            throw new InvalidArgumentException("Missing required params, only login key is optional.");
        }

        $url = static::ENDPOINT . $action .= (strpos($action, ',') ? ',' : '/') . 'appkey/' . $appKey . '/format/' . $format . '/output/' . $output;
        if ($loginKey !== null) {
            $url .= '/userkey/' . $loginKey;
        }

        if ($page !== null) {
            $url .= '/page/' . $page;
        }

        return $url;
    }
    
    /**
     * Prepares the signing key as required by API v2 of Wykop.pl
     *
     * @param string $url Full query url (including the domain)
     * @param array $post Array of POST keys and values
     * @throws InvalidArgumentException
     * @return string MD5 hash of the key
     */
    protected function sign(string $url, array $post = null) : string
    {
        $secret = $this->getSecret();
        if (empty($secret)) {
            throw new InvalidArgumentException("Missing App Secret.");
        }

        if ($post !== null) {
            ksort($post);
        }

        return md5($secret . $url . ($post === null ? '' : implode(',', $post)));
    }

    /**
     * Sends the API request.
     *
     * @param string $action for example "links/upcoming"
     * @param array $postData data contents
     * @param array $filesData files, ie. array('embed' => "@plik.jpg;type=image/jpeg")
     *
     * @return array response
     */
    protected function call(string $action, array $postData = null, array $filesData = null, $page = null)
    {
        $url = $this->buildQueryUrl($action, $this->getAppKey(), $this->getResponseFormat(), $this->getHtmlOutput(), $this->getLoginKey(), $page);
        if ($filesData !== null) {
            $postData = $filesData + $postData;
        }
            
        $options = [
                CURLOPT_RETURNTRANSFER     => true,
                CURLOPT_HEADER             => false,
                CURLOPT_ENCODING           => '',
                CURLOPT_USERAGENT          => $this->getUserAgent(),
                CURLOPT_AUTOREFERER        => true,
                CURLOPT_CONNECTTIMEOUT     => 15,
                CURLOPT_TIMEOUT            => 15,
                CURLOPT_MAXREDIRS          => 10,
                CURLOPT_FAILONERROR        => true,
                CURLOPT_HTTPHEADER         => ['apisign: ' . $this->sign($url, $postData)]
        ];
        
        if ($postData !== null) {
            $postDataString = is_array($postData) ? http_build_query($postData, 'f_', '&') : '';
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postDataString;
        }
                                                                                                                                                                           
        $ch  = curl_init($url);
        curl_setopt_array($ch, $options);
        $this->lastResponse = curl_exec($ch);
        if ($this->lastResponse === false) {
            $err = curl_errno($ch);
            $info = curl_getinfo($ch);
            $errmsg = curl_strerror($err) . ': ' . curl_error($ch);
            curl_close($ch);
            if ($info['http_code'] == 401) {
                throw new InvalidWykopCredentials($errmsg, 401);
            }
            throw new CurlException($errmsg, $err);
        }
        curl_close($ch);

        if (empty($this->lastResponse)) {
            throw new WykopGeneralException("Empty response.");
        }

        $parsedJson = json_decode($this->lastResponse, true);
        if ($parsedJson === false) {
            throw new WykopGeneralException("Invalid response: could not parse JSON.");
        }

        if (isset($parsedJson['error']) && !empty($parsedJson['error'])) {
            if ($parsedJson['error']['code'] === 14) {
                throw new InvalidWykopCredentials($parsedJson['error']['message_en'], $parsedJson['error']['code']);
            }
            throw new WykopException($parsedJson['error']['message_en'], $parsedJson['error']['code']);
        }

        if (!isset($parsedJson['data'])) {
            throw new WykopException("Response is missing data field. Check `getLastResponse`.");
        }

        return $parsedJson;
    }

    /**
     * Check if user is signed in or re-attempts to do so.
     *
     * @throws SimpleWykopStateException
     * @return $this
     */
    protected function verifyLoginState()
    {
        if (empty($this->getLogin())) {
            throw new SimpleWykopStateException("Operation requires login information to be present.");
        }

        if (empty($this->getLoginKey())) {
            //let us try to sign in first
            $this->login($this->getLogin());
        }
    }
}
