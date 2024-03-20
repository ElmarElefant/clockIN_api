<?php


class Authentication
{
    protected $Token = null;
    protected $Database = null;
    protected $IsValidToken = false;
    protected $IsValidAdminToken = false;

    /**
     * Es wird aus dem HTTP-Header der Bearer Token ausgelesen, und mit den in der Datenbank gespeicherten Token verglichen.
     */
    public function __construct(Database $Database = null){
        $this->Token = $this->getBearerToken();
        $this->Database = $Database;
        $this->checkToken();
    }

    /**
     * Gibt den im HTTP Header gefundenen Bearer-Token aus. Wurde keiner gefunden wird null ausgegeben.
     * 
     * @return  ?string     Der Token oder null wenn keiner angegeben wurde.
     */
    public function getToken() : ?string {
        return $this->Token;
    }

    /**
     * Gibt zurück, oder der angegebene Token in der Datenbank verifiziert werden konnte.
     * 
     * @return  boll    True wenn ein gültiger Token gefunden wurde, ansonsten False
     */
    public function getIsValidToken() : bool {
        return $this->IsValidToken;
    }

    /**
     * Gibt zurück, ob der angegebene Token zu einem Admin-Konto gehört.
     * 
     * @return  bool    True wenn der Token zu einem Konto mit Admin-Rechten gehört, ansonsten False
     */
    public function getIsValidAdminToken() : bool {
        return $this->IsValidAdminToken;
    }

    /**
     * Sucht nach einem Token im HTTP Header (Authorization) und kontrolliert danach ob dieser in der `users` Table der Datenbank existiert.
     * Danach werden die Properties Token, IsValidToken und IsValidAdminToken gesetzt.
     */
    protected function checkToken() {
        if(is_null($this->Token) || is_null($this->Database)){
            $this->IsValidToken = false;
            $this->IsValidAdminToken = false;
            return;
        }
        $sql = "SELECT * FROM `users` WHERE `apitoken` = :token";
        $params = ['token'=> [$this->Token, PDO::PARAM_STR]];
        $res = $this->Database->fetchAllSQL($sql, $params);
        if(is_null($res) || count($res) == 0){
            $this->IsValidToken = false;
            $this->IsValidAdminToken = false;
            return;
        }else{
            $this->IsValidToken = true;
            if( isset($res[0]['administrator']) && $res[0]['administrator'] == "1" ){
                $this->IsValidAdminToken = true;
            }else{
                $this->IsValidAdminToken = false;
            }
            return;
        }
    }

    /**
     * Extrahiert aus dem HTTP Authorization Header den Bearer Token.
     * 
     * @return  ?string     Den Token, oder null wenn keiner gefunden wurde.
     * */
    function getBearerToken() : ?string {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /** 
     * Den Authorization HTTP Header auslesen und zurückgeben.
     * 
     * @return  ?string     Der Header als string oder null, wenn keiner gefunden wurde. Header sollte die Form `Bearer $token` haben.
     * */
    function getAuthorizationHeader() : ?string {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

}
