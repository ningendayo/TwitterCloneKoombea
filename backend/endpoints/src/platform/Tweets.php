<?php

namespace endpoints\src;

use api\DataTypes;
use api\Request;
use api\Response;
use database\SQLDatabase;
use DataBaseTypes;
use endpoints\EndPoint;

class Tweets extends EndPoint
{

    private SQLDatabase $mysql;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response, [
            DataBaseTypes::SQLDatabase
        ]);
        $this->mysql = $this->getSQLDatabase();
    }

    public function publish()
    {
        //A MIDDLEWARE IMPLEMENTATION CAN BE ALSO USED TO CHECK SESSION
        $this->request->checkSession([\Roles::NO_ROL]);
        $payload = $this->request->getPayload();
        $id_user = $payload['id'];
        $this->request->checkInput([
            'description' => DataTypes::string
        ], true);
        $description = $this->request->getValue('description');
        if (mb_strlen($description) > 280) {
            $this->response->printError('Tweets maximum length should be 280 characters', 400);
        }
        $this->mysql->dbCreate('tweets', [
            'id_user' => $id_user,
            'description' => $description
        ]);
        $this->response->addValue('message', 'Tweet has been created successfully')
            ->printResponse();
    }

    public function feed()
    {
        $this->request->checkSession([\Roles::NO_ROL]);
        $payload = $this->request->getPayload();
        $id_user = $payload['id'];
        $tweets = $this->mysql->dbRead("(SELECT
    users.id as ui,
    users.fullname,
	users.username,
	tweets.id,
	tweets.id_user,
	tweets.description,
	tweets.registered_at
FROM
	tweets
	INNER JOIN following ON following.id_user_b = tweets.id_user 
	INNER JOIN users ON users.id = tweets.id_user
WHERE
	following.id_user_a =$id_user  UNION ALL SELECT
	users.id as ui,
    users.fullname,
	users.username,
	tweets.id,
	tweets.id_user,
	tweets.description,
	tweets.registered_at
FROM
	tweets
	INNER JOIN users ON users.id = tweets.id_user 
	WHERE id_user=$id_user) alldata", ['*'], "
        ORDER BY registered_at DESC");
        $this->response->addValue('data', $tweets)
            ->printResponse();
    }

    public function getUserTweets()
    {
        $this->request->checkSession([\Roles::NO_ROL]);
        $rawRequest = $this->request->getRawRequest();
        $userId = 0;
        if (isset($rawRequest['userId'])) {
            $this->request->checkInput([
                'userId' => DataTypes::integer
            ]);
            $userId = $this->request->getValue('userId');
        }
        if (isset($rawRequest['userName'])) {
            $this->request->checkInput([
                'userName' => DataTypes::string
            ]);
            $userName = $this->request->getValue('userName');
            $check = $this->mysql->dbRead('users', ['id'], "WHERE username='$userName'");
            if (count($check) === 0) {
                $this->response->printError('The specified username has not been found');
            }
            $userId = $check[0]['id'];
        }
        $tweets = $this->mysql->dbRead('tweets', ['*'], "WHERE id_user=$userId");
        $this->response->addValue('data', $tweets)
            ->printResponse();
    }


}