<?php

namespace endpoints\src;

use api\DataTypes;
use api\Request;
use api\Response;
use database\SQLDatabase;
use DataBaseTypes;
use endpoints\EndPoint;
use Roles;
use utils\FileManager;
use utils\Functions;

class Users extends EndPoint
{
    private SQLDatabase $mysql;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response, [
            DataBaseTypes::SQLDatabase
        ]);
        $this->mysql = $this->getSQLDatabase();
    }

    public function uploadProfilePicture()
    {
        global $configs;
        $this->request->checkSession([Roles::NO_ROL]);
        $id_user = $this->request->getPayload()['id'];
        $fileManager = new FileManager($this->response);
        if ($fileManager->filesCount() === 0) {
            $this->response->printError('You must send a profile picture', 4000);
        }
        if (!$fileManager->isValidExtension(['jpg', 'png', 'jpeg'])) {
            $this->response->printError('Picture extension is not valid', 400);
        }
        if ($fileManager->filesCount() > 1) {
            $this->response->printError('You can only upload one image', 400);
        }
        $pictureInfo = $fileManager->saveAllDocuments($configs['paths']['resources'])[0];
        $this->mysql->beginTransaction();
        $this->mysql->dbDelete('users_images', 'id_user', $id_user);
        $this->mysql->dbCreate('users_images', [
            'id_user' => $id_user,
            'ext' => $pictureInfo['ext'],
            'uuid_internal' => $pictureInfo['uuid'],
            'uuid_external' => Functions::guidv4()
        ]);
        $this->response->addValue('message', 'Profile Picture has been updated')
            ->printResponse();
    }

    public function myProfilePicture()
    {
        global $configs;
        $this->request->checkSession([Roles::NO_ROL]);
        $id_user = $this->request->getPayload()['id'];
        $picture = $this->mysql->dbRead('users_images', ['uuid_internal', 'ext'], "WHERE id_user=$id_user");
        if (count($picture) === 0) {
            header("Content-Type: image/png");
            die(file_get_contents("https://cdn-icons-png.flaticon.com/512/149/149071.png"));
        }
        $picture = $picture[0];
        header("Content-Type: image/${picture['ext']}");
        readfile("${$configs['paths']['resources']}/${picture['uuid_internal']}/${picture['ext']}");
        die();
    }

    public function profilePictureByUuid()
    {
        global $configs;
        $this->request->checkSession([Roles::NO_ROL]);
        $this->request->checkInput([
            'uuid' => DataTypes::string
        ]);
        $uuid = $this->request->getValue('uuid');
        $picture = $this->mysql->dbRead('users_images', ['uuid_internal', 'ext'], "WHERE uuid_external='$uuid'");
        if (count($picture) !== 0) {
            $picture = $picture[0];
            header("Content-Type: image/${picture['ext']}");
            readfile("${$configs['paths']['resources']}/${picture['uuid_internal']}/${picture['ext']}");
            die();
        }
        header("Content-Type: image/png");
        die(file_get_contents("https://cdn-icons-png.flaticon.com/512/149/149071.png"));
    }

    public function getUserInfo()
    {
        $this->request->checkSession([Roles::NO_ROL]);
        $this->request->checkInput([
            'id' => DataTypes::integer,
        ]);
        $id = $this->request->getValue('id');
        $user = $this->mysql->dbRead('users', ['fullname', 'username'], "WHERE id=$id ORDER BY registered_at DESC");
        if (count($user) === 0) {
            $this->response->printError('User does not exist');
        }
        $user = $user[0];
        $this->response->addValue('data', $user)
            ->printResponse();
    }

}