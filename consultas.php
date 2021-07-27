<?php

/**Consultas para ajax y los modales
 * Funciones para exportar los datos por excel
 */



include 'mysql.php';
//Label selects Admin
$campos = array('numero', 'id_tipo', 'id_operador',  'id_estado',  'id_tipo_numero', 'id_servidor', 'id_entrega', 'fecha_alta', 'fecha_ultimo_cambio', 'cliente_actual', 'numeros_desvios', 'observaciones', 'id_motivo_baja');
$camposa = array('numero', 'id_tipo', 'id_operador', 'id_estado', 'id_tipo_numero', 'id_servidor', 'id_entrega', 'fecha_alta', 'cliente_actual', 'numeros_desvios', 'observaciones');
$camposab = array('numero', 'id_tipo', 'id_operador', 'id_estado', 'id_tipo_numero', 'id_servidor', 'id_entrega', 'fecha_alta', 'cliente_actual', 'numeros_desvios', 'observaciones', 'id_motivo_baja');
$camposu = array('numero', 'id_tipo', 'id_operador', 'id_estado', 'id_tipo_numero', 'id_servidor', 'id_entrega', 'fecha_alta', 'fecha_ultimo_cambio', 'cliente_actual', 'numeros_desvios', 'observaciones');
$pcamposSS = array('Tipos', 'Operadores', 'Estados', 'Tipos&nbsp;de&nbsp;Números', 'Entregas', 'Servidores', 'Motivos&nbsp;de&nbsp;Baja');
$tablas = ['tipo', 'operador', 'estado', 'tipo_numero', 'entrega', 'servidor', 'motivo_baja'];
$pcamposS = array('Tipo', 'Operador', 'Estado', 'Tipo&nbsp;de&nbsp;Número', 'Entrega', 'Servidor', 'Motivo de Baja');
$resSer = $mensaje = $tabla = $accion = $ps = '';
$actualiza = false;
$existe = false;
session_start();


//Carga de las tablas en $admins y los selects/////////////////////////////////////////////////////
$admins = [];
foreach ($tablas as $t) {
    $t == 'servidor' ? $c = 'host' : $c = $t;
    $res = carga($t, '', "ORDER by $c");
    $admins[$t] = array($res);
}

$id_estado_activado = $admins['estado'][0][array_search('Activado', array_column($admins['estado'][0], 1))]['id'];
$id_estado_desactivado = $admins['estado'][0][array_search('Desactivado', array_column($admins['estado'][0], 1))]['id'];
$id_estado_baja = $admins['estado'][0][array_search('Baja', array_column($admins['estado'][0], 1))]['id'];


//Recogemos la accion de los ajax
if (isset($_GET['a'])) {
    $accion = $_GET['a'];
    if (isset($_GET['t'])) {
        $tabla = $_GET['t'];
    }
    if (isset($_GET['v'])) {
        $valor = $_GET['v'];
    }
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    }
    if (isset($_GET['vhost'])) {
        $host = $_GET['vhost'];
    }
    if (isset($_GET['vip'])) {
        $ip = $_GET['vip'];
    }
    if (isset($_GET['vdes'])) {
        $des = $_GET['vdes'];
    }

    //Obtenemos el indice para asignar un nombre de cabecera
    while ($tab = current($tablas)) {
        if ($tab == $tabla) {
            $ps = $pcamposSS[key($tablas)];
            break;
        }
        next($tablas);
    }

    if ($accion == 'Añadir') {
        if ($tabla != 'servidor') {
            $query = "INSERT INTO $tabla ($tabla) VALUES (?)";
            $values = [$valor];
        } else {
            $query = "INSERT INTO servidor (host,ip,descripcion) VALUES (?,?,?)";
            $values = [$host, $ip, $des];
        }
        $lastId = insert($query, $values);
        echo "<div hidden id='lastId'>$lastId</div>";
        if ($lastId > 0)  $actualiza = true;
    }

    if ($accion == 'Actualizar') {
        //Condicion
        $condicion = "id_$tabla = $id";
        //Traemos de numeracion los registros con condicion
        $resn = cargaBindeo('numeracion', $condicion);
        //Traemos de numeracion_historial los registros con condicion
        $resh = cargaBindeo('numeracion_historial', $condicion);
        //Si existe en cualquera de las dos tablas crearemos un nuevo registro ,lo necesitamos para guardar el viejo en el historial       
        if (count($resn) > 0 || count($resh) > 0) {
            //Creamos nuevo registro del campo a actualizar
            if ($tabla != 'servidor') {
                $query = "INSERT INTO $tabla ($tabla) VALUES (?)";
                $values = [$valor];
            } else {
                $query = "INSERT INTO $tabla (host,descripcion,ip) VALUES (?,?,?)";
                $values = [$host, $des, $ip];
            }
            $id_ultimo = insert($query, $values);
            if ($id_ultimo > 0) {
                //Para que actualize el array $admins de selects
                $actualiza = true;
                //Grabamos en historial cada uno de los registros de numeracion si existen, ya que van a ser cambiados
                if (count($resn) > 0) {
                    foreach ($resn as $r) {
                        $values = [];
                        for ($i = 0; $i < count($camposa); $i++)  array_push($values, $r[$camposa[$i]]);
                        grabaT($camposa, $values, 'numeracion_historial');
                    }
                    //Cambiamos estado: false, color rojo, porque en numeracion ya no hay, solo en historial hay
                    $query = " UPDATE $tabla SET activo=? WHERE id=?";
                    $values = [false, $id];
                    $res = update($query, $values);
                    //Actualizamos en numeracion
                    $query = " UPDATE numeracion SET  id_$tabla='$id_ultimo' ,fecha_ultimo_cambio=DEFAULT  WHERE id_$tabla = '$id' ";
                    $a = update($query, '');
                }
                //Si no lo ha podido crear es que existia
            } else {
                echo  $mensaje = "<div id='mensaje-admin' class='alert alert-danger'>Ya existe el registro: $valor, $ip, $des</div>";
            }
            //Si no lo contienen ni numeracion ni historial , actualizamos en su tabla
        } else {
            //Servidor puede tener combinaciones diferentes de host, descripcion, ip      
            if ($tabla == 'servidor') {
                $query = " UPDATE $tabla SET host=?, descripcion=? ,  ip=? WHERE id=?";
                $values = [$host, $des, $ip, $id];
            } else {
                $query = " UPDATE $tabla SET $tabla=? WHERE id=?";
                $values = [$valor, $id];
            }
            $res = update($query, $values);
            //Si no lo graba es que existe la combinacion
            if (!$res)  echo  $mensaje = "<div id='mensaje-admin' class='alert alert-danger'>Hay un registro que contiene $valor, $ip, $des</div>";
            else //Para que actualize el array $admins de selects
                $actualiza = true;
        }
    }

    if ($accion == 'Act-Des') {
        $res = carga('numeracion', "id_$tabla=$id", '');
        if (sizeOf($res) > 0) $existe = true;
        /**lo busca en el filtro para ver si existe en numeracion */
        if ($existe) {
            echo  $mensaje = "<div id='mensaje-admin' class='alert alert-danger'>Hay un registro/s que contiene $valor $ip $des</div>";
        } else {
            //si no existe en numeracion vemos si en historial
            $res = carga('numeracion_historial', "id_$tabla=$id", '');
            if (sizeOf($res) > 0) {
                $row = carga($tabla, "id=$id", '');
                $activo = $row[0]['activo'];
                if ($activo) {
                    $values = array(false, $id);
                    $estado = 'desactivado';
                    $id_estado = $id_estado_desactivado;
                } else {
                    $values = array(true, $id);
                    $estado = 'activado';
                    $id_estado = $id_estado_activado;
                }
                $query = "  UPDATE $tabla SET  activo=?  WHERE id = ? ";
                $res = update($query, $values);
            } else
                $res = baja($tabla, $id);
        }
        if ($res === true)  $actualiza = true;
    }
    if ($tabla  == 'servidor') {
        if ($accion == 'ne' || $accion == 'ne-ck') {
            cargaSelsServidorNE();
        } else cargaSelsServidor();
    } else {
        cargaSels($tabla, $ps);
    }
}

//Carga seelct administracion
function cargaSels($t, $ps)
{
    global $mensaje, $admins, $actualiza;
    if ($actualiza) {
        $res = carga($t, '', "ORDER BY $t ASC");
        $admins[$t] = array($res);
    } else
        $res = $admins[$t][0];
    if ($mensaje != '') {
        echo  '<div id="hideCargaSels">';
    } ?>
    <label class="control-label"><?php echo $ps; ?></label>
    <select class="form-select w-100" id="select-<?php echo $t; ?>-admin" tabla="<?php echo $t; ?>">
        <?php
        cargaSel($res, $t); ?>
    </select>
    <?php
    if ($mensaje != '') {
        echo  '</div>';
    }
}

function cargaSel($res, $c)
{
    global $valor;
    $idbin = 0;
    foreach ($res as $r) {
        $id = $r['id'];
        $op = $r[$c];
        $ac = $r['activo'];
        $bg = '';
        $desactivado = '';
        if (!$ac) {
            $bg = "color:red;";
            $desactivado = ' (Desactivado)';
        }
        if ($op == $valor || $id == $idbin) {
            $idbin = $id;
            //si no le ponemos las comillas a style lo iguala a selected si $bg es ''
            echo "<option  class='admin' value=$id style='$bg' selected>"; 
        } else {
            echo "<option  class='admin'  value=$id style=$bg>";
        }
        echo $op . $desactivado."</option>";
    }
}



//Carga seelct administracion
function cargaSelsServidor()
{
    global $id, $mensaje, $admins, $actualiza;
    $host = $ip = $des  = '';
    $idbin = $id;
    if ($actualiza) {
        $res = carga('servidor', '', 'ORDER BY host ASC');
        $admins['servidor'] = array($res);
    } else  $res = $admins['servidor'][0];
    if ($id != '') {
        foreach ($res as $r) {
            if ($r['id'] == $id) {
                $host = $r['host'];
                $ip = $r['ip'];
                $des = $r['descripcion'];
            }
        }
    }
    //Para poner el mensaje de error opr foreign key
    if ($mensaje != '') {
        echo  '<div id="hideCargaSels">';
    }
    //
    echo '
<div class="row d-flex px-3">
    <div class="form-group w-50   mr-4" style="height:60px;">
        <label class="control-label">Host</label>
        <input type="text" class="form-control mr-4 w-100" id="servidor-admin" style="width:150px;" value="' . $host . '" >
    </div>
    <div class="form-group w-50 " style="height:60px;">
        <label class="control-label">Hosts</label>
        <select class="form-select w-100" id="select-servidor-admin" tabla="servidor"> ';
    foreach ($res as $r) {
        $id = $r['id'];
        $host = $r['host'];
        $ac = $r['activo'];
        $bg = '';
        $desactivado = '';
        if (!$ac) {
            $bg = "color:red;";
            $desactivado = ' (Desactivado)';
        }
        if ($id == $idbin) {
            echo "<option class='admin'  value='$id' selected style=$bg>".$host . $desactivado."</option>";
        } else {
            echo "<option class='admin'  value='$id' style=$bg>".$host . $desactivado."</option>";
        }
    }
    echo '
        </select>
    </div>
</div>
<div class="row d-flex px-3 " style="height:60px;">
    <div class="form-group w-50 mr-4" style="height:60px;">
        <label class="control-label">Ip</label>
        <input type="text" class="form-control mt-1 w-100" id="ip-admin" style="width:150px;" value="' . $ip . '" >
    </div>
    <div class="form-group w-50 " style="height:60px;">
        <label class="control-label">Descripción</label>
        <input type="text" class="form-control mt-1 w-100" id="descripcion-admin" style="width:150px;" value="' . $des . '" >
    </div>
</div>';

    if ($mensaje != '') {
        echo  '</div>';
    }
    $_GET = '';
}

//Carga seelct servidor nuevo / editar
function cargaSelsServidorNE()
{
    global $id, $accion, $admins, $actualiza;
    $host = $ip = $des = '';
    $idbin = $id;
    if ($actualiza) {
        $res = carga('servidor', '', 'ORDER BY host ASC');
        $admins['servidor'] = array($res);
    } else  $res =  $admins['servidor'][0];
    if ($id != '') {
        foreach ($res as $r) {
            if ($r['id'] == $id) {
                $host = $r['host'];
                $ip = $r['ip'];
                $des = $r['descripcion'];
            }
        }
    }
    echo    '<div class="form-group w-100 ">
                <div class="form-group d-flex justify-content-between " style="height:60px;">
                    <div class="form-group " >';
    if ($accion == 'ne-ck') {
    ?> <div class="d-flex justify-content-between">
            <label for="sel-id_servidor-ne-con">Servidor</label>
            <input type="checkbox" name="servidor-ck">
        </div>
    <?php       } else {
        echo '    <label for="sel-id_servidor-ne">Servidor</label>';
    }    ?>
    <select id="sel-id_servidor-ne" class="form-select" name="id_servidor" tabla="servidor">
    <?php foreach ($res as $r) {
        $id = $r['id'];
        $host = $r['host'];
        $ac = $r['activo'];
        if ($ac) {
            if ($id == $idbin) {
                echo "<option    value='$id' selected>$host</option>";
            } else {
                echo "<option   value='$id' >$host</option>";
            }
        }
    }
    echo '
                    </select> </div>';
                    if ($accion == 'ne-ck') {
                        echo'<div class="form-group ip-admin-ck">';
                    }else {
                        echo'<div class="form-group ip-admin">';
                    }                 
                echo'<label for="ip-admin" class="col col-form-label col-form-label-sm">Ip</label>';                   
                echo'<input id="ip-admin" type="text" class="form-control w-100"    value="' . $ip . '" disabled>
                    </div>
                </div>';
                if ($accion == 'ne-ck') {
                    echo'<div class="form-group w-100   ip-admin-ck" style="height:60px;">';
                }else {
                    echo'<div class="form-group w-100   ip-admin" style="height:60px;">';
                }               
            echo'   <label class="col col-form-label col-form-label-sm">Descripción</label>
                    <input id="descripcion-admin" type="text" class="form-control "  value="' . $des . '" disabled>
                </div>
            </div>';

    $_GET = '';
}

//Graba en la tabla dada
function grabaT($camposa, $values, $tabla)
{
    $bcampos = [];
    for ($i = 0; $i < sizeOf($camposa); $i++) {
        array_push($bcampos, '?');
    }
    $camposs = implode(", ", $camposa);    //Nos da un string
    $bcamposs = implode(" , ", $bcampos);
    $query = " INSERT INTO $tabla ($camposs) VALUES ($bcamposs) ";
    return  insert($query, $values);
}



    ?>