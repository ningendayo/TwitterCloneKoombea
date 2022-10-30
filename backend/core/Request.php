<?php


namespace api;

use api\JWTConfig;
use Exception;
use Firebase\JWT\JWT;
use Roles;

class Request
{
    private array $request = [];
    private Response $response;

    public function __construct($request)
    {
        $this->request = $request;
        $this->response = new Response();
    }

    /**
     * @param $key
     * @param false $escape
     * @return mixed
     */
    public function getValue($key, bool $escape = true)
    {
        if (!isset($this->request[$key])) {
            /*$this->response->printError("El campo '$key' no existe en la solicitud");*/
            return null;
        }
        if ($escape) {
            return (gettype($this->request[$key]) === 'string') ? htmlspecialchars($this->request[$key]) : ($this->request[$key]);
        }
        return $this->request[$key];
    }

    /**
     * GET RAW REQUEST
     */
    public function getRawRequest(): array
    {
        return $this->request;
    }

    public function removeValue($key)
    {
        unset($this->request[$key]);
    }

    /**
     * This method will check if the user cookie 'token' is valid, this will represent
     * the user as a logged one.
     * If this method determinate that the user is not authenticated or the recieve rol is not the same
     * that the specified in the token, the code will die with a json response for the client (FrontEnd)
     * @param array $roles ((Roles que pueden verificar al usuario)Even when this parameter is an string, just send values availables as fields in /api/Roles.php class) if you send an 'NO_ROL' field front the before mentioned file this method wont let the sequence die when having a valid session token not caring about the rol inside the token
     */
    public function checkSession(array $roles): bool
    {
        global $configs;
        if (!isset($_COOKIE['token'])) {
            $this->response->printError('Usuario no autenticado', 401);
        }
        $token = $_COOKIE['token'];
        try {
            $payload = JWT::decode($token, JWTConfig::jwt_key, array('HS256'));
            $tokenPath = $payload->user_data->tokenPath ?? false;
            $configTokenPath = $configs['system']['tokenPath'] ?? false;
            if ($tokenPath && $configTokenPath) {
                if ($tokenPath !== $configTokenPath) {
                    $this->response->printError("Token inválido (token path; $tokenPath), vuelva a iniciar sesión en el sistema.", 401);
                }
            } else {
                $this->response->printError("Token inválido (token path no ha sido especificado)", 401);
            }
            if (in_array(Roles::NO_ROL, $roles)) {
                return true;
            }
            $user_rol = $payload->user_data->rol;
            $has = false;
            foreach ($roles as $rol) {
                if ($user_rol === $rol) {
                    $has = true;
                    break;
                }
            }
            if (!$has) {
                $this->response->printError('No tienes permisos para realizar esta acción', 401);
                return false;
            }
            return true;
        } catch (Exception $e) {
            $this->response->printError('Autenticación no válida: ' . $e->getMessage(), 401);
        }
        return false;
    }

    public function checkInput($array, $die_on_empty_field = false)
    {
        $message = '';
        $error = false;
        foreach ($array as $key => $value) {
            if (!isset($this->request[$key])) {
                $message = "The field '$key' has not been found in the request";
                $error = true;
                break;
            } else {
                $datatype = gettype($this->request[$key]);
                if ($value === DataTypes::dynamic) {
                    continue;
                }
                if ($value == DataTypes::number && ($datatype == DataTypes::integer || $datatype == DataTypes::double)) {
                    $datatype = DataTypes::number;
                }
                if ($datatype != $value) {
                    $message = "The data type of the field '$key' in the request must be '$value' but the received was '$datatype'";
                    $error = true;
                    break;
                }
                if ($datatype == DataTypes::array && count($this->request[$key]) == 0 && $die_on_empty_field) {
                    $message = "The field '$key' in the request is empty the request can not be processed";
                    $error = true;
                    break;
                }
                if ($datatype == DataTypes::string && $die_on_empty_field &&/* !in_array($key, $exluce_empty) &&*/ $this->request[$key] == '') {
                    $message = "The field '$key' in the request has and empty string";
                    $error = true;
                    break;
                }
            }
        }
        if ($error) {
            $this->response->printError($message, 400);
        }
    }

    public function checkInputArrayValuesTypes(string $key, array $datatypes)
    {
        //&& count($this->request[$key]) !== 0
        if (isset($this->request[$key]) && gettype($this->request[$key]) === DataTypes::array) {
            $str_datatypes = implode(",", $datatypes);
            foreach ($this->request[$key] as $value) {
                $type = gettype($value);
                if (!in_array($type, $datatypes)) {
                    $this->response->printError("All values of array '$key' must be one of the next: [$str_datatypes] but a value with '$type' was found", 400);
                }
            }
        } else {
            //or the array is empty
            $this->response->printError("The key '$key' is not an array", 400);
        }
    }

    public function getPayload(): array
    {
        if (!isset($_COOKIE['token'])) {
            $this->response->printError('Usuario no autenticado', 401);
        }
        $token = $_COOKIE['token'];
        try {
            $payload = JWT::decode($token, JWTConfig::jwt_key, array('HS256'));
            $aux = (array)$payload->user_data;
            if (isset($aux['rol'])) {
                $aux['rol'] = intval($aux['rol']);
            }
            return $aux;
        } catch (\Exception $e) {
            $this->response->printError('Autenticación no válida: ' . $e->getMessage(), 401);
            return [];
        }
    }
}




