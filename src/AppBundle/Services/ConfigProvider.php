<?php
/**
 * Created by PhpStorm.
 * User: danielsolis
 * Date: 23/06/17
 * Time: 16:24
 */

namespace AppBundle\Services;
use Symfony\Component\Yaml\Yaml;

class ConfigProvider
{
    protected $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function getConfiguration()
    {
        $file = sprintf(
            "%s/config/security.yml",
            $this->rootDir
        );
        $parsed = Yaml::parse(file_get_contents($file));

        return $parsed['security']['access_control'];
    }

    public function getRoleHierarchy()
    {
        $file = sprintf(
            "%s/config/security.yml",
            $this->rootDir
        );
        $parsed = Yaml::parse( file_get_contents( $file ) );

        return $parsed['security']['role_hierarchy'];
    }
}
