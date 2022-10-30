<?php

namespace endpoints\src;

use api\DataTypes;
use api\JWTConfig;
use api\Request;
use api\Response;
use database\SQLDatabase;
use DataBaseTypes;
use endpoints\EndPoint;
use Firebase\JWT\JWT;
use Roles;
use utils\FileManager;
use utils\Functions;

class Session extends EndPoint
{

    private SQLDatabase $mysql;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response, [
            DataBaseTypes::SQLDatabase
        ]);
        $this->mysql = $this->getSQLDatabase();
    }


    private function getUserToken(array $user_data): string
    {
        global $configs;
        $user_data['tokenPath'] = $configs['system']['tokenPath'];
        $app = $configs['app']['app_name'];
        $iat = time();
        $exp = $iat * 60 * 60;
        $payload = array(
            "iss" => $app,
            "sub" => $app,
            "aud" => "Login",
            "iat" => $iat,
            "exp" => $exp,
            "user_data" => $user_data
        );
        return JWT::encode($payload, JWTConfig::jwt_key);
    }

    public function login()
    {
        $this->request->checkInput([
            'username' => DataTypes::string,
            'password' => DataTypes::string
        ], true);
        $username = $this->request->getValue('username');
        $password = hash('SHA512', $this->request->getValue('password'));
        $user = $this->mysql->dbRead('users', ['id', 'fullname', 'username'], "WHERE username='$username' AND pass='$password'");
        if (count($user) === 0) {
            $this->response->printError('The account does not exists', 400);
        }
        $user = $user[0];
        $user['rol'] = Roles::STANDARD;
        $token = $this->getUserToken($user);
//        header("Set-Cookie: token=$token; HttpOnly");
        $this->response->addValue('message', 'You have logged in successfully')
            ->addValue('user', $user)
            ->addValue('token', $token)->printResponse();
    }

    public function register()
    {
        global $configs;
        $this->request->checkInput([
            'fullname' => DataTypes::string,
            'username' => DataTypes::string,
            'email' => DataTypes::string,
            'pass' => DataTypes::string
        ], true);
        $fullname = $this->request->getValue('fullname');
        $username = $this->request->getValue('username');
        $email = $this->request->getValue('email');
        $pass = hash('SHA512', $this->request->getValue('pass'));
        if ($this->mysql->existsField(['username' => $username], 'users')) {
            $this->response->printError("The username '$username' already exists", 400);
        }
        if ($this->mysql->existsField(['username' => $username], 'users')) {
            $this->response->printError("The email '$username' already exists", 400);
        }
        $this->mysql->beginTransaction();
        $this->mysql->dbCreate('users', [
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email,
            'pass' => $pass
        ]);
        $user_id = $this->mysql->getLastInsertedId();
        $fileManager = new FileManager($this->response);
        if ($fileManager->filesCount() === 0) {
            $this->mysql->commit();
            $this->response->addValue('message', 'Your account has been successfully created')
                ->printResponse();
        }
        if ($fileManager->filesCount() > 1) {
            $this->mysql->rollBack();
            $this->response->printError('You can only upload one image', 400);
        }
        if (!$fileManager->isValidExtension(['jpg', 'png', 'jpeg'])) {
            $this->mysql->rollBack();
            $this->response->printError('The profile picture extension is invalid', 400);
        }
        $pictureInfo = $fileManager->saveAllDocuments($configs['paths']['resources'])[0];
        $this->mysql->dbCreate('users_images', [
            'id_user' => $user_id,
            'ext' => $pictureInfo['ext'],
            'uuid_internal' => $pictureInfo['uuid'],
            'uuid_external' => Functions::guidv4()
        ]);
        $this->response->addValue('message', 'Your account has been created successfully')
            ->printResponse();
    }

    public function logout()
    {
        global $configs;
        setcookie("token", "", time() - 3600);
        $this->response->addValue('message', 'Session has finished')
            ->printResponse();
    }

    public function decodeToken()
    {
        $request = $this->request;
        $response = $this->response;
        $request->checkInput(["token" => DataTypes::string]);
        $token = $request->getValue("token");
        global $configs;
        try {
            $decoded = JWT::decode($token, JWTConfig::jwt_key, array('HS256'));
            $tokenPath = $decoded->user_data->tokenPath ?? false;
            $configTokenPath = $configs['system']['tokenPath'] ?? false;
            if ($tokenPath && $configTokenPath) {
                if ($tokenPath !== $configTokenPath) {
                    $response->printError("Token inválido (token path; $tokenPath), vuelva a iniciar sesión.", 401);
                }
            } else {
                $response->printError("Token inválido (token path no ha sido especificado)", 401);
            }
            $response->addValue("data", $decoded);
        } catch (\Exception $exception) {
            $response->printError("Token invalido", 400);
            return;
        }
        $response->printResponse();
    }


}