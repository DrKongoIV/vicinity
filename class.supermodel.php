<?php

namespace base;

class SuperModel
{

    /** @var $instance SuperModel */
    private static $instance;

    /** Returns instance of model
     *
     * @return SuperModel
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /** @var  \Connection */
    protected $connection;

    /**
     * @return \Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * SuperModel constructor.
     *
     * @param $configPath string path to config file
     */
    public function __construct($configPath)
    {
        $this->connection = new \Connection($configPath);
        self::$instance = $this;
    }


    function curl($url, $method = "GET", $data = [], $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($method !== "GET" && $method !== "POST") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        } else if ($method === "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);

        curl_close($ch);
        return $response;

    }
}