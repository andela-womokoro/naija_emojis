<?php

namespace Wilson\Source\Controllers;

use PDO;
use Slim\Slim;
use Firebase\JWT\JWT;
use Wilson\Source\Models\User;
use Wilson\Source\Authenticator;
use Wilson\Source\Configuration;

class UserController
{
    public static function register(Slim $app)
    {
        try {
            $app->response->headers->set('Content-Type', 'application/json');

            $user = new User();

            $user->username = $app->request->params('username');
            $user->password = $app->request->params('password');
            $user->name = $app->request->params('name');

            $rows = $user->save();

            if($rows > 0) {
                return json_encode("User registration successful.");
            }
            else {
                return json_encode("User registration failed!");
            }
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public static function login(Slim $app)
    {
        $app->response->headers->set('Content-Type', 'application/json');

        $username = $app->request->params('username');
        $password = $app->request->params('password');

        try
        {
            $conn = User::getConnection();
            $sql = "SELECT * FROM users WHERE username='$username'";
            $stmt = $conn->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($password === $result['password'])
            {
                $token = [
                    'iat'  => time(),
                    'exp'  => time() + 1800,
                    'data' => [
                        'userID'   => $result['user_id'],
                        'username' => $username
                    ]
                ];

                Configuration::load();
                $secretKey = getenv('JWT_KEY');

                $jwt = JWT::encode($token, $secretKey);

                return json_encode($jwt);
            }
            else {
                return json_encode("Login failed. Username or password is invalid.");
            }
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public static function logout(Slim $app)
    {
        $auth = Authenticator::authenticate($app);
        $auth = json_decode($auth, true);

        if(is_array($auth)) {
            return json_encode("You've logged out successfully.");
        }
    }
}