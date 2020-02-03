<?php

function atributos($arr_result, $campos){
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
    if(isset($result['realestate'])){
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


?>