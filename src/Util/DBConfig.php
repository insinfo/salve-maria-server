<?php
/**
 * Created by PhpStorm.
 * User: Isaque
 * Date: 14/07/2017
 * Time: 18:09
 */

namespace App\Util;
class DBConfig 
{
    const DEFAULT_CONNECTION = 'default';    
    const DEFAULT_DATABASE_NAME = 'd6rqd5tupb4km1';
    const DEFAULT_SCHEMA_NAME = 'public';

    public static function getConnections()
    {

        extract(parse_url(getenv('DATABASE_URL')));
        $dbname=substr($path, 1);
        
        return $connections = [

            'connections' => [
                
                'default' => [
                    'driver' => 'pgsql',
                    'host' => $host,
                    'port' => '5432',
                    'database' => $dbname,
                    'username' => $user,
                    'password' => $pass,
                    'charset' => 'utf8',
                    'prefix' => '',
                    'schema' => ['public'],
                    'sslmode' => 'prefer',
                ],
            ],
        ];
    }
}