<?php
class JWTController
{
    private $header;
    private $payload;
    private $signature;

    public function __construct($params)
    {
        $params["iat"] = time();
        $params["exp"] = time() + (30 * 24 * 60 * 60);

        $this->header = json_encode(["alg" => "HS256", "typ" => "JWT"]);
        $this->payload = json_encode($params);
    }

    public function generateToken()
    {
        include("../util/config.php");

        $base64Header = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($this->header));
        $base64Payload = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($this->payload));

        $this->signature = hash_hmac("sha256", $base64Header . "." . $base64Payload, $secretKey, true);
        $base64Signature = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($this->signature));

        $jwt = $base64Header . "." . $base64Payload . "." . $base64Signature;
        return $jwt;
    }

    public static function validateToken($token)
    {
        $jwt = self::getPayload($token);

        if(!isset($jwt["id"]) || !isset($jwt["account_type"]))
            return false;

        if(time() <= $jwt["exp"])
            return true;

        return false;
    }

    public static function getPayload($token)
    {
        $tokenParts = explode(".", $token);
        $payload = base64_decode($tokenParts[1]);

        return json_decode($payload, true);
    }
}
?>