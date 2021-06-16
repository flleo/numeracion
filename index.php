<?php
include 'consultas.php';

$tabla  = $condicion = $value = $titulo = $message = $motivo_baja = $tipo_numero = $id = $operador = $ruta = $descripcion =  $servidor = $entrega = $estado = $tipo = $fecha_alta = $fecha_ultimo_cambio =  $cliente_actual = $numeros_desvios = $observaciones = '';
$numero = 'todos';
$camposr = array('numero', 'id_tipo', 'id_operador',  'id_estado',  'id_tipo_numero', 'id_servidor', 'id_entrega', 'fecha_alta', 'cliente_actual', 'numeros_desvios', 'observaciones', 'id_motivo_baja', 'reactivar');
$camposacon = array('id_tipo', 'id_operador', 'id_estado', 'id_tipo_numero', 'id_servidor', 'id_entrega');
$camposn = array('numero', 'id_tipo', 'id_operador', 'id_estado', 'id_tipo_numero', 'id_servidor', 'id_entrega', 'cliente_actual', 'numeros_desvios', 'observaciones');
$camposb = array('numero', 'id_tipo', 'id_operador', 'id_estado', 'id_tipo_numero', 'id_servidor', 'id_entrega', 'fecha_alta', 'cliente_actual', 'numeros_desvios', 'observaciones', 'id_motivo_baja');
$camposf = array('numeros', 'id_tipos', 'id_operadors', 'id_estados', 'id_tipo_numeros', 'id_servidors', 'id_entregas', 'fecha_altas', 'fecha_ultimo_cambios', 'cliente_actuals', 'numeros_desvioss', 'observacioness', 'id_motivo_bajas');
$pcampos = array('Número', 'Operador', 'Tipo', 'Estado',  'Tipo número', 'Servidor', 'Entrega',  'Fecha&nbsp;alta', 'Fec.&nbsp;últ.&nbsp;cambio', 'Client.&nbsp;actual', 'Núm.&nbsp;de&nbsp;desvío', 'Descripción', 'Observaciones');
$bcampos  = $values  = $filtros = $valuesRecuperados  = $res = [];
$nregistros = 0;
$pintado = $reactivado = false;

//Inicio

//quitar filtros
if (isset($_POST['quitar_filtros'])) {
    $_POST = [];
}

if ($_POST == []) {
    $tabla = 'numeracion';
    bindeaFiltros();
}


if (isset($_POST['th_fecha'])) {
    $tabla = 'numeracion_historial';
    bindeaFiltros();
}

if (isset($_POST['numeracion'])) {
    $tabla = 'numeracion';
    bindeaFiltros();
}

//Filtrar numeracion
if (isset($_POST['filtrar']) || isset($_POST['filtrar_historial'])) {
    $tabla = $_POST['titulo'];
    bindeaFiltros();
}

//Filtrar historial
if (isset($_POST['historial']) || isset($_POST['historial_todos']) || isset($_POST['historial_con'])) {
    $tabla = 'numeracion_historial';
    bindeaFiltros();
}

if (isset($_POST['page'])) {
    $tabla = $_POST['tabla'];
    bindeaFiltros();
}

//Nuevo
if (isset($_POST['graba'])) {
    $tabla = 'numeracion';
    //Para varios numeros de entrada
    $numeross = $_POST['numero'];
    $numeross = explode(",", $numeross);
    foreach ($numeross as $num) {
        $num = str_replace(' ', '', $num);
        datosModal();
        $id = graba($camposn, $tabla);
        if ($id > 0) {
            $message = "<div class='alert alert-success'>El número " . $num . ", ha sido grabado con éxito</div>";
        } else {
            $message = "<div id='message-yaexiste' class='alert alert-danger'>El número " . $num . ", ya existe</div>";
        }
        bindeaFiltros();
    }
}

if (isset($_POST['actualiza'])) {
    $id = $_POST['id'];
    $numero = $_POST['numero'];
    $id_motivo_baja = null;
    $ok = recuperaYGraba('numeracion', 'numeracion_historial', $camposa, $camposa, $id);
    if ($ok > 0) {
        actualiza();
        $tabla = 'numeracion';
        bindeaFiltros();
    }
}

//Actualiza acciones conjuntas
if (isset($_POST['actualiza_con'])) {
    if($_POST['id'] != '') {
        $ids = explode(',', $_POST['id']);
        $numeros = explode(',', $_POST['numero']);
        $id_motivo_baja = null;
        for ($i = 0; $i < sizeOf($ids); $i++) {
            $id = $ids[$i];
            $numero = $numeros[$i];
            $ok = recuperaYGraba('numeracion', 'numeracion_historial', $camposa, $camposa, $id);
        }
        if ($ok > 0) {
            $id = implode(', ', $ids);
            $numero = implode(', ', $numeros);
            actualiza();
            $ids = '';
            $tabla = 'numeracion';
            bindeaFiltros();
        }
    } else {
        $tabla = 'numeracion';
        bindeaFiltros();
    }
}

if (isset($_POST['baja'])) {
    $ids = explode(',', $_POST['id_baja']);
    if ($ids[0] != '') {
        $numeros = explode(',', $_POST['numero_baja']);
        $id_motivo_baja = $_POST['id_motivo_baja'];
        $id_estado  = $admins['estado'][0][array_search('Baja', array_column($admins['estado'][0], 1))]['id'];
        for ($i = 0; $i < sizeOf($ids); $i++) {
            $id = $ids[$i];
            $numero = $numeros[$i];
            $ok = recuperaYGraba('numeracion', 'numeracion_historial', $camposa, $camposr, $id);
        }
        if ($ok) {
            $ok = baja('numeracion', $_POST['id_baja']);
            if ($ok) {
                $message = "<div class='alert alert-success'>El número " . implode(',', $numeros) . ", ha sido baja, con éxito</div>";
            }
            $tabla = 'numeracion';
            bindeaFiltros();
        }
    } else {
        $message = "<div class='alert alert-danger'>Debe seleccionar un número</div>";
        $tabla = 'numeracion';
        bindeaFiltros();
    }
}

//Reactivar registro de baja
if (isset($_POST['reactivar'])) {
    $id = $_POST['id'];
    $id_estado  = $admins['estado'][0][array_search('Activado', array_column($admins['estado'][0], 1))]['id'];
    $id_motivo_baja = ''; //para que al recuperar no lo cargue
    $ok = recuperaYGraba('numeracion_historial', 'numeracion', $camposn, $camposn, $id);
    if ($ok) {
        $message = "<div class='alert alert-success '>El número " . $numero . ", ha sido recuperado con éxito</div>";
        $query = "UPDATE numeracion_historial set reactivar=? where id=?";
        update($query, [false, $id]);
        $tabla = 'numeracion';
        bindeaFiltros();
    }
}

//Actualiza reasignaciones

    foreach ($tablas as $t) {
        if (isset($_POST["$t-ck"])) {
            $ck = $_POST["$t-ck"];
            //Si esta checkeado
            if ($ck) {
                if (isset($_POST["sel-ori-$t"]) && isset($_POST["sel-rea-adm-$t"])) {
                    $id_ori = $_POST["sel-ori-$t"];
                    $id_rea = $_POST["sel-rea-adm-$t"];
                    $nums = recuperaYGrabaR($t);
                    if (count($nums) > 0) {
                        $query = "UPDATE numeracion set id_$t=?, fecha_ultimo_cambio=DEFAULT where id_$t=?";
                        $values = [$id_rea, $id_ori];
                        $ok = update($query, $values);
                        if ($ok) {
                            $message = "<div class='alert alert-success'>El número/s " . implode(',', $nums)  . ", fue actualizado con éxito</div>";
                        }
                    }
                }
            }
            $tabla = 'numeracion';
            bindeaFiltros();
        }
    }




///////////////////////////////////////Funciones //////////////////////////////////////

//Recogemos siguientes campos
function datosModal()
{
    global $values, $camposn, $camposa, $camposaa;
    $values = $camposaa = [];
    if (isset($_POST['graba'])) {
        $campos = $camposn;
    } else {
        $campos = $camposa;
    }
    if (isset($_POST['fecha_alta'])) $_POST['fecha_alta'] = date('Y-m-d H:m:s', strtotime($_POST['fecha_alta']));
    if (isset($_POST['fecha_ultimo_cambio'])) $_POST['fecha_ultimo_cambio'] = date('Y-m-d H:m:s', strtotime($_POST['fecha_ultimo_cambio']));
    for ($i = 0; $i < sizeof($campos); $i++) {
        if (isset($_POST[$campos[$i]])) {
            $val = $_POST[$campos[$i]];
            array_push($values, $val);
            array_push($camposaa, $campos[$i]);
        }
    }
}

//Recuperamos numeracion donde id_tabla y grabamos en historial
function recuperaYGrabaR($t)
{
    global $id_ori, $camposa;
    $condicion = "id_$t = $id_ori";
    $res = cargaBindeo('numeracion', $condicion);
    $nums = [];
    $camposb = [];
    for ($i = 0; $i < sizeOf($camposa); $i++) array_push($camposb, '?');
    $camposbb = implode(", ", $camposb);    //Nos da un string
    if ($res != '') {
        foreach ($res as $r) {
            $values = [];
            for ($i = 0; $i < sizeOf($camposa); $i++) {
                array_push($values, $r[$i + 1]);
            }
            $camposs = implode(", ", $camposa);    //Nos da un string
            $query = " INSERT INTO numeracion_historial ($camposs) VALUES ($camposbb) ";
            $ok = insert($query, $values);
            if ($ok > 0)  array_push($nums, $r[1]);
        }
    }
    if (sizeof($res) == sizeof($nums))
        return $nums;
}

/*Recuperamos datos del numero en base de datos*/
function recuperaRegistro(
    $tabla_carga,
    $condicion,
    $campos
) {
    global $numero, $valuesRecuperados, $id_motivo_baja, $id_estado;
    //Para recuperar los originales sin modificar , para el historial
    $res = cargaBindeo($tabla_carga, $condicion);
    foreach ($res as $key) {
        $numero = $key['numero'];
        if ($id_estado != '') $key['id_estado'] = $id_estado;
        foreach ($campos as $c) {
            array_push($valuesRecuperados, $key[$c]);
        }
    }
    if ($id_motivo_baja != '') {
        array_push($valuesRecuperados, $id_motivo_baja);
        array_push($valuesRecuperados, true);
    }
}

function recuperaYGraba($tabla_recupera, $tabla_graba, $camposRecupera, $camposGraba, $id)
{
    recuperaRegistro($tabla_recupera, 'id=' . $id, $camposRecupera);
    $ok = graba(
        $camposGraba,
        $tabla_graba
    );
    return $ok;
}

function actualiza()
{
    global  $camposaa, $values,  $message, $tabla, $id, $numero;
    $tabla = 'numeracion';
    datosModal();
    bindeaRecuperados();
    $q1 = [];
    for ($i = 1; $i < sizeOf($camposaa); $i++) {
        array_push($q1, $camposaa[$i]
            . '="' . $values[$i] . '"');
    }
    $q1s = implode(", ", $q1);
    $query = " UPDATE $tabla SET $q1s,fecha_ultimo_cambio=DEFAULT WHERE id in ($id) ";
    $ok = update($query, '');
    if ($ok) {
        $message = " <div class='alert alert-success'>El número/s " . $numero . ", fué actualizado con éxito</div>";
    }
    return $ok;
}



function bindeaRecuperados()
{
    global  $values, $valuesRecuperados;
    if (sizeOf($valuesRecuperados) > 0) {
        $values = $valuesRecuperados;
    }
}

function graba($campos, $tabla)
{
    global $values, $valuesRecuperados;
    bindeaRecuperados();
    $ok = grabaT($campos, $values, $tabla);
    if ($ok > 0) {
        $valuesRecuperados = [];
    }
    return $ok;
}

//Rellenamos los selects filtros

function fechaDesAsc()
{
    if (isset($_SESSION['th_fecha']) && $_SESSION['th_fecha'] != '') {
        $order = "ORDER BY fecha_ultimo_cambio desc";
        $_SESSION['th_fecha'] = '';
    } else {
        $order = "ORDER BY fecha_ultimo_cambio";
        $_SESSION['th_fecha'] = 'selected';
    }
    return $order;
}


///////////////////////////////////////////////



//Funciones prepara listado para pintarlo/////////////////////////////////////////////////////////////

//bindeaRecuperados los filtros
function bindeaFiltros()
{
    global $camposf, $tabla, $tam, $condiciones, $ncampos;
    $ncampos = 0;
    $condiciones = [];

    //Quitamos el ultuimop filtro motivo_baja para numeracion
    if ($tabla == 'numeracion') {
        $tam = sizeof($camposf) - 1;
    } else {
        $tam = sizeof($camposf);
    }
    for ($i = 0; $i < $tam; $i++) {
        prepara($i);
    }
}

//Carga filtros
function prepara($i)
{
    global $camposf, $campos, $ncampos, $tam, $condiciones, $condicion;
    $ncampos++;
    $condicion = '';
    //Carga filtros
    $campo = $camposf[$i];
    if (isset($_POST[$campo])) {
        $valor = $_POST[$campo];
        if ($valor == 'Todos') {
            $valor = '';
        }
        if ($valor != '') {
            $condicion = $campos[$i] . " like '%$valor%' ";
        }
        if ($condicion != '')
            array_push($condiciones, $condicion);
    }
    //Carga solo el numero para historial
    if (isset($_POST['historial_con']) || isset($_POST['historial'])) {
        $valor = $_POST['numero'];
        $condicion = "numero in ($valor) ";
        $tam = 1;
        array_push($condiciones, $condicion);
    }
    if ($ncampos == $tam) {
        cargaDatosFiltrados();
    }
}

//Traemos de bd, con las condiciones y ordenados
function cargaDatosFiltrados()
{
    global $condiciones, $tabla, $res, $condicion;
    //Orden de los registros 
    if ($tabla == 'numeracion_historial') {
        //Si historial incluimos th-feccha
        if (isset($_POST['th_fecha'])) {
            $order = fechaDesAsc();
        } else {
            $order = "ORDER BY fecha_ultimo_cambio desc";
        }
    } else
        $order = "ORDER BY numero asc, fecha_ultimo_cambio desc";
    //
    if (sizeof($condiciones) > 1) {
        $condiciones = implode(' AND ', $condiciones);
    } else {
        $condiciones = implode('', $condiciones);
    }
    $res = carga($tabla, $condiciones, $order);
    filtros();
}


function filtros()
{
    global $res, $tabla, $filtros, $order,  $id_tipos, $id_operadors, $id_estados, $id_tipo_numeros, $id_servidors, $id_entregas, $fecha_altas, $fecha_ultimo_cambios, $cliente_actuals, $numeros_desvioss, $observacioness, $id_motivo_bajas;

    $numeros = $id_tipos = $id_operadors = $id_estados = $id_tipo_numeros = $id_servidors = $id_entregas = $fecha_altas = $fecha_ultimo_cambios = $cliente_actuals = $numeros_desvioss = $observacioness = $id_motivo_bajas = [];
    $filtros = array(
        $numeros, $id_tipos, $id_operadors, $id_estados, $id_tipo_numeros, $id_servidors,
        $id_entregas, $fecha_altas, $fecha_ultimo_cambios, $cliente_actuals, $numeros_desvioss, $observacioness
    );
    $ftablas = ['tipo', 'operador', 'estado', 'tipo_numero', 'servidor', 'entrega'];
    if ($tabla == 'numeracion_historial') {
        array_push($filtros, $id_motivo_bajas);
        array_push($ftablas, 'motivo_baja');
    } /*Rellenamos*/
    foreach ($res as $re) {
        for ($i = 0; $i < sizeof($filtros); $i++) {
            if (!in_array($re[$i + 1], $filtros[$i]) && $re[$i + 1] != '') {
                array_push($filtros[$i], $re[$i + 1]);
            }
        }
    } /*Ordenamos*/
    $pselects = $filtros;
    $filtros = [];
    for ($i = 0; $i <
        sizeOf($pselects); $i++) {
        if ($i != 7 && $i != 8) { /*fechas */
            asort($pselects[$i]);
        } else {
            arsort($pselects[$i]);
        }
        array_push($filtros, $pselects[$i]);
    }

    pagina();
}

/*Hasta el listado //Pagina inicio*/
function pagina()
{
    global  $message, $titulo,  $camposf, $pcamposf, $filtros, $tabla,
        $nregistros, $nregistrosPorPagina, $npaginas, $pag, $res;
?>

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="pragma" content="no-cache" />
        <title>Listín Telefónico</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
        </script>
        <link type="text/css" rel="stylesheet" href="css/styles.css">
        <style>
            /* Medium devices (tablets, 617px and up)**/
            @media (max-width: 768px) {
                .modal-content {
                    margin: 200px auto !important;
                    margin-left: 45% !important;
                    min-width: 600px !important;
                }

                .container {
                    min-width: 900px;
                }

                table {
                    margin: auto !important;
                    max-width: 80% !important;
                }
            }

            @media (min-width: 618px) {
                .modal-content {
                    margin: 80px auto !important;
                    margin-left: 15% !important;
                    min-width: 70% !important;
                }
            }

            @media (min-width: 868px) {
                .modal-content {
                    margin: 100px auto !important;

                }

                #movimientos {
                    display: flex;
                    flex-wrap: wrap;
                }

                table {
                    margin: 5% !important;
                    max-width: 90% !important;
                }
            }

            tr {
                height: 65px;
            }

            [class$=filter] {
                width: 145px;
            }

            input,
            select {
                height: 35px;
            }

        </style>
    </head>

    <body>
        <?php
        echo $message; ?>
        <div class="container w-100" style="max-width:2000px;">
            <?php
            if (isset($_POST['page'])) {
                $tabla = $_POST['tabla'];
            }
            if ($tabla == 'numeracion') {
                $titulo = 'numeración';
            } else {
                $titulo = 'historial';
            } ?>
            <h3 class="text-center">Listín Telefónico: <span id="titulo"><?php echo $titulo; ?></span></h3>
            <div class="   ">
                <!--Pintamos los filtros-->
                <form action="index.php" method="POST" id="movimientos">
                    <div id="filtros" class="d-flex flex-wrap">
                        <?php
                        for ($i = 0; $i < sizeof($filtros); $i++) {
                            /*no ocultos*/
                            if ($camposf[$i] == 'numeros' || $camposf[$i] == 'id_operadors' || (isset($_POST[$camposf[$i]]) && $_POST[$camposf[$i]] != '' && $_POST[$camposf[$i]] != 'Todos')) {
                                echo '<div class="form-group  px-1 " >';
                            } else {
                                /*Filtros ocultos*/
                                echo '<div hidden class="form-group  px-1 mas-filtros" >';
                            }
                            echo "<label for='$camposf[$i]'>$pcamposf[$i]</label>";
                            $value = '';
                            //Se rrecogen los valores de los filtros
                            if (isset($_POST[$camposf[$i]]) && !isset($_POST['quitar_filtros'])) {
                                $value = $_POST[$camposf[$i]];
                            } // input numeros
                            if ($camposf[$i] == 'numeros') {
                                if (isset($_POST['numero'])) {
                                    $value  = $_POST['numero'];
                                }
                                echo "<input id='$camposf[$i]' class='form-control filter' name=$camposf[$i]  type='number' value='$value' ></div>";
                                /*10 numeros_desvios; 12 motivo_baja : campos con select */
                            } elseif ($i < 10 || $i == 12) {
                                $tablasf = array(
                                    '', 'tipo', 'operador', 'estado', 'tipo_numero', 'servidor',
                                    'entrega', '', '', '', '', ''
                                );
                                if ($tabla == 'numeracion_historial') array_push($tablasf, 'motivo_baja');
                                select($camposf[$i], $filtros[$i], $tablasf[$i], $value);
                                echo ' </div>';
                            } else { // input desvios   ||  input observaciones                              
                                echo "<input id='$camposf[$i]-filter' class='form-control filter' name=$camposf[$i]   value='$value'></div>";
                            }
                        }
                        /*Quitar Filtros*/
                        echo ' <div  class="form-group mx-1" >
            <label>&nbsp;</label>  
            <input type="submit" id="btn-quitar-filtros" class="btn border-info text-info filter form-control" value="Quitar filtros" />';
                        echo '  </div>';
                        echo ' <div  class="form-group  px-1" >
                <label>&nbsp;</label>';
                        if ($tabla == 'numeracion_historial') {
                            echo '<input type="submit" id="filtrar-historial-btn" class="btn btn-info filtrar form-control" name="filtrar_historial"   value="Filtrar" />';
                        } else {
                            echo '<input  type="submit" class="btn btn-info  form-control" name="filtrar"   value="Filtrar" />  ';
                        }
                        echo '</div>';
                        /*Boton + filtros*/
                        echo ' <div class="form-group  px-1">
                        <label>&nbsp;</label>
                        <input type="button" class="btn  btn-light btn-filtros border-secondary form-control"
                            value="+ Filtros" style="height:35px;width:100opx;" />
                    </div>'; ?>
                    </div>
                    <div class=" d-flex flex-wrap w-100  m-1  ml-0 pl-0">
                        <?php if ($tabla == 'numeracion_historial') {
                            echo '<input type="submit"  class="btn btn-primary btn-numeracion  " name="numeracion" value="Numeración" />  ';
                        } else {
                            //Nuevo, administrador, historial
                            echo '               
                    <input type="button" class="btn btn-primary btn-nuevo mb-1" data-bs-toggle="modal" data-bs-target="#modal"  value="Nuevo"  />  
                    <input id="btn-admin" type="button" class="btn btn-warning btn-admin mb-1 mx-2"  value="Administración" 
                    data-bs-target="#modal-admin" data-bs-toggle="modal" name="administracion"  />                   
                    <input type="submit" id="btn-historial-todos" class="btn btn-secondary text-light border-secondary btn-numeracion mb-1" name="historial_todos"   value="Historial" />  ';
                        } ?>
                        <!--Exportar-->
                        <form id="form-exporta" action="consultas.php" method="post">
                            <input hidden name="titulo" value="<?php echo $tabla; ?>">
                            <input id='exporta' tabla='<?php echo $tabla; ?>' type="submit" name="exporta" class="btn text-success btn-light border-success mx-2" data-toggle="tooltip" title="Exportar excel" value="Exportar" />
                        </form>
                        <!--//Paginacion*/-->
                        <nav class="pb-0" aria-label="Page navigation example ">
                            <input hidden name="tabla" value="<?php echo $tabla; ?>">
                            <ul class="pagination ml-5  d-flex">
                                <?php
                                $nregistros = sizeof($res);
                                $nregistrosPorPagina = 25;
                                $npaginas = ceil($nregistros / $nregistrosPorPagina);
                                $pa = 1;
                                $po = $pa + 1;
                                $pagg = 0;
                                if (isset($_POST['page'])) {
                                    $pagg = $pag = $_POST['page'];
                                    if ($pag > 1) {
                                        $pa = $pag - 1;
                                    } else {
                                        $pa = 1;
                                    }
                                    if ($pag < $npaginas) {
                                        $po = $pag + 1;
                                    } else {
                                        $po = $npaginas;
                                    }
                                }
                                echo '                        <li class="page-item "> 
                         <input name="tabla" value="' . $tabla . '" hidden>   
       <button type="submit"  class="page-link " name="page" value="' . $pa . '" aria-label="Previous"> ';
                                echo '                                       <span aria-hidden="true">&laquo;</span>
                         </button>
                     </li>';
                                $pag = 1;
                                while ($pag <= $npaginas) {
                                    $p = $pag;
                                    $pag++;
                                    echo '                      <li class="page-item">';
                                    if ($pagg == $p || ($pagg == 0 && $p == 1)) {
                                        echo '<button id="page-selected" type="submit"  class="page-link" name="page" value="' . $p . '" aria-label="Previous">' . $p . '</button>';
                                    } else {
                                        echo '<button type="submit"  class="page-link" name="page" value="' . $p . '" aria-label="Previous">' . $p . '</button>';
                                    }
                                    echo '                            </li>';
                                }
                                echo '
                     <li class="page-item">
      <button type="submit"  class="page-link"  name="page" value="' . $po . '" aria-label="Previous">                        
                       <span aria-hidden="true">&raquo;</span>
                         </button>
                     </li>
             </ul>                       
     </nav>  ';
                                //echo "<input hidden name='numeros' value=$value>";
                                //Opciones conjuntas: reasignar,editar,historial,baja///////////////
                                if ($tabla == 'numeracion') {
                                ?>
                                <?php echo " 
                <input  type='button' class='btn btn-info btn-reasignar-con  mb-1 mx-2' data-toggle='modal' title='Reasigna todos los registros de un tipo, hacia otro tipo' value='Reasignar' style='height:35px;'>                 
                <input  type='button' class='btn btn-primary btn-editar-con  mb-1' data-toggle='modal' value='Editar' style='height:35px;'>               
                <input id='btn-historial-con' type='submit'  name='historial_con' class='btn btn-secondary text-light btn-historial-con mx-2' value='Historial' style='height:35px;'>";
                                    echo   '<input name="btn_baja_con" type="submit" class="btn btn-danger btn-baja  " data-toggle="modal"   value="Baja" >';
                                }
                                echo '                            
                </form>
            </div>
        </div>';

                                pinta();
                            }

                            /*Pintado selects filtros: campo de filtro, filtro(select), tabla $t, si se oculta o no*/
                            function select($campof, $filtro, $t, $value)
                            {
                                global $admins;
                                echo '  <select class="form-select " id="select-filtro-' . $campof . '" name="' . $campof . '" style="width:115px;">';
                                echo "      <option  value='Todos' >Todos</option>";

                                foreach ($filtro as $num) {
                                    if ($t != '') {
                                        $v = $admins[$t][0][array_search($num, array_column($admins[$t][0], 0))][$t];
                                        /**nos da la id */
                                        if ($t == 'servidor')
                                            $v = $admins[$t][0][array_search($num, array_column($admins[$t][0], 0))]['host'];
                                        /**nos da la id */
                                    } else $v = $num;
                                    if ($num == $value) {
                                        echo "<option  value='$num' selected>$v</option>";
                                    } else {
                                        echo "<option  value='$num'>$v</option>";
                                    }
                                }
                                echo ' </select>';
                            }

                            //Pintar Listado/////////////////////////////////////////////////////////////
                            function pinta()
                            {
                                global $admins, $numero, $tablas, $tabla, $pag, $npaginas, $nregistrosPorPagina, $res;
                                //Filtros inicio
                                if (isset($_POST['page'])) {
                                    $pag = $_POST['page'];
                                }
                                if ($pag == ($npaginas + 1)) {
                                    $pag = 1;
                                }
                                echo " <div class='row d-flex mt-0 pt-0 '>";
                                if (isset($_POST['numeros'])) {
                                    $numero = $_POST['numeros'];
                                }
                                if ($tabla != 'numeracion' && $numero != '') {
                                    echo "<h5 class='mr-5 mt-0 pt-0'>Historial&nbsp;del&nbsp;número:&nbsp;<b>$numero</b></h5>";
                                }
                                $inicio =  $ninicio = ($pag - 1) * $nregistrosPorPagina;
                                $fin = $nfin = $pag * $nregistrosPorPagina;
                                $totalr = sizeof($res);
                                if ($fin > $totalr) {
                                    $fin = $totalr;
                                }
                                if ($inicio == $totalr) {
                                    $inicio = $totalr - 1;
                                }
                                echo "<h6 class='ml-5 '>Registros del <b>" . ($inicio + 1) . "</b> al <b>$fin</b> de un total de $totalr registros</h6>";
                                echo "
     </div>
        <table  class='table mt-4' >
                <tr>";

                                $pcampos = array('Número', 'Operador', 'Tipo', 'Estado',  'Tipo número', 'Servidor',  'Opciones');
                                if ($tabla == 'numeracion') {
                                    $pcampos = array('Número', 'Operador', 'Tipo', 'Estado',  'Tipo número', 'Servidor',  'Opciones');
                                    echo "<th><input  type='checkbox' name='allcheck'></th>";
                                } else {
                                    $pcampos = array('Número', 'Operador', 'Tipo', 'Estado',  'Tipo número', 'Servidor', 'Fecha ult.cambio', 'Opciones');
                                }
                                for ($i = 0; $i < sizeOf($pcampos); $i++) {
                                    if ($pcampos[$i] != 'Fecha ult.cambio')
                                        echo "<th>$pcampos[$i]</th>";
                                    else {
                                        echo "<form method='post'>
                        <th><button id='th-fecha' class='btn' name='th_fecha' type='submit'>$pcampos[$i]</button></th>
                    </form>";
                                    }
                                }
                                echo " </tr>
            </thead>
            <tbody >";
                                $re = 0;   //numero de registro
                                foreach ($res as $key) {   //Filas de la tabla o registros
                                    $re++;
                                    if ($re > $ninicio && $re <= $nfin) {
                                        echo "<tr>";   //Registro/////////////////////
                                        if ($tabla == 'numeracion')
                                            echo "  <td id='check$re' re=$re><input  type='checkbox'></td>";
                                        echo "  <td hidden id='id$re'>" . $key['id'] . "</td>
                        <td  id='numero$re'>" . $key['numero'] . "</td>";
                                        //Campos nombre de las tablas segun id
                                        $op = $admins['operador'][0][array_search($key['id_operador'], array_column($admins['operador'][0], 0))]['operador'];
                                        $ti = $admins['tipo'][0][array_search($key['id_tipo'], array_column($admins['tipo'][0], 0))]['tipo'];
                                        $es = $admins['estado'][0][array_search($key['id_estado'], array_column($admins['estado'][0], 0))]['estado'];
                                        $tn = $admins['tipo_numero'][0][array_search($key['id_tipo_numero'], array_column($admins['tipo_numero'][0], 0))]['tipo_numero'];
                                        $se = $admins['servidor'][0][array_search($key['id_servidor'], array_column($admins['servidor'][0], 0))]['host'];
                                        $ip = $admins['servidor'][0][array_search($key['id_servidor'], array_column($admins['servidor'][0], 0))]['ip'];
                                        $en = $admins['entrega'][0][array_search($key['id_entrega'], array_column($admins['entrega'][0], 0))]['entrega'];
                                        //lista
                                        echo "<td hidden id='id_operador$re' value='" . $key['id_operador'] . "'>$op</td>";
                                        echo "<td  class='focus text-center' style='min-width:120px;' ><img id='img-id_operador$re' tabindex='0' data-toggle='tooltip' alt='$op' src='images/$op.jpg'  />
                <small>$op</small></td>";
                                        echo "<td  id='id_tipo$re'value=" . $key['id_tipo'] . " >$ti</td>";
                                        echo "<td  hidden id='id_estado$re' value=" . $key['id_estado'] . ">$es</td>";
                                        echo "<td  class='focus text-center ' style='min-width:100px;'><img id='img-id_estado$re' tabindex='0' data-toggle='tooltip' alt='$es' src='images/$es.jpg'  />
                <small>$es</small></td>";
                                        echo "<td  id='id_tipo_numero$re' value=" . $key['id_tipo_numero'] . ">$tn</td>";
                                        echo "<td  id='id_servidor$re'  value=" . $key['id_servidor'] . ">$se<small>$ip</small></td>";
                                        //Siguientes campos para +info           
                                        echo "<td  hidden id='fecha_alta$re'>" . $key['fecha_alta'] . "</td>";
                                        if ($tabla != 'numeracion_historial') {
                                            echo " <td hidden  id='fecha_ultimo_cambio$re'>" . $key['fecha_ultimo_cambio'] . "</td>";
                                        } else {
                                            echo " <td id='fecha_ultimo_cambio$re'>" . $key['fecha_ultimo_cambio'] . "</td>";
                                        }
                                        echo "  <td hidden id='cliente_actual$re'>" . $key['cliente_actual'] . "</td>
          <td hidden  id='id_entrega$re' value='" . $key['id_entrega'] . "'>$en</td>
          <td hidden id='numeros_desvios$re'>" . $key['numeros_desvios'] . "</td>
          <td hidden id='observaciones$re'>" . $key['observaciones'] . "</td>";
                                        if ($tabla == 'numeracion_historial') {
                                            if ($key['id_motivo_baja'] != '') {
                                                //motivo_baja 
                                                $mb = $admins['motivo_baja'][0][array_search($key['id_motivo_baja'], array_column($admins['motivo_baja'][0], 0))]['motivo_baja'];
                                                echo "<td  hidden id='id_motivo_baja$re' title='" . $key['id_motivo_baja'] . "'>$mb</td>";
                                                /*Reactivar*/
                                                if ($key['reactivar'])
                                                    echo "  <form  method='post'>
                                    <input hidden name='id' value='" . $key['id'] . "'>
                                    <td>
                                        <input re='$re' type='button' class='btn btn-info btn_info' data-toggle='modal'  value='+ info'>
                                        <input re='$re' name='reactivar' type='submit' class='btn btn-warning btn-reactivar ' value='Reactivar'>
                                    </td>
                                </form> ";
                                                else {
                                                    echo "<td><input re='$re'  type='button' class='btn btn-info btn_info' data-toggle='modal' value='+ info'></td>";
                                                }
                                            } else {
                                                echo "<td><input re='$re'  type='button' class='btn btn-info btn_info' data-toggle='modal' value='+ info'></td>";
                                            }
                                        } elseif ($tabla == 'numeracion') {
                                            //  +info,editar,historial,baja
                                            echo "
                              <form  method='post'>
                                  <td >
                                    <div class='d-flex flex-nowrap justify-content-center'>
                                        <input re='$re'  type='button' class='btn btn-info btn_info' data-toggle='modal'    value='+'>
                                        <input re='$re' type='button' class='btn btn-primary btn-editar mx-2' data-toggle='modal' value='E'>
                                        <form method='post' >
                                            <input hidden  name='numero' value=" . $key['numero'] . ">
                                            <input id='historial$re' type='submit'  name='historial' class='btn btn-secondary text-light btn-historial  ' value='H'>
                                        </form>";
                                            echo   '<input re="' . $re . '" name="btn_baja" type="submit" class="btn btn-danger btn-baja " data-toggle="modal"   value="B" style="margin-left:8px;">
                                    </div>
                                </td>
                                  
                              </form>';
                                        }
                                        echo "</tr>  ";
                                    }
                                }

                                echo '</tbody>
         </table>
         </div>
        <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
        <script type="application/javascript" src="jq/mijq.js"></script>
    </body>

    </html>
    ';
                            }

                            ///////////////////////////////////////modales////////////////////////////////////////////////////////////////////

                            //Para que al exposrtar no incluya los modales
                            if (!isset($_POST['exporta']) ) {
                                ?>
                                <!-- Modal Reasignar-->
                                <div class="modal fade" id="modal-reasignar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Reasignación de campos</h5>
                                                <button type="button" class="btn-close cerrar-reasignar" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form id="form-reasignar" name="reasignar" method="post">
                                                <div class=" modal-body d-flex justify-content-between">
                                                    <div class="w-25">
                                                        <h6>Original</h6>
                                                        <?php
                                                        for ($i = 0; $i < sizeOf($pcamposS) - 1; $i++) {
                                                            echo "<div class='d-flex '>
                    <input  type='checkbox' name='$tablas[$i]-ck' style='margin-right:2px;'>
                    <label >$pcamposS[$i]</label>                      
                </div>";
                                                            echo "<select class='form-select' name='sel-ori-$tablas[$i]' >";
                                                           /* foreach ($filtros[$i + 1] as $f) {
                                                                  echo "<option value=$f>$v<option>";
                                                            }*/
                                                            echo "</select>";
                                                        }
                                                        ?>
                                                    </div>
                                                    <button id="btn-reasignar-modal" class="btn border-secondary"  >Reasignar</button>
                                                    <div class="w-25 form-group ">
                                                        <h6>Reasignado</h6>
                                                        <?php
                                                        for ($i = 0; $i < sizeOf($pcamposS) - 1; $i++) {
                                                            echo "<div class='mt-2'";
                                                            echo "<label >$pcamposS[$i]</label>";
                                                            echo "<select class='form-select mt-1' name='sel-rea-adm-$tablas[$i]'>";
                                                            foreach ($admins[$tablas[$i]][0] as $f) {
                                                                if ($f['activo'])
                                                                    echo "<option value=$f[0]>$f[1]<option>";
                                                            }
                                                            echo "</select>";
                                                            echo "</div>";
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </form>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary cerrar-reasignar" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--Modal Admin-->

                                <div class="modal fade " id="modal-admin" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog moodal-dialog-centered" role="document">
                                        <div class="modal-content p-0 modaladmin">
                                            <div class="modal-header bg-info ">
                                                <h3 class="modal-title text-white mt-2 mb-0 p-0">Administración</h3>
                                                <button type="button" class=" btn-cerrar-admin" data-bs-dismiss="modal" aria-label="Cerrar">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body ">
                                                <div class="form-group ">
                                                    <?php for ($i = 0; $i < sizeOf($tablas); $i++) {
                                                        $t = $tablas[$i];
                                                        $ps = $pcamposSS[$i];
                                                        $p = $pcamposS[$i];
                                                    ?>
                                                        <div class="form-group mb-4 pt-2">
                                                            <div class=" d-flex flex-row-reverse pl-4  mb-2 ">
                                                                <div class="d-flex   flex-wrap btns-admin  btn-sm mr-4">
                                                                    <input type="button" class="btn btn-sm btn-success ml-4 btn-action-admin btn-success-<?php echo $t . ' ' . $t; ?>" value="Añadir">
                                                                    <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Seleccione un elemento">
                                                                        <input type="button" class="btn btn-sm btn-warning  mx-4 btn-action-admin btn-warning-<?php echo $t . ' ' . $t; ?>" value="Actualizar" disabled>
                                                                        <input type="button" class="btn btn-sm btn-danger  seleccione act-des btn-action-admin btn-danger-<?php echo $t . ' ' . $t; ?>" data-bs-toggle="modal" data-bs-target="#modal-error-borrar-admin" tabla="<?php echo $t; ?>" value="Act-Des" disabled>
                                                                    </span>
                                                                </div>
                                                                <h5 class="flex-grow-1"><?php echo $ps; ?></h5>
                                                            </div>
                                                        <?php if ($t == 'servidor') {
                                                            echo '            <div id="response-' . $t . '" class="row  px-4 ">';
                                                            cargaSelsServidor();
                                                            echo '             </div>';
                                                        } else {
                                                            echo '
                        <div class="row flex-nowrap px-4 ">
                            <div class="form-group w-50  mr-4" style="height:60px;">
                                <label class="control-label">' . $p . '</label>
                                <input type="text" class="form-control mr-4 w-100" id="' . $t . '-admin"
                                    style="width:150px;" autocomplete=off>
                            </div>
                            <div id="response-' . $t . '" class="flex-nowrap w-50 ">';
                                                            cargaSels($t, $ps);
                                                            echo '
                            </div>
                        </div>';
                                                        }
                                                        echo '
                    </div>';
                                                    }
                                                    echo '
                </div>
                <div class="modal-footer ">
                    <button type="button" class="btn btn-secondary btn-cerrar-admin" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>';
                                                        ?>

                                                        <!--Modal borrar admin-->
                                                        <div class="modal fade " id="modal-error-borrar-admin" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog moodal-dialog-centered " role="document">
                                                                <div id="modalcssbaja" class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h6 id="titulo-error-borrar-admin" class="modal-title text-secondary mt-2">
                                                                            Administración: datos</h6>
                                                                        <button class="close btn-cerrar" data-bs-dismiss="modal" aria-label="Cerrar">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body d-flex flex-wrap">
                                                                        <img id="img-error" class="mt-3 mx-4" src="images/Desactivado.jpg">
                                                                        <h6 class="mt-4" id="seguro-borrar"></h6>
                                                                    </div>
                                                                    <div class="modal-footer ">
                                                                        <button id="btn-borrar-dato-final" name="borrar_dato_admin" class="btn btn-danger">Vale</button>
                                                                        <button class="btn btn-secondary btn-cerrar " data-bs-dismiss="modal">Cerrar</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Modal Editar/Nuevo-->
                                                        <div class="modal fade" id="modal" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog moodal-dialog-centered" role="document">
                                                                <div class="modal-content p-0 modalcss">
                                                                    <div class="modal-header bg-info ">
                                                                        <h5 id="m-titulo" class="modal-title text-white mt-2 mb-0 p-0">
                                                                            Nuevo
                                                                            registro</h5>
                                                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar4">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <form role="form" method="post" id="modal-form-id" enctype="multipart/form-data" autocomplete=off>
                                                                        <div class="modal-body">
                                                                            <div class="form-group p-2 d-flex flex-column justify-space-between">
                                                                                <input type="text" hidden name="id">
                                                                                <input type="text" hidden name="fecha_alta" id="fecha_alta-ne">
                                                                                <div class="form-group w-100 ">
                                                                                    <label for="numero">Número/s</label>
                                                                                    <input type="tel" class="form-control" name="numero" pattern="\d+ *(,? *\d+ *)*,?" value="" required>
                                                                                    <small>Format: 123456789, 922567897,
                                                                                        822456456</small>
                                                                                </div>
                                                                                <div class="form-group  d-flex flex-wrap">
                                                                                    <div class="form-group ">
                                                                                        <div class="d-flex justify-content-between">
                                                                                            <label for="id_tipo">Tipo</label>
                                                                                            <input type="checkbox" name="tipo-ck">
                                                                                        </div>
                                                                                        <select class="form-select " name="id_tipo" id="sel-id_tipo-ne">
                                                                                            <?php foreach ($admins['tipo'][0] as $f) {
                                                                                                if ($f['activo'])
                                                                                                    echo "<option value=$f[0]>$f[1]<option>";
                                                                                            }
                                                                                            ?> </select>
                                                                                    </div>
                                                                                    <div class="form-group mx-4">
                                                                                        <div class="d-flex justify-content-between">
                                                                                            <label for="id_operador">Operador</label>
                                                                                            <input type="checkbox" name="operador-ck">
                                                                                        </div>
                                                                                        <select class="form-select " name="id_operador" id="sel-id_operador-ne">
                                                                                            <?php foreach ($admins['operador'][0] as $f) {
                                                                                                if ($f['activo'])
                                                                                                    echo "<option value=$f[0]>$f[1]<option>";
                                                                                            }
                                                                                            ?> </select>
                                                                                    </div>
                                                                                    <div class="form-group ">
                                                                                        <div class="d-flex justify-content-between">
                                                                                            <label for="id_estado">Estado</label>
                                                                                            <input type="checkbox" name="estado-ck">
                                                                                        </div>
                                                                                        <select class="form-select" name="id_estado" id="sel-id_estado-ne">
                                                                                            <?php foreach ($admins['estado'][0] as $f) {
                                                                                                if ($f['estado'])
                                                                                                    echo "<option value=$f[0]>$f[1]<option>";
                                                                                            }
                                                                                            ?> </select>
                                                                                    </div>
                                                                                    <div class="form-group mx-4">
                                                                                        <div class="d-flex justify-content-between">
                                                                                            <label for="tipo_numero">Tipo de
                                                                                                número</label>
                                                                                            <input type="checkbox" name="tipo_numero-ck">
                                                                                        </div>
                                                                                        <select class="form-select " name="id_tipo_numero" id="sel-id_tipo_numero-ne">
                                                                                            <?php foreach ($admins['tipo_numero'][0] as $f) {
                                                                                                if ($f['activo'])
                                                                                                    echo "<option value=$f[0]>$f[1]<option>";
                                                                                            }
                                                                                            ?> </select>
                                                                                    </div>
                                                                                    <div class="form-group ml-4">
                                                                                        <div class="d-flex justify-content-between">
                                                                                            <label for="id_entrega">Entrega</label>
                                                                                            <input type="checkbox" name="entrega-ck">
                                                                                        </div>
                                                                                        <select class="form-select " name="id_entrega" id="sel-id_entrega-ne">
                                                                                            <?php foreach ($admins['entrega'][0] as $f) {
                                                                                                if ($f['activo'])
                                                                                                    echo "<option value=$f[0]>$f[1]<option>";
                                                                                            }
                                                                                            ?> </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div id="response-servidor-ne" class="form-group ">
                                                                                    <select hidden class="form-select" name="id_servidor" id="sel-id_servidor-ne" tabla="servidor">
                                                                                        <?php foreach ($admins['servidor'][0] as $f) {
                                                                                            if ($f['activo'])
                                                                                                echo "<option value=$f[0]>$f[1]<option>";
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group w-100 mt-3">
                                                                                    <label for="numero">Cliente&nbsp;actual</label>
                                                                                    <input type="text" class="form-control mt-2" name="cliente_actual" id="cliente_actual-ne">
                                                                                </div>
                                                                                <div class="form-group ">
                                                                                    <div class="d-flex justify-content-between">
                                                                                        <label for="numeros_desvios">Núm.
                                                                                            desvíos</label>
                                                                                        <input type="checkbox" name="tipo-ck">
                                                                                    </div>
                                                                                    <textarea class="form-control" name="numeros_desvios" id="numeros_desvios-ne" value=""></textarea>
                                                                                </div>
                                                                                <div class="form-group  ">
                                                                                    <div class="d-flex justify-content-between">
                                                                                        <label for="observaciones">Observaciones</label>
                                                                                        <input type="checkbox" name="tipo-ck">
                                                                                    </div>
                                                                                    <textarea type="text" class="form-control" name="observaciones" id="observaciones-ne" value=""></textarea>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer ">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                            <input type="submit" id="btn-modal-submit" class="btn btn-primary " name='graba' value="Grabar" />
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--Modal baja-->
                                                        <div class="modal" id="modal-baja" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog" role="document">
                                                                <div id="modalcssbaja" class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h4 class="modal-title text-secondary">Procedimiento
                                                                            de baja</h4>
                                                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <form method='post' enctype="multipart/form-data">
                                                                        <div class="modal-body">
                                                                            <input hidden name="id_baja" id="id-baja">
                                                                            <input hidden name="numero_baja" id="numero-baja">
                                                                            <h5>¿Seguro que quiere dar de baja el número/s
                                                                                <b id="numeroh-baja"></b>&nbsp;?
                                                                            </h5>
                                                                            <div class="form-group d-flex flex-wrap mt-5 mx-5 justify-content-between">
                                                                                <h5><b>Indique&nbsp;el&nbsp;motivo:</b></h5>
                                                                                <select class="form-select w-50 " name="id_motivo_baja" id="id-motivo-baja">
                                                                                    <?php
                                                                                    $res = carga("motivo_baja", '', "ORDER BY motivo_baja ASC");
                                                                                    cargaSel($res,  'motivo_baja'); ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button id="btn-hacer-baja" type="submit" name="baja" class="btn btn-danger">Baja</button>
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Modal info-->
                                                        <div class="modal fade " id="modal-info" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                                                            <div class="modal-dialog " role="document">
                                                                <div id="modalcssinfo" class="modal-content ">
                                                                    <div class="modal-header bg-info ">
                                                                        <h4 id="m-titulo" class="modal-title text-white mt-2 mb-0 p-0">+
                                                                            info</h4>
                                                                        <button type="button" class="" data-bs-dismiss="modal" aria-label="Cerrar">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="form-group ">
                                                                            <div class="form-group mb-4">
                                                                                <div class=' d-flex justify-content-between mx-4'>
                                                                                    <div class='form-group '>
                                                                                        <label for='info-fecha_alta'>Fecha
                                                                                            del alta</label>
                                                                                        <h5 readonly id='info-fecha_alta' class='form-control'></h5>
                                                                                    </div>
                                                                                    <div class='form-group  ml-5'>
                                                                                        <label for='info-fecha_ultimo_cambio'>Fecha
                                                                                            ult.cambio</label>
                                                                                        <h5 readonly id='info-fecha_ultimo_cambio' class='form-control'></h5>
                                                                                    </div>
                                                                                </div>
                                                                                <div class='form-group mx-4'>
                                                                                    <label for='info-cliente_actual'>Cliente
                                                                                        actual</label>
                                                                                    <h5 readonly id='info-cliente_actual' class='form-control'></h5>
                                                                                </div>
                                                                                <div class='form-group mx-4'>
                                                                                    <label for='info-numeros_desvios'>Números
                                                                                        de desvíos</label>
                                                                                    <textarea readonly id='info-numeros_desvios' class='form-control'></textarea>
                                                                                </div>
                                                                                <div class='form-group mx-4'>
                                                                                    <label for='info-entrega'>Entrega</label>
                                                                                    <h5 readonly id='info-id_entrega' class='form-control'></h5>
                                                                                </div>
                                                                                <div class='form-group mx-4'>
                                                                                    <label for='info-observaciones'>Observaciones</label>
                                                                                    <textarea readonly id='info-observaciones' class='form-control'></textarea>
                                                                                </div>
                                                                                <div class='form-group mx-4'>
                                                                                    <label for='info-motivo_baja'>Motivo de
                                                                                        baja</label>
                                                                                    <h5 readonly id='info-id_motivo_baja' class='form-control'></h5>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php
                                                    };
                                                        ?>