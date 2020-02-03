<?php 
require '../../vendor/autoload.php';
//require 'funciones.php';

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;
use Google\Cloud\Datastore\EntityIterator;
use Google\Cloud\Datastore\Key;
use Google\Cloud\Datastore\Query\Query;

$datastore = new DatastoreClient([ 'keyFile' => json_decode(file_get_contents('/home/bitnami/ultraprop_credencial/credencial.json'), true)
]);

// base de la api
// recibiendo parametros devuelve json

$data = array();
$attributes = array();
$links = array();
$campos = array();
$relationships = array();

// caso realestate
// api/realestate/{id}
$e = $_REQUEST['entidad'];
$id = $_REQUEST['id'];


/// sinonimos de entidad
switch($e){
    case 'realestate':
    case 're':
    case 'inmobiliarias':
        $e= "RealEstate";
        $campos = ['name', 'email', 'telephone_number', 'fax_number', 'domain_id','address', 'status', 'website'];
        break;
    case 'consultas':
    case 'Consulta':
        $e = 'Consulta';
        $campos = ['sender_email', 'sender_name', 'sender_telephone','sender_comment','is_from_ultraprop', 'realestate_name','realestate_property_link','prop_operation_desc'];
        break;
    case 'propiedades':
    case 'property':
        $e = 'Property';
        $campos = ['rooms', 'street_name', 'city', 'state','prop_operation_id','prop_operation_state_id', 
        'main_description', 'images_count','price_rent','price_rent_computed','price_sell_currency', 'price_sell', 'prop_state_id', 
        'main_image_url'];
        break;
    case 'images':
        $e = 'ImageFile';
        $campos = ['file', 'filename', 'position', 'title'];
        break;
    }
/* no anda pero en consola si
$q = "select * from  $e
where __key__  = KEY($e,$id) ";
echo($q);
$result = $datastore->gqlQuery($q);
*/

// si tiene el id es porque viene de una consulta 
// del tipo /propiedad/99
if(isset($_REQUEST['id']) && $_REQUEST['id']!=''){
$key = $datastore->key($e,$id);
$result = $datastore->lookup($key);

$att = atributos($result, $campos);

    $data = [
        'type' => $e,
        'id' => $id,
        'attributes' => $att
    ];


$response = [$data];

}else{
        $q = "SELECT * FROM $e ORDER BY created_at desc LIMIT @li ";
        $query = $datastore->gqlQuery($q, [
            'bindings' => [
                'li' => 5
            ]
        ]);

        $result = $datastore->runQuery($query);
        foreach ($result as $index => $task) {
            $key = $task->key();
            $elid = $key->pathEndIdentifier();
            $key = $task->key();
            $elid = $key->pathEndIdentifier();
            //print($elid);

            $att = atributos($task, $campos);
            $data = [
                'type' => $e,
                'id' => $elid,
                'attributes' => $att
            ];

            $response [] = $data;
        }
           
}

//$result = (array) $query;
//$res = json_decode(json_encode($res, true));
//$res = json_decode(json_encode($result, true));

//$prop = (array)$result['property'];
//foreach($results as $result=>$value){

function atributos($arr_result, $campos){

    $BASE = "https://redengo.com/googledatastore/api/";

    if(isset($arr_result['created_at'])){
        $fecha = json_decode(json_encode($arr_result['created_at'], true));
        $arr_result["createdAt"] = $fecha->date;
    }
    if(isset($arr_result['updated_at'])){
        $fecha = json_decode(json_encode($arr_result['updated_at'], true));
        $attributes["updatedAt"] = $fecha->date;
    }

    if(isset($arr_result['property'])){
        $prop = json_decode(json_encode($arr_result['property'], true));
        if(isset($prop->path[0]->id)){
        $attributes["propertyId"] = $prop->path[0]->id;
        }else{
            $attributes["propertyId"] = $prop->path[0]->name;
        }
        $links['propiedad'] = $BASE."propiedades/".$attributes['propertyId'];
    }
    if(isset($arr_result['realestate'])){
        $realestate = json_decode(json_encode($arr_result['realestate'], true));
        if(isset($realestate->path[0]->id)){
            $attributes["realestateId"]  = strval($realestate->path[0]->id);
            }else{
                $attributes["realestateId"]  = strval($realestate->path[0]->name);
            }
        $links['realestate'] = $BASE."realestate/".$attributes['realestateId'];
    }
    if(isset($arr_result['plan'])){
        $plan = json_decode(json_encode($arr_result['plan'], true));
        $attributes["planId"]  = $plan->path[0]->id;
    }

    if(isset($arr_result['location'])){
        $loc = $arr_result['location'];
        
        $attributes["lat"]  = $loc->latitude();
        $attributes["lon"]  = $loc->longitude();
    }

    foreach($campos as $campo){
        $attributes[$campo] = $arr_result[$campo];
    }
    return ($attributes);
}//fin funcion atributos



$j = json_encode($response, true);

 //  header('Content-Type: application/vnd.api+json');
   echo $j;
 
echo("<pre>");
print("\n___________________\n\n");
print_r($att);
print("\n___________________\n");
var_dump($result);

echo("</pre>");





























/*
foreach ($result as $index => $value) {
    echo($index);
    echo($value);
}
*/

/*
$key = $datastore->key('Property', $id);
$task = $datastore->lookup($key);

$re = $datastore->lookup($task['realestate']);


    switch($task['prop_operation_id']){
        case 1:
            $tipo = 'Venta';
            $precio = $task['price_sell_computed'];
            break;
        case 2:
            $tipo = 'Alquiler';
            $precio = $task['price_rent_computed'];
    }
  
*/

//////////////////////////////////////////////
//// busqueda de imagenes
 /*
 Esto anda:
 	select * from  ImageFile
    where property  = Key(Property, 5703042573795328)
    */
    
/*
$query = $datastore->query()
->kind('ImageFile')
->filter('property', "=", $key);

$result = $datastore->runQuery($query);
foreach ($result as $index => $imagen) {
	//var_dump( $index );
	$imagenes .= " <img class='card-img-top' src='".$imagen['title']."' alt=''>";
	
}
*/

?>