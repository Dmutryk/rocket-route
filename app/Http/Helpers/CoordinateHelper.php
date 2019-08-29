<?php

namespace App\Http\Helpers;

class CoordinateHelper
{

    /**
     * @param array $notams
     * @return array
     */
    public function getCoordinatesFromNotam(array $notams): array
    {
        $markers = [];
        $addedMarkers = [];

        foreach ($notams as $elem) {

            $itemQArray = explode('/', $elem['ItemQ']);
            $coordinates = end($itemQArray);

            if (!empty($coordinates)) {
                $latitudeNegative = false;
                $longitudeNegative = false;

                $Nposition = strpos($coordinates, 'N');
                if (!$Nposition) {
                    $Nposition = strpos($coordinates, 'S');
                    $latitudeNegative = true;
                }

                $Wposition = strpos($coordinates, 'E');
                if (!$Wposition) {
                    $Wposition = strpos($coordinates, 'W');
                    $longitudeNegative = true;
                }

                $latitude = substr($coordinates, 0, $Nposition);
                $longitude = substr($coordinates, $Nposition + 1, $Wposition - $Nposition - 1);

                $latHours = substr($latitude, 0, 2);
                $latMinutes = substr($latitude, 2, 2) / 60;

                $longHours = substr($longitude, 0, 3);
                $longMinutes = substr($longitude, 3, 2) / 60;

                $x = number_format($latHours + $latMinutes, 6, '.', '');
                $y = number_format($longHours + $longMinutes, 6, '.', '');

                if ($latitudeNegative) {
                    $x = '-' . $x;
                }

                if ($longitudeNegative) {
                    $y = '-' . $y;
                }

                if (!in_array($coordinates, $addedMarkers)) {
                    $markers[] = ['x' => $x, 'y' => $y, 'notam' => $elem];
                    $addedMarkers[] = $coordinates;
                }
            }
        }


        return $markers;
    }
}
