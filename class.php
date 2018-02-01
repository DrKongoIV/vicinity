<?php

namespace knhackt;

use base\Printable;

class DrawableDistrict extends Printable
{
    private $id = null;
    private $polygon = array();

    public function __construct($id, $polygon)
    {
        $this->id = $id;
        $this->polygon = $polygon;
    }

    public function getClassType()
    {
        return "DrawableDistrict";
    }

    public function getData()
    {
        return ['id' => $this->id, 'polygon' => $this->polygon];
    }
}

class City extends Printable
{
    private $districts = array();
    public $area, $inhabitantsPerSqKm, $inhabitantsTotal, $orientationEconomical,
        $orientationPolitical, $participation, $inhabitantData, $averageAge;

    public function completeData()
    {
        return $this->fetchDistricts()->fetchInhabitantData();
    }

    public function fetchInhabitantData()
    {

        if ($this->area != 0) {
            $sqKm = $this->area / 1000000;
            $this->inhabitantsPerSqKm = $this->inhabitantsTotal / $sqKm;
        }
        return $this;
    }


    public function fetchDistricts()
    {
        $districts = Model::getInstance()->getAllIds("district");

        $pol = 0;
        $eco = 0;
        $par = 0;
        $inh = 0;

        $iData = null;
        foreach ($districts as $districtId) {
            $district = ((new District($districtId))->completeData());
            array_push($this->districts, $district);

            $this->area += intval($district->area);
            $this->inhabitantsTotal += intval($district->inhabitantsTotal);

            $pol += $district->orientationPolitical * $district->inhabitantsTotal;
            $eco += $district->orientationEconomical * $district->inhabitantsTotal;
            $par += $district->participation * $district->inhabitantsTotal;
            $inh += $district->inhabitantsTotal;

            if ($iData == null) {
                $iData = $district->inhabitantData;
            } else {
                foreach ($district->inhabitantData as $y => $dat) {

                    foreach ($dat as $k => $v) {
                        if ($k == "state") {
                            continue;
                        }
                        $iData[$y][$k] += $v;
                    }
                }

            }
        }

        $this->inhabitantData = $iData;
        $this->averageAge = Model::getInstance()->averageAge($iData);


        if ($inh != 0) {
            $this->orientationEconomical = $eco / $inh;
            $this->orientationPolitical = $pol / $inh;
            $this->participation = $par / $inh;
        }


        return $this;

    }

    public function getClassType()
    {
        return "City";
    }

    public function getData()
    {
        return array("area" => $this->area, "inhabitantsTotal" => $this->inhabitantsTotal,
            "orientationEconomical" => $this->orientationEconomical,
            "orientationPolitical" => $this->orientationPolitical,
            "participation" => $this->participation,
            "inhabitantsPerSqKm" => $this->inhabitantsPerSqKm, "inhabitants" => $this->inhabitantData, "districts" => $this->districts, "averageAge"=>$this->averageAge);
    }
}

class District extends Printable
{
    private $cityBlocks = array();
    public $id, $name, $area, $perimeter, $inhabitantsPerSqKm, $inhabitantsTotal, $orientationEconomical,
        $orientationPolitical, $participation, $inhabitantData, $averageAge;


    public function __construct($id)
    {
        $this->id = $id;

    }

    public function evaluate()
    {
        return $this;

    }

    public function completeData()
    {
        return $this->fetchData()->fetchCityBlocks()->fetchInhabitantData();
    }

    public function fetchInhabitantData()
    {
        $this->inhabitantsTotal = 0;
        foreach ($this->cityBlocks as $cityBlock) {
            $this->inhabitantsTotal += intval($cityBlock->inhabitantsTotal);
        }


        $sqKm = $this->area / 1000000;
        $this->inhabitantsPerSqKm = $this->inhabitantsTotal / $sqKm;

        return $this;
    }

    public function fetchData()
    {
        if($this->id == null)
        {
            return $this;
        }
        $data = Model::getInstance()->getDistrictInfo($this->id);
        $this->name = $data['name'];
        $this->area = $data['area'];
        $this->perimeter = $data['perimeter'];

        return $this;
    }

    public function fetchCityBlocks()
    {
        $data = Model::getInstance()->getCityBlockIdByDistrict($this->id);

        $pol = 0;
        $eco = 0;
        $par = 0;
        $inh = 0;

        $iData = null;

        foreach ($data as $cityBlock) {
            $block = (new CityBlock($cityBlock['cityBlockId']))->completeData();
            array_push($this->cityBlocks, $block);

            $pol += $block->orientationPolitical * $block->inhabitantsTotal;
            $eco += $block->orientationEconomical * $block->inhabitantsTotal;
            $par += $block->participation * $block->inhabitantsTotal;
            $inh += $block->inhabitantsTotal;

            if ($iData == null) {
                $iData = $block->inhabitantData;
            } else {
                foreach ($block->inhabitantData as $y => $dat) {

                    foreach ($dat as $k => $v) {
                        if ($k == "state") {
                            continue;
                        }
                        $iData[$y][$k] += $v;
                    }
                }

            }

        }

        $this->inhabitantData = $iData;

        $this->averageAge = Model::getInstance()->averageAge($iData);

        if ($inh != 0) {
            $this->orientationEconomical = $eco / $inh;
            $this->orientationPolitical = $pol / $inh;
            $this->participation = $par / $inh;
        }
        return $this;
    }

    public function getClassType()
    {
        return "District";
    }

    public function getData()
    {
        return array("id" => $this->id, "name" => $this->name, "area" => $this->area,
            "perimeter" => $this->perimeter, "inhabitantsTotal" => $this->inhabitantsTotal,
            "orientationEconomical" => $this->orientationEconomical,
            "orientationPolitical" => $this->orientationPolitical,
            "participation" => $this->participation,
            "inhabitantsPerSqKm" => $this->inhabitantsPerSqKm, "inhabitants" => $this->inhabitantData, "cityBlocks" => $this->cityBlocks, "averageAge" => $this->averageAge);
    }
}


class CityBlock extends Printable
{
    private $buildingBlocks = array();
    public $id, $districtId, $area, $perimeter, $inhabitantsPerSqKm, $inhabitantsTotal, $districtName, $orientationEconomical, $averageAge,
        $orientationPolitical, $participation;
    public $inhabitantData = array();

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function evaluate()
    {

        return $this;
    }

    public function completeData()
    {
        return $this->fetchData()->fetchBuildingBlocks()->fetchInhabitantData();
    }

    public function fetchInhabitantData()
    {

        $data = Model::getInstance()->getCityBlockInhabitantInfo($this->id);

        foreach ($data as $year) {
            $this->inhabitantData[$year['state']] = $year;
        }

        $this->averageAge = Model::getInstance()->averageAge($this->inhabitantData);
        $last = $data[0];
        $this->inhabitantsTotal = $last['inhabitantsTotal'];

        if($this->inhabitantsTotal == 0)
        {
            foreach ($this->buildingBlocks as $block)
            {
                $this->inhabitantsTotal += $block->inhabitantsTotal;
            }
        }

        $sqKm = $this->area / 1000000;
        $this->inhabitantsPerSqKm = $this->inhabitantsTotal / $sqKm;


        return $this;
    }

    public function fetchData()
    {
        $data = Model::getInstance()->getCityBlockInfo($this->id);

        $this->districtId = $data['districtId'];
        $this->area = $data['area'];
        $this->perimeter = $data['perimeter'];

        $this->districtName = ((new District($this->districtId))->fetchData()->name);


        return $this;
    }

    public function fetchBuildingBlocks()
    {
        $data = Model::getInstance()->getBuildingBlockIdByCityBlock($this->id);

        $pol = 0;
        $eco = 0;
        $par = 0;
        $inh = 0;

        foreach ($data as $cityBlock) {
            $block = (new BuildingBlock($cityBlock['buildingBlockId']))->completeData();
            array_push($this->buildingBlocks, $block);

            $pol += $block->orientationPolitical * $block->inhabitantsTotal;
            $eco += $block->orientationEconomical * $block->inhabitantsTotal;
            $par += $block->participation * $block->inhabitantsTotal;
            $inh += $block->inhabitantsTotal;

        }

        if ($inh != 0) {

            $this->orientationEconomical = $eco / $inh;
            $this->orientationPolitical = $pol / $inh;
            $this->participation = $par / $inh;
        }

        return $this;
    }

    public function getClassType()
    {
        return "CityBlock";
    }

    public function getData()
    {
        return array("id" => $this->id, "districtId" => $this->districtId, "area" => $this->area,
            "perimeter" => $this->perimeter, "inhabitantsTotal" => $this->inhabitantsTotal,
            "orientationEconomical" => $this->orientationEconomical,
            "orientationPolitical" => $this->orientationPolitical,
            "participation" => $this->participation,
            "inhabitantsPerSqKm" => $this->inhabitantsPerSqKm, "inhabitants" => $this->inhabitantData,
            "buildingBlocks" => $this->buildingBlocks, "districtName" => $this->districtName, "averageAge" => $this->averageAge);
    }
}

class VoteDistrict extends Printable
{
    private $buildingBlocks = array();
    public $id, $inhabitantsTotal, $inhabitantsPerSqKm, $area, $perimeter, $orientationEconomical,
        $orientationPolitical, $participation, $inhabitantData, $districtName, $averageAge;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function fetchInhabitantData()
    {
        $this->inhabitantsTotal = 0;

        foreach ($this->buildingBlocks as $buildingBlock) {
            $this->inhabitantsTotal += intval($buildingBlock->inhabitantsTotal);
        }

        $sqKm = $this->area / 1000000;
        $this->inhabitantsPerSqKm = $this->inhabitantsTotal / $sqKm;
        return $this;
    }

    public function evaluate()
    {

        return $this;
    }

    public function completeData()
    {
        return $this->fetchData()->fetchBuildingBlocks()->fetchInhabitantData();
    }

    public function fetchBuildingBlocks()
    {
        $data = Model::getInstance()->getBuildingBlockIdByVoteDistrict($this->id);

        $iData = null;
        foreach ($data as $cityBlock) {
            $city = (new BuildingBlock($cityBlock['buildingBlockId']))->completeData();
            $this->districtName = $city->districtName;
            array_push($this->buildingBlocks, $city);
            if ($iData == null) {
                $iData = $city->inhabitantData;
            } else {
                foreach ($city->inhabitantData as $y => $dat) {

                    foreach ($dat as $k => $v) {
                        if ($k == "state") {
                            continue;
                        }
                        $iData[$y][$k] += $v;
                    }
                }

            }
        }
        $this->averageAge = Model::getInstance()->averageAge($iData);

        $this->inhabitantData = $iData;

        return $this;
    }

    public function fetchData()
    {
        $data = Model::getInstance()->getVoteDistrictInfo($this->id);
        $this->area = $data['area'];
        $this->perimeter = $data['perimeter'];

        $orientation = Model::getInstance()->getVoteDistrictOrientation($this->id);

        $this->orientationEconomical = $orientation['eco'];
        $this->orientationPolitical = $orientation['pol'];

        $this->participation = Model::getInstance()->getVoteDistrictParticipation($this->id);

        return $this;
    }

    public function getClassType()
    {
        return "VoteDistrict";
    }

    public function getData()
    {
        return array("id" => $this->id, "area" => $this->area,
            "districtName" => $this->districtName,
            "perimeter" => $this->perimeter, "totalInhabitants" => $this->inhabitantsTotal,
            "inhabitantsPerSqKm" => $this->inhabitantsPerSqKm,
            "orientationEconomical" => $this->orientationEconomical,
            "orientationPolitical" => $this->orientationPolitical,
            "participation" => $this->participation,
            "averageAge" => $this->averageAge,
            "inhabitants"=> $this->inhabitantData,
            "buildingBlocks" => $this->buildingBlocks);
    }
}

class BuildingBlock extends Printable
{
    private $buildings = array();
    public $id, $strId, $plz, $cityBlockId, $electionDistrictId, $area, $perimeter, $inhabitantsTotal, $inhabitantsPerSqKm, $orientationEconomical,
        $orientationPolitical, $participation, $voteDistrict, $districtName, $inhabitantData, $averageAge;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function evaluate()
    {
        return $this;

    }

    public function completeData()
    {
        return $this->fetchData()->fetchBuildings()->fetchInhabitantData();
    }

    public function fetchInhabitantData()
    {

        $data = Model::getInstance()->getBuildingBlockInhabitantInfo($this->id);

        $this->inhabitantsTotal = $data['total'];

        if($this->inhabitantsTotal == null)
        {
            $this->inhabitantsTotal = 1;
        }

        if ($this->area != null) {
            $sqKm = $this->area / 1000000;
            $this->inhabitantsPerSqKm = $this->inhabitantsTotal / $sqKm;
        }


        $city = (new CityBlock($this->cityBlockId))->fetchData()->fetchInhabitantData();

        $this->averageAge = Model::getInstance()->averageAge($city->inhabitantData);
        $this->inhabitantData = $city->inhabitantData;

        foreach ($this->inhabitantData as $y => $dat) {
            foreach ($dat as $k => $val) {
                if ($k == "state")
                    continue;

                if($city->inhabitantsTotal == 0)
                {
                    continue;
                }

                $this->inhabitantData[$y][$k] = round($val / $city->inhabitantsTotal * $this->inhabitantsTotal);
            }

        }

        return $this;
    }

    public function fetchData()
    {


        $data = Model::getInstance()->getBuildingBlockInfo($this->id);

        $this->strId = $data['streetId'];
        $this->plz = $data['plz'];
        $this->cityBlockId = $data['cityBlockId'];
        $this->area = $data['area'];
        $this->perimeter = $data['perimeter'];
        $this->electionDistrictId = $data['electionDistrictId'];

        $city = (new CityBlock($this->cityBlockId))->fetchData();
        $this->districtName = $city->districtName;


        $this->voteDistrict = (new VoteDistrict($this->electionDistrictId))->fetchData();

        $this->orientationPolitical = $this->voteDistrict->orientationPolitical;
        $this->orientationEconomical = $this->voteDistrict->orientationEconomical;
        $this->participation = $this->voteDistrict->participation;

        return $this;
    }

    public function fetchBuildings()
    {
        $data = Model::getInstance()->getBuildingsByBuildingBlock($this->id);

        if ($data != null) {
            foreach ($data as $building) {
                array_push($this->buildings, (new Building($building['buildingId']))->completeData());
            }

        }


        return $this;
    }

    public function getClassType()
    {
        return "BuildingBlock";
    }

    public function getData()
    {
        return array("id" => $this->id, "strId" => $this->strId, "districtName" => $this->districtName,
            "plz" => $this->plz, "cityBlockId" => $this->cityBlockId, "electionDistrictId" => $this->electionDistrictId,
            "area" => $this->area, "perimeter" => $this->perimeter,
            "inhabitantsPerSqKm" => $this->inhabitantsPerSqKm, "inhabitantTotal" => $this->inhabitantsTotal,
            "orientationEconomical" => $this->orientationEconomical,
            "orientationPolitical" => $this->orientationPolitical,
            "participation" => $this->participation,
            "inhabitants" => $this->inhabitantData,
            "averageAge" => $this->averageAge,
            "buildings" => $this->buildings);
    }
}

class Building extends Printable
{
    private $constructionDate, $amountApartments, $id, $blockId, $apartmentsStr;


    public function __construct($id)
    {
        $this->id = $id;
    }

    public function evaluate()
    {

    }

    public function completeData()
    {
        return $this->fetchData();
    }

    public function fetchData()
    {
        $data = Model::getInstance()->getBuildingInfo($this->id);

        $this->constructionDate = $data['constructionYearStr'];
        $this->apartmentsStr = $data['constructionYearNum'];
        $this->blockId = $data['buildingBlockId'];
        $this->amountApartments = $data['apartmentsNum'];
        $this->apartmentsStr = $data['apartmentsStr'];


        return $this;
    }

    public function getClassType()
    {
        return "Building";
    }

    public function getData()
    {
        return array("id" => $this->id, "constructionDate" => $this->constructionDate, "amountApartments" => $this->amountApartments,
            "blockId" => $this->blockId, "apartmentStr" => $this->apartmentsStr);
    }

}

