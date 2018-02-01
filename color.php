<?php

function getRanking($type, $crit)
{
    $ids = $this->getAllIds($type);
    $vals = array();
    switch (strtolower($type)) {
        case "district":
            foreach ($ids as $id) {
                $obj = new District($id);
                $obj->completeData();
                array_push($vals, $obj->$crit);
            }
            break;
        case "cityblock":
            foreach ($ids as $id) {
                $obj = new CityBlock($id);
                $obj->completeData();
                array_push($vals, $obj->$crit);
            }
            break;

        case "buildingblock":
            foreach ($ids as $id) {
                $obj = new District($id);
                $obj->completeData();
                array_push($vals, $obj->$crit);
            }
            break;
        case "votedistrict":
            foreach ($ids as $id) {
                $obj = new VoteDistrict($id);
                $obj->completeData();
                array_push($vals, $obj->$crit);
            }
            break;
        default:
            return null;
            break;
    }
    return $this->calcColors($vals);
}

function calcColors($values)
{
    $ratio = array();
    $values[] = array();
    $min = min($values);
    $max = max($values);
    $diff = $max - $min;
    foreach ($values as $key => $val) {
        $ratio[$key] = ($val - $min) / $diff;
    }

    return array('range' => array('min' => $min, 'max' => $max), 'ranking' => $ratio);
}

?>
