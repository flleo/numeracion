<?php

include 'connect.php';

$conn = connect();

function carga($tabla, $condicion, $order)
{
    global $conn;
    $select  = bindeaFecha($tabla);  
    if($condicion != '') 
    $stmt = $conn->prepare("$select FROM $tabla WHERE $condicion $order");
    else  $stmt = $conn->prepare("$select FROM $tabla  $order");
    $stmt->execute();
    $results= $stmt->fetchAll();
    return $results;
}
function cargaBindeo($tabla,$condiciones)
{
    global $conn;      
    $stmt = $conn->prepare("SELECT * FROM $tabla WHERE $condiciones" );
    $stmt->execute();
    $results = $stmt->fetchAll();
    return $results;
}

function insert($query, $values)
{
    global $conn;
    $stmt = $conn->prepare($query);
    try {
        if($values != '')
        $stmt->execute($values);
        else  $stmt->execute();    
    } catch (PDOException $e) {
        $conn->rollback();
    }
    $r = $conn->lastInsertId(); 
    return $r ; 
}

function update($query, $values)
{
    global $conn;
    $res = false;
    $stmt = $conn->prepare($query);
    try {
        if($values != '')
        $res = $stmt->execute($values);
        else  $res = $stmt->execute();
    } catch (PDOException $e) {
        $conn->rollback();
    }
    return $res;
}

function baja($tabla,$id)
{
    global $conn;
    $res = false;
    try {
        $res = $conn->exec("DELETE FROM $tabla WHERE id in ($id)");
    } catch (PDO $e) {   
        $conn->rollback();
    }
    return $res;
}

function bindeaFecha($tabla) {
    if($tabla == 'numeracion_historial') 
    $select =  "SELECT id, numero, id_tipo, id_operador, id_estado, id_tipo_numero, id_servidor, id_entrega, DATE_FORMAT(fecha_alta, '%d/%m/%Y %H:%i:%s') as fecha_alta, DATE_FORMAT(fecha_ultimo_cambio, '%d/%m/%Y %H:%i:%s') as fecha_ultimo_cambio, cliente_actual, numeros_desvios, observaciones, id_motivo_baja, reactivar";       
    elseif($tabla == 'numeracion') 
    $select = "SELECT id, numero, id_tipo, id_operador, id_estado, id_tipo_numero, id_servidor, id_entrega, DATE_FORMAT(fecha_alta, '%d/%m/%Y %H:%i:%s') as fecha_alta, DATE_FORMAT(fecha_ultimo_cambio, '%d/%m/%Y %H:%i:%s') as fecha_ultimo_cambio, cliente_actual, numeros_desvios, observaciones";
    else $select = 'SELECT *';
    return $select;
}