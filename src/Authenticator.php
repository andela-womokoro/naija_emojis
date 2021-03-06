<?php

namespace Wilson\Source;

use Exception;
use Slim\Slim;
use Firebase\JWT\JWT;
use Wilson\Source\OutputFormatter;
use Firebase\JWT\ExpiredException;

class Authenticator
{
    /**
     * This function authenticates users before they can be granted access to protected endpoints.
     * @param  Slim   $app
     */
    public static function authenticate(Slim $app)
    {
        $app->response->headers->set('Content-Type', 'application/json');
        $token = $app->request->headers->get('Authorization');

        if(is_null($token)) {
            OutputFormatter::formatOutput($app, 401, "You're not authorized to perform this action. Please login.");
        }

        try {
            Configuration::load();
            $secretKey = getenv('JWT_KEY');

            $jwt = JWT::decode($token, $secretKey, ['HS256']);

            return json_encode($jwt->data);
       }
       catch(ExpiredException $e) {
            OutputFormatter::formatOutput($app, 400, "Your token has expired. Please login again.");
       }
       catch(Exception $e) {
            OutputFormatter::formatOutput($app, 400, 'Exception: '.$e->getMessage());
       }
    }

    /**
     * This function checks if a parameter contained in a request has a valid value
     * @param  Slim   $app   [Slim instance]
     * @param  $param        [the parameter to check]
     * @return string
     */
    public static function checkParamValue(Slim $app, $param, $value)
    {
        $app->response->headers->set('Content-Type', 'application/json');

        if(is_null($value) || empty($value)) {
           OutputFormatter::formatOutput($app, 400, "Missing or invalid parameter value for: $param");
        }
        else {
            return $value;
        }
    }
}