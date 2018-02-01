<?php namespace knhackt;

use base\SuperController;

class Controller extends SuperController
{

    protected $model;

    public function __construct(array $data)
    {
        ini_set("display_errors", true);
        $this->model = new Model("cfg.php");
        parent::__construct($data, $this->model);
    }


    protected function getVisualization()
    {
        $param = $this->handleParameters("type");
        return $this->model->fetchVisualizeData($param['type']);
    }


    protected function getVoteDistrictInfo()
    {
        $param = $this->handleParameters("id");
        return $this->model->getVoteDistrictInfo($param['id']);
    }

    protected function getCityBlockInfo()
    {
        $param = $this->handleParameters("id");
        return $this->model->getCityBlockInfo($param['id']);
    }

    protected function getDistrictInfo()
    {
        $param = $this->handleParameters("id");
        return $this->model->getDistrictInfo($param['id']);
    }

    protected function getBuildingBlockInfo()
    {
        $param = $this->handleParameters("id");
        return $this->model->getBuildingBlockInfo($param['id']);
    }

    protected function getBuildingInfo()
    {
        $param = $this->handleParameters("id");
        return $this->model->getBuildingInfo($param['id']);
    }

    protected function getData()
    {
        $param = $this->handleParameters("id", "type");
        return $this->model->getData($param['type'], $param['id']);
    }

    protected function getAverageInfo()
    {
        $param = $this->handleParameters("id", "type");
        return $this->model->getAverageInfo($param['type'], $param['id']);
    }

    protected function getRanking()
    {
        $param = $this->handleParameters("type", "criteria");
        return $this->model->getRanking($param['type'], $param['criteria']);
    }



}