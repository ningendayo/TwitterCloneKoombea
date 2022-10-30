<?php

namespace utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Functions
{
    function includeDir($path)
    {
        $dir = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($dir);
        foreach ($iterator as $file) {
            $fname = $file->getFilename();
            if (preg_match('%\.php$%', $fname)) {
                require_once($file->getPathname());
            }
        }
    }

    public static function guidv4(): string
    {
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }


    /**
     * This method check if the password pass some regex
     * 1) At least one upper case letter
     * 2) At least one lower case letter
     * 3) At least one special character
     * @param string $password (The password to be judge)
     * @return bool (True if the password fullfil the regex requirements and false if not)
     */
    function passwordRegex(string $password): bool
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);
        return $uppercase && $lowercase && $number && $specialChars && (strlen($password) >= 8);
    }

    public static function removeAccents(string $string)
    {
        $string = str_replace(
            array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
            array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
            $string
        );
        $string = str_replace(
            array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
            array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
            $string);
        $string = str_replace(
            array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
            array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
            $string);
        $string = str_replace(
            array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
            array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
            $string);
        $string = str_replace(
            array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
            array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
            $string);
        $string = str_replace(
            array('Ñ', 'ñ', 'Ç', 'ç'),
            array('N', 'n', 'C', 'c'),
            $string
        );
        return $string;
    }

    public static function str_contains($str, $search): bool
    {
        return (strpos($str, $search) !== false);
    }

    /**
     * Procesa los planes para aplicar los cupones, los upgrades y el impuesto por pais
     * Este metodo regresa el pianel junto con sus valores para el calculo del costo
     * @param object $planes
     * @param object|null $cupon
     * @param object|null $upgrades
     * @param int $asegurados
     * @param int $viaje_duracion
     * @param float $impuesto
     */
    public static function procesarCosto(object &$planes, ?object $cupon, ?object $upgrades, int $asegurados, int $viaje_duracion, float $impuesto)
    {
        $upgrades = json_decode(json_encode($upgrades), true);
        foreach ($planes as &$item) {
            $aplicar_cupon = false;
            if ($cupon !== null) {
                $codigoRecord = $cupon;
                $porcentaje = $codigoRecord['porcentaje'];
                if (isset($codigoRecord['id_punto_venta'])) {
                    $aplicar_cupon = true;
                    $item['costo_original'] = $item['costo'];
                    $item['costo'] -= ($porcentaje / 100) * $item['costo'];
                    $item['costo2'] -= ($porcentaje / 100) * $item['costo2'];
                } else {
                    if (isset($codigoRecord['id_plan']) && intval($item['id']) === $codigoRecord['id_plan']) {
                        $aplicar_cupon = true;
                        $item['costo_original'] = $item['costo'];
                        $item['costo'] -= ($porcentaje / 100) * $item['costo'];
                        $item['costo2'] -= ($porcentaje / 100) * $item['costo2'];
                    }
                }
            }
            $costo_antes_de_upgrade = $item['costo'];
            $costo_antes_de_upgrade2 = $item['costo2'];
            if ($upgrades !== null) {
                foreach ($upgrades as $upgrade) {
                    $tipo_precio = $upgrade['tipo_precio'];
                    $valor_precio = $upgrade['valor_precio'];
                    if ($cupon !== null && $aplicar_cupon) {
                        $porcentaje = $cupon['porcentaje'];
                        $valor_precio -= ($porcentaje / 100) * $valor_precio;
                        $upgrade['costo_total'] -= ($porcentaje / 100) * $upgrade['costo_total'];
                        $upgrade['comision_regional'] -= ($porcentaje / 100) * $upgrade['comision_regional'];
                        $upgrade['comision_punto_venta'] -= ($porcentaje / 100) * $upgrade['comision_punto_venta'];
                        $upgrade['comision_vendedor'] -= ($porcentaje / 100) * $upgrade['comision_vendedor'];
                        $upgrade['bono_vendedor'] -= ($porcentaje / 100) * $upgrade['bono_vendedor'];
                        $upgrade['gasto_mkt'] -= ($porcentaje / 100) * $upgrade['gasto_mkt'];
                        $upgrade['descuento'] -= ($porcentaje / 100) * $upgrade['descuento'];
                        $upgrade['profit'] -= ($porcentaje / 100) * $upgrade['profit'];
                        $upgrade['tarifa'] = $upgrade['costo_total'] + $upgrade['comision_regional'] + $upgrade['comision_punto_venta']
                            + $upgrade['comision_vendedor'] +
                            $upgrade['bono_vendedor'] +
                            $upgrade['gasto_mkt'] +
                            $upgrade['descuento'] +
                            $upgrade['profit'];
                    }
                    for ($i = 0; $i < $asegurados; $i++) {
                        if ($tipo_precio === 'PORCENTAJE') {
                            $item['costo'] += ($valor_precio / 100) * $costo_antes_de_upgrade;
                            $item['costo2'] += ($valor_precio / 100) * $costo_antes_de_upgrade2;
                            break;
                        } else {
                            $costo_aux = $upgrade['tarifa'];
                            $costo_aux2 = $upgrade['tarifa'] - $upgrade['comision_punto_venta'];
                            if ($tipo_precio === 'NUMERICO POR DIAS') {
                                $costo_aux *= $viaje_duracion;
                                $costo_aux2 *= $viaje_duracion;
                            }
                            $item['costo'] += $costo_aux;
                            $item['costo2'] += $costo_aux2;
                        }
                    }
                }
            }
//            $item['costo'] += ($impuesto / 100)*$item['costo'];
//            $item['costo2'] += ($impuesto / 100)*$item['costo2'];
        }
    }

    public static function getCompletedPurchaseMailBody($compradorFullName, $inner_assiste_dark_logo): string
    {
        global $configs;
        //template notificacion comprador
        $htmlNotificacionComprador = file_get_contents("{$configs['paths']['mailTemplates']}/notifications/notificacion_comprador.php");
        $icon = $configs['paths']['webImagesFrontend'] . "/logos/InnerAssisteDark.png";
        $htmlNotificacionComprador = str_replace('{{inner_logo}}', $icon, $htmlNotificacionComprador);
        $htmlNotificacionComprador = str_replace('{{nombre_pasajero}}', $compradorFullName, $htmlNotificacionComprador);
        $htmlNotificacionComprador = str_replace('{{nombre_pasajero_ingles}}', $compradorFullName, $htmlNotificacionComprador);
        //fin
        return $htmlNotificacionComprador;
    }

    public static function obtenerPlan(int $id, array $planes): array
    {
        $target_plan = null;
        foreach ($planes as $plane) {
            if ($plane['id'] === $id) {
                $target_plan = $plane;
                break;
            }
        }
        return (array)$target_plan;
    }

}

