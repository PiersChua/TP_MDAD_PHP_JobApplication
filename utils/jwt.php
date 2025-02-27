<?php

/**
 * JWT Class to encapsulate information and perform verification
 */
class Jwt
{
    private static $sign_key = 'E9$zBX@U6!qaF#v&3QnPjY^kWtR7mLXCd4GV8TsMh9o1Awp*2yrK5JZebNH!Df';
    private static int $expirationTime = 7; // set 7 days expiration

    /**
     *  getenv is not working, need to use phpdotenv
     */
    // public function __construct()
    // {
    //     this->$sign_key = getenv("JWT_SECRET");
    // }
    /**
     * Encodes a payload into a JSON Web Token (JWT)
     * 
     * 
     * @param array $payload The information to encapsulate
     * @return string returns JWT
     */
    public static function encode(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $issuedAt = time();
        $expiredAt = $issuedAt + (self::$expirationTime * 24 * 60 * 60);

        $newPayloadArray = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expiredAt,
        ]);

        $newPayload = json_encode($newPayloadArray);
        // Encode Header to Base64Url String
        $base64UrlHeader = self::base64URLEncode($header);
        $base64UrlPayload = self::base64URLEncode($newPayload);


        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$sign_key, true);
        $base64UrlSignature =
            self::base64URLEncode($signature);
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        return $jwt;
    }
    /**
     * Decodes a JSON Web Token (JWT) into it's payload
     * 
     * @param string $token The JWT token to decode
     * @return array The decoded payload if all validation passes, else an array with error messages 
     */
    public static function decode(string $token): array
    {
        if (
            // named capturing groups to store each part of the token in matches
            preg_match(
                "/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/",
                $token,
                $matches
            ) !== 1
        ) {
            return array("message" => "Invalid token format");
        }
        $signature = hash_hmac(
            "sha256",
            $matches["header"] . "." . $matches["payload"],
            self::$sign_key,
            true
        );

        $signature_from_token = self::base64URLDecode($matches["signature"]);

        // checks if the signature from token matches the one that is just signed
        if (!hash_equals($signature, $signature_from_token)) {
            return array("message" => "Invalid token", "type" => "Error");
        }
        $payload = json_decode(self::base64URLDecode($matches["payload"]), true);
        if (isset($payload["exp"]) && $payload["exp"] < time()) {
            return array("message" => "Token expired", "type" => "Error");
        }

        return $payload;
    }
    /**
     * Perform a verfication to check if the userId in a payload matches the given userId
     * 
     * @param array $payload The payload from decoding the JSON Web Token (JWT)
     * @param string $userId The user identifier from the $_POST superglobal
     */
    public static function verifyPayloadWithUserId(array $payload, string $userId)
    {
        if (isset($payload["type"]) && $payload["type"] === "Error") {
            http_response_code(401);
            echo json_encode($payload);
            exit();
        }
        // check if token belongs to the user
        if ($payload["userId"] !== $userId) {
            http_response_code(401);
            echo json_encode(array("message" => "Unauthorized Token", "type" => "Error"));
            exit();
        }
    }
    public static function getTokenFromHeader($headers): ?string
    {
        if (!isset($headers["Authorization"])) {
            return null;
        }
        $matches = array();
        if (preg_match('/Bearer\s+(\S+)/', trim($headers['Authorization']), $matches)) {
            return $matches[1];
        }
        return null;
    }

    private static function base64URLEncode(string $text): string
    {

        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }
    private static function base64URLDecode(string $text): string
    {
        return base64_decode(
            str_replace(
                ["-", "_"],
                ["+", "/"],
                $text
            )
        );
    }
}
