<?php
/**
 * Created by PhpStorm.
 * User: Isaque
 * Date: 10/12/2017
 * Time: 17:24
 */

namespace App\Util;

use Illuminate\Database\Capsule\Manager as Capsule;
use \ReflectionClass;
use \ReflectionProperty;
use PDO;

class DBLayer extends Capsule
{
    private static $SELECTED_CONNECTION_SETS = null;
    private static $connection = null;
    const JSON_FORMAT = 'json';
    const ARRAY_FORMAT = 'array';
    const OPERATOR_ILIKE = 'ilike';//operator ilike
    const OPERATOR_EQUAL = '=';//operator =
    const ORDER_DIRE_ASC = 'asc';//order by Direction ASCENDENT
    const ORDER_DIRE_DESC = 'desc';

    public static function Connect($connectionName = DBConfig::DEFAULT_CONNECTION)
    {
        $CONNECTIONS = DBConfig::getConnections()['connections'];
        self::$SELECTED_CONNECTION_SETS = $CONNECTIONS[$connectionName];

        $capsule = new Capsule;

        // application/config/database.php
        //'fetch' => PDO::FETCH_CLASS,
        // to
        //'fetch' => PDO::FETCH_ASSOC,

        //DB::connection()->setFetchMode(PDO::FETCH_ASSOC);
        $capsule->setFetchMode(PDO::FETCH_ASSOC);

        //isaque alterou a linha 104 do arquivo Connection.php do diretorio
        //illuminate/database/Connection.php para abilitar um retorno de array do query builder
        //de isso: protected $fetchMode = PDO::FETCH_OBJ;
        //para isso:  protected $fetchMode = PDO::FETCH_ASSOC;

        $capsule->addConnection(self::$SELECTED_CONNECTION_SETS);

        // Make this Capsule instance available globally via static methods
        $capsule->setAsGlobal();

        // Setup the Eloquent ORM
        $capsule->bootEloquent();


        //converte os dados para array
        //collect($data)->map(function($x){ return (array) $x; })->toArray()

        //Se você quer uma matriz, você pode simplesmente adicionar
        // ->all()logo depois ->get()retirar os itens da coleção.
        return $capsule;

    }

    /**
     * @param $data
     * @param $tableName
     * @param $localKey
     * @param $foreignKey
     * @param null $relationName
     * @param array $defaultNull
     *
     * @param null $callback_fields
     * Este parametro deve ser uma função com um parametro. Utilizada para alterar as informações de um determinado campo vindo do banco
     * Exemplo:
     * function ($field) {
     *  $field['description'] = strip_tags($field['description']);
     * }
     *
     * @param null $callback_query
     * Este parametro deve ser uma função com um parametro. Neste parametro você receberá a query utilizada na consulta, possibilitando
     * realizar operações de querys extras para esta ação.
     *
     * Exemplo:
     * function ($query) {
     *  $query -> orderBy('field_name', 'asc');
     * }
     *
     * @param bool $isSingle
     *
     **/

    private static function _getRelation (&$data, $tableName, $localKey, $foreignKey, $relationName = null, $defaultNull=[], $callback_fields = null, $callback_query = null, $isSingle=false) {
        $db = self::Connect();

        //1º obtem os ids
        $itens_id = [];
        foreach ($data as $item2) {
            $itemId = isset($item2[$foreignKey]) ?$item2[$foreignKey]:null;
            //não adiciona se for nulo ou vazio ou diferente de int
            if(is_int($itemId))
            {
                array_push($itens_id, $itemId);
            }
        }

        //instancia o objeto query builder
        $query = $db->table(DBLayer::raw('"' . $tableName . '"'));

        //checa se foi passado callback
        if ($callback_query) {
            call_user_func($callback_query, $query);
        }

        //se não ouver itens a serem pegos no banco
        if(is_array($itens_id) && count($itens_id) > 0) {
            //prepara a query where e executa
            $query->whereRaw('"' . $tableName . '"."' . $localKey . '" in (' . implode(',', $itens_id) . ")");
            $result = $query->get();
        }else{
            $result = null;
        }

        //verifica se foi passado um nome para o node de resultados
        if ($relationName) {
            $relationName = $relationName.'';
        } else {

            $relationName = $tableName;
        }

        //intera sobre o array de dados passados
        foreach ($data as &$item) {
            $conjunto = [];
            $item[$relationName] = $defaultNull;
            //intera sobre os resultados da query
            if($result != null) {
                foreach ($result as $value) {
                    //verifica se o corrente item tem relação com algum filho trazido pela query
                    if ($item[$foreignKey] == $value[$localKey]) {

                        //checa se foi passado callback
                        if ($callback_fields) {
                            $value = call_user_func($callback_fields, $value);
                        }

                        //verifica se é para trazer um filho ou varios
                        if ($isSingle) {
                            $item[$relationName] = $value ? $value : $defaultNull;
                            break;
                        } else {
                            array_push($conjunto, $value ? $value : $defaultNull);
                        }

                        $item[$relationName] = $conjunto;
                    }
                }
            }
        }

        /* foreach ($data as &$item)
         {
             $query = $db->table($tableName)
                 ->where($localKey, '=', $item[$foreignKey]);

             if(method_exists ( $query , $returnType)) {
                 $extraData = $query -> $returnType();
             }

             if ($callback) {
                 $extraData = $callback($extraData);
             }

             if ($relationName)
             {
                 $item[$relationName] = $extraData ? $extraData : $defaultNull;
             }
             else
             {
                 $item[$tableName] = $extraData ? $extraData : $defaultNull;
             }
         }*/
    }

    /**
     * @param $data
     * @param $tableName
     * @param $localKey
     * @param $foreignKey
     * @param null $relationName
     * @param array $defaultNull
     * @param null $callback_fields
     * Este parametro deve ser uma função com um parametro. Utilizada para alterar as informações de um determinado campo vindo do banco
     * Exemplo:
     * function ($field) {
     *  $field['description'] = strip_tags($field['description']);
     * }
     *
     * @param null $callback_query
     * Este parametro deve ser uma função com um parametro. Neste parametro você receberá a query utilizada na consulta, possibilitando
     * realizar operações de querys extras para esta ação.
     *
     * Exemplo:
     * function ($query) {
     *  $query -> orderBy('field_name', 'asc');
     * }
     */

    public static function getRelation(&$data, $tableName, $localKey, $foreignKey, $relationName = null, $defaultNull=[], $callback_fields = null, $callback_query = null)
    {
        self::_getRelation($data, $tableName, $localKey, $foreignKey, $relationName, $defaultNull, $callback_fields, $callback_query, true);
    }

    /**
     * @param $data
     * @param $tableName
     * @param $localKey
     * @param $foreignKey
     * @param null $relationName
     * @param array $defaultNull
     * @param null $callback_fields
     * Este parametro deve ser uma função com um parametro. Utilizada para alterar as informações de um determinado campo vindo do banco
     * Exemplo:
     * function ($field) {
     *  $field['description'] = strip_tags($field['description']);
     * }
     *
     * @param null $callback_query
     * Este parametro deve ser uma função com um parametro. Neste parametro você receberá a query utilizada na consulta, possibilitando
     * realizar operações de querys extras para esta ação.
     *
     * Exemplo:
     * function ($query) {
     *  $query -> orderBy('field_name', 'asc');
     * }
     */

    public static function getRelationAll(&$data, $tableName, $localKey, $foreignKey, $relationName = null, $defaultNull=[], $callback_fields = null, $callback_query = null)
    {
        self::_getRelation($data, $tableName, $localKey, $foreignKey, $relationName, $defaultNull, $callback_fields, $callback_query);
    }

    /*
    public static function show()
    {

    }

    public static function storeObj($object)
    {
        $className = get_class($object);
        $reflect = new ReflectionClass($className);

        $props   = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);

        foreach ($props as $prop) {
            print $prop->getName() . "\n";
        }

        $columns = Capsule::connection()->getSchemaBuilder()->getColumnListing($className.'s');

        $id = Capsule::table(User::TABLE_NAME)->insertGetId(['email' => 'john@example.com', 'votes' => 0]);

    }

    public static function update()
    {

    }

    public static function delete()
    {

    }
*/
}


