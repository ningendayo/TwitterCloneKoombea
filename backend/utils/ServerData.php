<?php

namespace utils;

use DateTime;

class ServerData
{

    public static function getDate($fulldate = false): string
    {
        global $configs;
        date_default_timezone_set($configs['app']['defaultTimeZone']);
        if ($fulldate) {
            return date('d/m/Y H:i:s');
        }
        return date('d/m/Y');
    }

    public static function getTimeStamp(): int
    {
        global $configs;
        return (new DateTime(null, new \DateTimeZone($configs['app']['defaultTimeZone'])))->getTimestamp() * 1000;
    }

    public static function getIp(): string
    {
        return ($_SERVER['HTTP_CLIENT_IP'] ?? isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    }

    public static function checkDate($date): bool
    {
        $date_array = explode('/', $date);
        if (count($date_array) != 3) {
            return false;
        }
        if (!is_numeric($date_array[0]) || !is_numeric($date_array[1]) || !is_numeric($date_array[2])) {
            return false;
        }
        $dia = $date_array[0];
        $mes = $date_array[1];
        $year = $date_array[2];
        return checkdate($mes, $dia, $year);
    }

    public static function checkDateFormat($date, $format = 'Y-m-d'): bool
    {
        $dt = DateTime::createFromFormat($format, $date);
        return $dt && $dt->format($format) === $date;
    }

    /**
     * @param string $date_format
     * @return string|null  d/m/Y or null if provided date has no a valid format
     */
    public static function dateFromUsaToLa(string $date_format): ?string
    {
        if (self::checkDateFormat($date_format, 'd/m/Y')) {
            return $date_format;
        }
        if (self::checkDateFormat($date_format, 'Y-m-d')) {
            $partes = explode('-', $date_format);
            $anio = ($partes[0]);
            $mes = ($partes[1]);
            $dia = ($partes[2]);
            return "$dia/$mes/$anio";
        }
        return null;
    }

    public static function usd_to_mxn2(): ?array
    {
        global $configs;
        $api_key = $configs['tokens']['free-currconv'];
        $curl = curl_init("https://free.currconv.com/api/v7/convert?q=USD_MXN&compact=ultra&apiKey=$api_key");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        $result = curl_exec($curl);
        $result = preg_replace("/\xEF\xBB\xBF/", "", $result);
        curl_close($curl);
        $responseArray = @json_decode($result, true);
        $responseArray['source'] = 'free.currconv';
        if (!isset($responseArray['USD_MXN'])) {
            return null;
        }
        return $responseArray;
//        return [
//            'USD_MXN' => 19.98
//        ];
    }

    public static function usd_to_mxn3(): ?array
    {
        global $configs;
        $api_key = $configs['tokens']['freecurrencyapi'];
        $url = "https://freecurrencyapi.net/api/v2/latest?apikey=$api_key&base_currency=USD";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        $responseArray = json_decode($response, true);
        if (!$responseArray) {
            return null;
        }
        curl_close($ch);
        return [
            'source' => 'freecurrencyapi',
            'USD_MXN' => $responseArray['data']['MXN']
        ];
    }

    public static function usd_to_mxn1(): ?array
    {
        $url = "https://www.banxico.org.mx/tipcamb/llenarTiposCambioAction.do?idioma=sp&_=" . time() * 1000;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        $html = new \DOMDocument();
        if (@$html->loadHTML($response)) {
            $div = $html->getElementById('tdSF43718');
            if ($div) {
                return [
                    'source' => 'banxico',
                    'USD_MXN' => floatval(trim($div->nodeValue))
                ];
            } else {
                return null;
            }
        } else {
            return null;
        }

    }


    /**
     * Duelves la diferencia entre dos horas en horas
     * @param string $time1 Hora inferior en formato de 24 horas
     * @param string $time2 Higer time in 24 houts froma
     * @return float Differences between two times in hours
     */
    public static function getHoursFromTimes(string $time1, string $time2): float
    {
        $time1 = strtotime($time1);
        $time2 = strtotime($time2);
        return round(abs($time2 - $time1) / 3600, 2);
    }

    /**
     * Calcula la diferencia entre dos fechas, los formatos de la fecha deben ser d/m/Y
     * @param $date1
     * @param $date2
     * @return int|null
     */
    public static function getDifferenceDates($date1, $date2): ?int
    {
        try {
            $array_date_1 = explode('/', $date1);
            $array_date_2 = explode('/', $date2);
            $new_date_1 = $array_date_1[2] . '/' . $array_date_1[1] . '/' . $array_date_1[0];
            $new_date_2 = $array_date_2[2] . '-' . $array_date_2[1] . '-' . $array_date_2[0];
            $date1 = new DateTime($new_date_1);
            $date2 = new DateTime($new_date_2);
            return $date1->diff($date2)->format('%r%a');
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function convertDaysToYears($days): int
    {
        return (int)($days / 365);
    }

    /** Return the difference in years of the priveded dates
     * @param $date1 - d/m/Y lower
     * @param $date2 - d/M/Y
     * @return int|null years
     */
    public static function diffYears($date1, $date2): ?int
    {
        try {
            $date1 = new DateTime(ServerData::changeDateFormat($date1, 'd/m/Y', 'Y-m-d'));
            $date2 = new DateTime(ServerData::changeDateFormat($date2, 'd/m/Y', 'Y-m-d'));
            return $date2->diff($date1)->y;
        } catch (\Exeption $e) {
            return null;
        }
    }


    /**
     * It changes the provided date with the provided format to a specified output date format
     * @param string $date
     * @param string $inputFormat
     * @param string $outputFormat
     * @return string
     */
    public static function changeDateFormat(string $date, string $inputFormat, string $outputFormat): string
    {
        return DateTime::createFromFormat($inputFormat, $date)->format($outputFormat);
    }

    public function checkemail($str): bool
    {
        return !!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str);
    }

}