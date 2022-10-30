<?php

namespace endpoints\src;

use api\DataTypes;
use api\Request;
use api\Response;
use DataBaseTypes;
use endpoints\EndPoint;

class MongoTest extends EndPoint
{


    /**
     * @var string[]
     */
    private array $mongoObjDb;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response, [DataBaseTypes::SQLDatabase, DataBaseTypes::MongoDB]);
        $this->mongoObjDb = [
            'trashDatabase' => 'trashDatabase',
            'trashCollection' => 'trashCollection'
        ];
    }

    function create()
    {
        $request = $this->request;
        $request->checkInput(["nombre" => DataTypes::string, "edad" => DataTypes::integer]);
        $nombre = $request->getValue("nombre", true);
        $edad = $request->getValue("edad");
        $mongoDB = $this->getMongoDB();
        $database = $this->mongoObjDb['trashDatabase'];
        $collection = $this->mongoObjDb['trashCollection'];
        $result = $mongoDB->insertData($database, $collection, [
            "nombre" => $nombre,
            "edad" => $edad
        ], ["nombre" => $nombre]);
        if (!$result) {
            $this->response->printError("Ya existe un registro con este nombre", 401);
            return;
        }
        $this->response->addValue("message", "Tus datos han sido registrados correctamente $result")->printResponse();
    }

}