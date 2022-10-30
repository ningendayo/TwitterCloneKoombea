<?php

namespace endpoints\src;

use api\DataTypes;
use DataBaseTypes;
use Roles;

class Following extends \endpoints\EndPoint
{

    private \database\SQLDatabase $mysql;

    public function __construct(\api\Request $request, \api\Response $response)
    {
        parent::__construct($request, $response, [
            DataBaseTypes::SQLDatabase
        ]);
        $this->mysql = $this->getSQLDatabase();
    }

    public function follow()
    {
        $this->request->checkSession([Roles::NO_ROL]);
        $id_user = $this->request->getPayload()['id'];
        $rawRequest = $this->request->getRawRequest();
        $toFollowId = 0;
        if (isset($rawRequest['toFollowId'])) {
            $this->request->checkInput([
                'toFollowId' => DataTypes::integer
            ]);
            $toFollowId = $this->request->getValue('toFollowId');
        }
        if (isset($rawRequest['toFollowUserName'])) {
            $this->request->checkInput([
                'toFollowUserName' => DataTypes::string
            ]);
            $toFollowUserName = $this->request->getValue('toFollowUserName');
            $check = $this->mysql->dbRead('users', ['id'], "WHERE username='$toFollowUserName'");
            if (count($check) === 0) {
                $this->response->printError('The specified username has not been found');
            }
            $toFollowId = $check[0]['id'];
        }
        if ($toFollowId === 0) {
            $this->response->printError('The user can not be found');
        }
        if ($this->mysql->existsField(['id_user_a' => $id_user, 'AND', 'id_user_b' => $toFollowId], 'following')) {
            $this->response->printError('You already follow this user', 400);
        }
        if ($id_user === $toFollowId) {
            $this->response->printError('You can not follow yourself', 400);
        }
        $this->mysql->dbCreate('following', ['id_user_a' => $id_user, 'id_user_b' => $toFollowId]);
        $this->response->addValue('id', $toFollowId);
        $this->response->addValue('message', 'Now you follow this user')
            ->printResponse();

    }

    public function peopleFollow()
    {
        $this->request->checkSession([Roles::NO_ROL]);
        $id_user = $this->request->getPayload()['id'];
        $original_request_user = $id_user;
        $rawRequest = $this->request->getRawRequest();
        $this->request->checkInput(['op' => DataTypes::string, 'target' => DataTypes::string], true);
        if (isset($rawRequest['id_user'])) {
            $this->request->checkInput([
                'id_user' => DataTypes::integer
            ]);
            $id_user = $this->request->getValue('id_user');
        }
        $ops = [
            'count' => ['COUNT(following.id) as cantidad'],
            'list' => [
                'users.id',
                'users.fullname',
                'users.username',
                'following.registered_at as date'
            ]
        ];
        $op = $this->request->getValue('op');
        if (!in_array($op, array_keys($ops))) {
            $this->response->printError('Invalid value for argument op in the request');
        }
        $pagination_cond = "";
        if ($op === 'list') {
            $this->request->checkInput([
                'last_record' => DataTypes::integer
            ]);
            $last_record = $this->request->getValue('last_record');
            $pagination_cond = "AND users.id > $last_record";
        }
        $targets = [
            'myFollowers' => ['a', 'b'],
            'IFollow' => ['b', 'a']
        ];
        $target = $this->request->getValue('target');
        if (!in_array($target, array_keys($targets))) {
            $this->response->printError('Invalid value for argument target in the request');
        }
        if (($id_user != $original_request_user || $target === 'myFollowers') && $op === 'list') {
            $ops['list'][] = "(
                SELECT  IF(f_internal.id IS NULL,'NOT_FOLLOWING','FOLLOWING') FROM following f_internal WHERE f_internal.id_user_a = $original_request_user AND f_internal.id_user_b = users.id
            ) as iFollow";
        }
        $target_keys = $targets[$target];
        $data = $this->mysql->dbRead('following', $ops[$op], "
        INNER JOIN users ON users.id=following.id_user_${target_keys[0]}
        WHERE following.id_user_${target_keys[1]}=$id_user $pagination_cond ORDER BY users.fullname ASC LIMIT 10");
        $this->response->addValue('data', $data)->printResponse();
    }

    public function doIFollow()
    {
        $this->request->checkSession([Roles::NO_ROL]);
        $id_user = $this->request->getPayload()['id'];
        $this->request->checkInput([
            'id_user' => DataTypes::integer
        ]);
        $target_user = $this->request->getValue('id_user');
        $this->response->addValue('exists', $this->mysql->existsField([
            'id_user_a' => $id_user,
            'AND',
            'id_user_b' => $target_user
        ], 'following'))->printResponse();
    }

    public function unFollow()
    {
        $this->request->checkSession([Roles::NO_ROL]);
        $id_user = $this->request->getPayload()['id'];
        $this->request->checkInput([
            'id_user' => DataTypes::integer
        ]);
        $target_user = $this->request->getValue('id_user');
        $this->mysql->dbQuery("DELETE FROM `following` WHERE id_user_a=$id_user AND id_user_b=$target_user");
        $this->response->addValue('message', 'Yo do not follow this user anymore')
            ->printResponse();
    }


}