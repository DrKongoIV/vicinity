<?php namespace knhackt;

use base\SuperModel;

class Model extends SuperModel
{
    /**
     * @return Model
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }


    public function getVoteDistrictInfo($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT WB_NR as id, SHAPESTArea as area, SHAPESTLength as perimeter FROM wahlbezirke WHERE WB_NR=$id");
        if (isset($data[0]))
            return $data[0];
        return null;
    }

    public function getVoteDistrictParticipation($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT WAHLBETEILIGUNG as participation FROM bundestag WHERE  Wahlbezirk_Nr=$id")[0];
        return $data['participation'];

    }

    public function getVoteDistrictOrientation($id)
    {
        $electionResult = $this->connection->selectAssociativeValues("SELECT Z_CDU/Z_GUELTIG as cdu, Z_SPD/Z_GUELTIG as spd, Z_GRUENE/Z_GUELTIG as gruene, Z_FDP/Z_GUELTIG as fdp, Z_AfD/Z_GUELTIG as afd, Z_Linke/Z_GUELTIG as linke, Wahlbezirk_Nr as id FROM bundestag WHERE Wahlbezirk_Nr=$id")[0];

        $dEco = 0;
        $dPol = 0;

        foreach ($electionResult as $name => $result) {

            $name = strtoupper($name);

            $partyOrientation = $this->connection->selectAssociativeValues("SELECT wirtschaftl as eco, polit as pol FROM parteien WHERE name LIKE '$name'")[0];

            $eco = $partyOrientation['eco'];
            $pol = $partyOrientation['pol'];


            $dEco += $eco * doubleval($result);
            $dPol += $pol * doubleval($result);

        }

        return ["eco" => $dEco, "pol" => $dPol];
    }

    public function getCityBlockInfo($id)
    {

        $data = $this->connection->selectAssociativeValues("SELECT STV as id, STT_HR as districtId, SHAPESTArea as area, SHAPESTLength as perimeter FROM stadtviertelflaeche WHERE STV=$id");
        if (isset($data[0]))
            return $data[0];
        return null;
    }

    public function getCityBlockInhabitantInfo($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT YEAR(Stand_Einwohner) as state, EINW_HW_GESAMT as inhabitantsTotal, EINW_HW_FRAUEN as inhabitantsFemale, EINW_HW_DEUTSCH as inhanbitantsGerman, EINW_HW_AUSLAENDER as inhabitantsForeigner, EINW_HW_18_bis_unter_30 as inhabitantsUnder18, EINW_HW_18_bis_unter_30 as inhabitants18to30, EINW_HW_30_bis_unter_40 as inhabitants30to40, EINW_HW_40_bis_unter_50 as inhabitants40to50, EINW_HW_50_bis_unter_60 as inhabitants50to60, EINW_HW_60_und_älter as  inhabitantsOver60, Haushalte as haushold FROM einwohner_nach_gruppen WHERE STADTVIERTEL=$id ORDER BY state desc");
        return $data;
    }

    public function getDistrictInfo($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT STT as id, SHAPESTArea as area, SHAPESTLength as perimeter, STT_NAME as name FROM stadtteilflaeche WHERE STT=$id");
        if (isset($data[0]))
            return $data[0];
        return null;
    }

    public function getBuildingBlockInfo($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT BB_NR as id, WAHL as electionDistrictId,SHAPESTArea as area, SHAPESTLength as perimeter, STRNR as streetId, STV as cityBlockId, PLZ as plz FROM baublockflaeche WHERE BB_NR=$id");
        if (isset($data[0]))
            return $data[0];
        return null;
    }

    public function getBuildingBlockInhabitantInfo($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT EINW_HW_Gesamt as total FROM einwohner_raw WHERE BAUBLOCK=$id");
        if (isset($data[0]))
            return $data[0];
        return null;
    }

    public function getBuildingInfo($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT FID as id, BAUBLOCK as buildingBlockId, BAUJAHR_GEBAEUDE_GRUPPIERT as constructionYearStr, BAUJAHR as constructionYearNum, ANZAHL_WOHNUNG_GRUPPIERT as apartmentsStr, ANZAHL_WOHNUNGEN as apartmentsNum from gebaeudebestand WHERE FID=$id");
        if (isset($data[0]))
            return $data[0];
        return null;
    }

    public function getCityBlockIdByDistrict($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT STV as cityBlockId FROM stadtviertelflaeche WHERE STT_HR=$id");

        return $data;
    }

    public function getBuildingBlockIdByCityBlock($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT BB_NR as buildingBlockId FROM baublockflaeche WHERE STV=$id");

        return $data;
    }

    public function getBuildingBlockIdByVoteDistrict($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT BB_NR as buildingBlockId FROM baublockflaeche WHERE WAHL=$id");

        return $data;
    }

    public function getBuildingsByBuildingBlock($id)
    {
        $data = $this->connection->selectAssociativeValues("SELECT FID as buildingId FROM gebaeudebestand WHERE BAUBLOCK=$id");

        return $data;
    }


    public function getData($type, $id)
    {
        $type = strtolower($type);

        $resp = null;

        switch ($type) {
            case"district":
                $resp = ((new District($id)))->completeData();
                break;
            case"cityblock":
                $resp = ((new CityBlock($id)))->completeData();
                break;
            case"votedistrict":
                $resp = ((new VoteDistrict($id)))->completeData();
                break;
            case"buildingblock":
                $resp = ((new BuildingBlock($id)))->completeData();
                break;
            case"city":
                $resp = (new City())->completeData();
                break;
            default:
                break;
        }

        return $resp;

    }

    public function getAllIds($type)
    {
        $type = strtolower($type);

        $sql = "";

        switch ($type) {
            case"district":
                $sql = "SELECT STT as id FROM  stadtteilflaeche";
                break;
            case"cityblock":
                $sql = "SELECT STV as id FROM stadtviertelflaeche";
                break;
            case"votedistrict":
                $sql = "SELECT WB_NR as id FROM wahlbezirke";
                break;
            case"buildingblock":
                $sql = "SELECT BB_NR as id FROM baublockflaeche";
                break;
            default:
                return null;
                break;
        }

        $res = array();

        foreach ($this->connection->selectAssociativeValues($sql) as $id) {

            array_push($res, intval($id['id']));
        }

        return $res;

    }

    public function getAverageInfo($type, $id)
    {
        $type = strtolower($type);

        $resp = null;

        switch ($type) {
            case"district":
                $resp = ((new District($id)))->completeData()->evaluate();
                break;
            case"cityblock":
                $resp = ((new CityBlock($id)))->completeData()->evaluate();
                break;
            case"votedistrict":
                //$resp =((new V($id)))->evaluate();
                break;
            case"buildingblock":
                $resp = ((new BuildingBlock($id)))->completeData()->evaluate();
                break;
            default:
                break;
        }

        return $resp;

    }


    private function _ringsToDistricts($r, $id)
    {
        $resp = array();
        foreach ($r as $point) {
            $rings = array();

            foreach ($point as $polygonPoint) {
                $point = new \stdClass();
                $point->lng = $polygonPoint[0];
                $point->lat = $polygonPoint[1];
                array_push($rings, $point);
            }
            $dis = new DrawableDistrict($id, $rings);
            array_push($resp, $dis);
        }
        return $resp;

    }


    public function getSortables($type)
    {


        if ($type == "city") {
            return [];
        }

        $sortables =
            ["all" => ["inhabitantsPerSqKm", "area", "perimeter", "inhabitantsTotal", "orientationPolitical", "orientationEconomical", "participation", "averageAge"],
            ];


        return isset($sortables[$type]) ? array_merge($sortables[$type], $sortables['all']) : $sortables['all'];
    }

    public function fetchVisualizeData($type)
    {
        $type = strtolower($type);
        switch ($type) {
            case "city":
            case "district":
                $idName = "STT_NR";
                $url = "https://secure.konstanz.de/geoportal/rest/services/Fachdaten/KLRG_Stadtteilflaeche_WGS84/MapServer/0/query?where=1%3D1&outFields=*&outSR=4326&f=json";
                break;
            case "votedistrict":
                $idName = "WB_NR";
                $url = "https://secure.konstanz.de/geoportal/rest/services/Fachdaten/KLRG_Wahlbezirke_WGS84/MapServer/0/query?where=1%3D1&outFields=*&outSR=4326&f=json";
                break;
            case "cityblock":
                $idName = "STV";
                $url = "https://secure.konstanz.de/geoportal/rest/services/Fachdaten/KLRG_Stadtviertelflaeche_WGS84/MapServer/0/query?where=1%3D1&outFields=*&outSR=4326&f=json";
                break;
            case "buildingblock":
                $idName = "BB_NR";
                $url = "https://secure.konstanz.de/geoportal/rest/services/Fachdaten/KLRG_Baublockflaeche_WGS84/MapServer/0/query?where=1%3D1&outFields=*&outSR=4326&f=json";
                break;
            default:
                return null;
        }

        $data = $this->curl($url);

        $data = json_decode($data);

        $resp = array();

        foreach ($data->features as $d) {

            if ($type == "city") {
                $id = 0;
            } else {
                $id = $d->attributes->$idName;
            }
            $dist = $this->_ringsToDistricts($d->geometry->rings, $id);

            foreach ($dist as $dis) {
                array_push($resp, $dis);
            }

        }

        return array("sortableBy" => $this->getSortables($type), "data" => $resp);


    }

    public function getRanking($type, $crit)
    {
        $ids = $this->getAllIds($type);
        $vals = array();
        switch (strtolower($type)) {
            case "district":
                foreach ($ids as $id) {
                    $obj = new District($id);
                    $obj->completeData();
                    $vals[$id] = $obj->$crit;
                }
                break;
            case "cityblock":
                foreach ($ids as $id) {
                    $obj = new CityBlock($id);
                    $obj->completeData();
                    $vals[$id] = $obj->$crit;
                }
                break;

            case "buildingblock":
                foreach ($ids as $id) {
                    $obj = new BuildingBlock($id);
                    $obj->completeData();
                    $vals[$id] = $obj->$crit;
                }
                break;
            case "votedistrict":
                foreach ($ids as $id) {
                    $obj = new VoteDistrict($id);
                    $obj->completeData();
                    $vals[$id] = $obj->$crit;
                }
                break;
            default:
                return null;
                break;
        }
        return $this->_calcColors($vals);
    }

    public function averageAge($inhabitant, $year = null)
    {
        if($year == null)
        {
            foreach ($inhabitant as $y=>$v)
            {
                $year = $y;
                break;
            }
}
        $data = $inhabitant[$year];

        $under18 = $data['inhabitantsUnder18'];
        $d18to30 = $data['inhabitants18to30'];
        $d30to40 = $data['inhabitants30to40'];
        $d40to50 = $data['inhabitants40to50'];
        $d50to60 = $data['inhabitants50to60'];
        $over60 = $data['inhabitantsOver60'];

        $age = $under18 * 9 + $d18to30 * 24 + $d30to40 * 35 + $d40to50 * 45 + $d50to60 * 55 + $over60 * 75;

        $age /= $data['inhabitantsTotal'] == 0 ? 1 : $data['inhabitantsTotal'];

        if($age == 0)$age = 35;

        return $age;


    }

    private function _calcColors($values)
    {
        $ratio = array();
        $min = min($values);
        $max = max($values);
        $diff = $max - $min;
        foreach ($values as $key => $val) {
            $ratio[$key] = ($val - $min) / $diff;
        }

        return array('range' => array('min' => $min, 'max' => $max), 'ranking' => $ratio);
    }


}