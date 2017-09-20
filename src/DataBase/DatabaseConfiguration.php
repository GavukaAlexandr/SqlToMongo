<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 03.08.17
 * Time: 14:10
 */

namespace DataBase;

class DatabaseConfiguration implements DataBaseConfigurationInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    public function __construct(
        string $host = null,
        string $port = null,
        string $username = null,
        string $password = null)
    {
        if (
            $host !== null &&
            $port !== null &&
            $host !== null) {

            $this->host = $host;
            $this->port = $port;
            $this->username = $username;
            $this->password = $password;
        } else {
            $this->loadConfig();
        }

    }

    public function loadConfig()
    {
        $path = __DIR__ . '/../../Config/config.yml';
        $config = yaml_parse_file($path);

        $this->host = $config['config']['MongoDB']['uri'] . $config['config']['MongoDB']['host'];
        $this->port = ':' . $config['config']['MongoDB']['port'];
        $this->username = $config['config']['MongoDB']['user'];
        $this->password = $config['config']['MongoDB']['password'];
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
