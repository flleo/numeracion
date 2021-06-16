"use strict";

$(document).ready(function () {
  //Administracion///////////////////////////////////////////////////////////////////
  var v = vip = vhost = vdes = vsf = tabla = btn = btnVal = id = op = sel = selRutaIp = selRutaDes = accion = existe = ip = des = a = '';
  $('[class^=modal').modal('hide');
  $('form').attr('autocomplete', 'off');
  $('input').attr('autocomplete', 'off'); //Desbloquea los botones Actualizar y Borrar de admin

  function desbloqueaBtns(input) {
    $('[class$=' + tabla + ']').prop('disabled', false);
    $('[class$=' + tabla + ']').next().attr('title', '');
  } //bindeaRecuperados el input con el click del option, viene de bindeaRecuperadosOption()


  function bindeaRecuperadosInput() {
    input = $('#' + tabla + '-admin').val(html);
    v = $(input).val();

    if (v != '') {
      desbloqueaBtns(input);
    }
  }

  function bindeaRecuperadosServidor(a) {
    url = "consultas.php?a=" + a + "&id=" + id + "&t=" + tabla;
    $.ajax({
      url: url,
      method: "POST",
      contentType: false,
      cache: false,
      processData: false,
      success: function success(data) {
        if (a == '') {
          $('#response-' + tabla).html(data);
          desbloqueaBtns();
          bindeaRecuperadosOptionAdmin();
        } else {
          $('#response-servidor-ne').html(data);

          if (a == 'ne-ck') {
            $('#sel-id_servidor-ne').prop('disabled', true);
            actDesSelNECon();
          }

          bindeaRecuperadosServidorNEOption(a);
        }
      }
    }); //  Hace que el modal siga abierto

    return false;
  } //bindea los selects de admin con su input


  function bindeaRecuperadosOptionAdmin() {
    $('select').change(function () {
      tabla = $(this).attr('tabla');
      id = $(this).val();
      html = $(this).children(":selected").html();

      if (tabla == 'servidor') {
        bindeaRecuperadosServidor('');
      } else {
        bindeaRecuperadosInput();
      }
    });
  } //Acciones Admin////////////////////////////////////////////
  //Selects de Admin


  var selsadm = ['#select-tipo-admin', '#select-operador-admin', '#select-estado-admin', '#select-tipo_numero-admin', '#select-servidor-admin', '#select-entrega-admin', '#select-ruta-admin', '#select-motivo_baja-admin'];
  cerrarMenClick(); //aL picar en input admin cerramos el mensaje

  function cerrarMenClick() {
    $('input[id$=-admin]').click(function () {
      $('#mensaje-admin').hide();
    });
  }

  $('#btn-admin').click(function () {
    bindeaSelsAdmin();
    bindeaRecuperadosOptionAdmin();
  });

  function response() {
    bindeaRecuperadosOptionAdmin(); //Hacemos click en el select para que se bindeen

    if (btnVal == 'Añadir') id = $('#lastId').html();
    btn.attr('disabled', false);
    btn.val(btnVal); //Devuelve el valor original al boton
    //aL picar en input admin cerramos el mensaje

    cerrarMenClick();
  } //Para que se cierre al hacer click fuera, obliga a presionar cerrar


  $('#modal').modal({
    backdrop: 'static',
    keyboard: true
  });

  function envia() {
    url = "consultas.php?a=" + btnVal + "&t=" + tabla + "&v=" + v + "&id=" + id + "&vhost=" + vhost + "&vip=" + vip + "&vdes=" + vdes;
    $.ajax({
      url: url,
      type: "POST",
      data: {
        vsf: vsf
      },
      beforeSend: function beforeSend() {
        btn.val('Ejecutando...');
        btn.attr('disabled', 'disabled');
      },
      success: function success(data) {
        $('#response-' + tabla).html(data);
        response();
      },
      error: function error() {
        response();
      }
    }); //  Hace que el modal siga abierto

    return false;
  } //valores option select-filtro de la tabla


  function filtroT() {
    vsf = [];
    ops = $('#select-filtro-id_' + tabla + 's option');

    for (i = 1; i < ops.length; i++) {
      vs = $(ops[i]).val();
      vsf.push(vs);
    }

    return vsf;
  }

  function borrar() {
    $('#seguro-borrar').html('¿Seguro que quiere Act-Des o borrar el dato <b >' + v + '</b></param>&nbsp;?');
    $('#btn-borrar-dato-final').show();
    $('#img-error').hide(); // $('#modal-error-borrar-admin').show();

    $('#modal-error-borrar-admin').modal('show'); //ok borrar

    $('#btn-borrar-dato-final').click(function () {
      $('#modal-error-borrar-admin').modal('hide');
      fsv = filtroT();
      if (vsf.length > 0) envia();
    });
  }
  /*//Enter para el input accionara el boton Anadir
   $('input[id$=-admin]').keypress(function(e) {
       if (e.which == 13) {
           $(this).parents().children('input').click();
       }
   });
    */


  function error() {
    $('#btn-borrar-dato-final').hide();
    $('#modal-error-borrar-admin').modal('show');
    $('#img-error').show();
  }
  /**
   * Refente: al modal Administración
   * funcion: calcula si el valor del input conicide con los selects de su tabla
   * v : valor del input
   * h : html del option
   * return: enviar a Anadir, actualizar o borrar campos
   */


  function siExiste() {
    if (tabla == 'servidor') v = vhost;

    if (v != '' && v != undefined) {
      vacio = existe = false;
      $('#select-' + tabla + '-admin > option').each(function () {
        h = $(this).html();

        if (h == v) {
          existe = true;
        }
      });
    } else {
      vacio = true;
    }
  }

  $('#modal-error-borrar-admin').modal({
    backdrop: false,
    keyboard: false
  });

  function btnAction() {
    siExiste();

    switch (btnVal) {
      case 'Act-Des':
        if (existe) {
          borrar();
        } else {
          $('#seguro-borrar').html('El campo <b>' + v + '</b>, no existe');
          error();
        }

        break;

      case 'Añadir':
        if (vacio) {
          $('#seguro-borrar').html('El campo no puede ser nulo ');
          error();
        } else if (!existe) {
          envia();
        } else {
          $('#seguro-borrar').html('El campo <b>' + v + '</b>, ya existe');
          error();
        }

        break;

      case 'Actualizar':
        if (tabla != 'servidor') {
          if (!existe) {
            filtroT();
            envia();
          } else {
            $('#seguro-borrar').html('El campo <b>' + v + '</b>, ya existe');
            error();
          }
        } else {
          filtroT();
          envia();
        }

        break;
    }
  } ////////Comienzo de la ejecucion de acciones Administrador/////////////////////////////////


  $("[class*=btn-action-admin]").click(function (b) {
    btn = $(this);
    btnVal = $(this).val();
    c = $(btn).attr('class');
    ca = c.split(' ');
    tabla = ca[ca.length - 1];
    v = $('#' + tabla + '-admin').val();

    if (tabla == 'servidor') {
      vhost = v;
      vip = $('#ip-admin').val();
      vdes = $('#descripcion-admin').val();
    }

    btnAction();
  }); ///NUEVO EDITAR BAJA/////////////////////////////////////////////////////////////////
  //selects modal nuevo/editar

  var selsne = ['#sel-id_tipo-ne', '#sel-id_operador-ne', '#sel-id_estado-ne', '#sel-id_tipo_numero-ne', '#sel-id_servidor-ne', '#sel-id_entrega-ne']; //cambia valor del select servidor en nuevo/editar

  function bindeaRecuperadosServidorNEOption(a) {
    $('#sel-id_servidor-ne').change(function () {
      id = $(this).val();
      html = $(this).children(":selected").html();
      tabla = 'servidor';
      bindeaRecuperadosServidor(a);
    });
  } //bindeaRecuperados select para modal #modal nuevo/editar dessde los selects admin


  function bindeaRecuperadosSelsNEAdm() {
    for (i = 0; i < selsadm.length; i++) {
      $(selsne[i]).html('');
      opp = $(selsadm[i]).children('option');

      for (j = 0; j < opp.length; j++) {
        c = $(opp[j]).attr('class');
        /*Si option no tiene text-danger */

        if (c == '') {
          $(selsne[i]).append(opp[j]);
        }
      }
    }
  } //Nuevo registro///////////////////////////////////////////////
  //Reseteo de campos


  $('.btn-nuevo').click(function () {
    bindeaSelsAdmin();
    bindeaRecuperadosSelsNEAdm();
    bindeaRecuperadosServidorNEOption('ne');
    $('select').change();
    $('#sel-id_servidor-ne > option:first').click();
    $('#numero-ne').val('');
    $('#numero-ne').prop('readonly', false);
    $('#cliente_actual-ne').val('');
    $('#numeros_desvios-ne').val('');
    $('#observaciones-ne').val('');
    $('#btn-modal-submit').val('Grabar');
    $('#btn-modal-submit').attr('name', 'graba');
    $('#m-titulo').html('Nuevo registro');
    $('label+[type=checkbox]').hide();
    $('#modal').modal('show');
  }); //Botones de las rows del listado//////////////////////////////////////////
  //Baja

  $('.btn-baja').focusin(function () {
    re = $(this).attr('re');
    id = $('#id' + re).html();
    numero = $('#numero' + re).html();
    $('#id-baja').val(id);
    $('#numero-baja').val(numero);
    $('#numeroh-baja').html(numero);
    $('#modal-baja').modal('show');
  }); // cuando enter                

  /*  $('input[class$=filter]').keypress(function(e) {
        if (e.which == 13) {
            $('input[value=Filtrar]').click();
        }
    })*/
  //Editar
  //Asignamos valor del registro a los selects

  $('.btn-editar').focusin(function () {
    bindeaSelsAdmin(); //indice para indicar a consultas.php de que modal se trata

    a = 'ne'; //Indice del registro del listado

    re = $(this).attr('re'); //Campos de los inputs del listado

    cil = ['id', 'numero', 'fecha_alta', 'cliente_actual', 'numeros_desvios', 'observaciones']; //Le devolvemos los name

    $('label+[type=checkbox]').prop('checked', 'true'); //Asignamos valor del registro a los  inputs

    cil.forEach(function (c) {
      v = $('#' + c + re).html();
      $('[name=' + c + ']').val(v);
      $('[name=' + c + ']').prop('disabled', false);
    }); //Recogemos los selects desde los de admin (todos los existentes y activos)

    bindeaRecuperadosSelsNEAdm(); //cambia valor del select servidor en nuevo/editar

    bindeaRecuperadosServidorNEOption('ne'); //Asignamos valor select

    campos.forEach(function (c) {
      v = $('#' + c + re).attr('value');
      $('#sel-' + c + '-ne').val(v);
      $('#sel-' + c + '-ne').change();
      $('#sel-' + c + '-ne').prop('disabled', false);
    });
    $('#btn-modal-submit').val('Actualizar');
    $('#btn-modal-submit').attr('name', 'actualiza');
    $('#m-titulo').html('Actualizar registro');
    $('[name=numero]').prop('readonly', true);
    $('#ip-admin').prop('disabled', true);
    $('#descripcion-admin').prop('disabled', true);
    $('label+[type=checkbox]').hide();
    $('#modal').modal('show');
  }); //Acciones conjuntas///////////////////////////////////////////////////////////////////////

  function eliminaVacios(sel) {
    b = $(sel + ' > option');

    for (i = 0; i < b.length; i++) {
      if ($(b[i]).val() == '') {
        $(b[i]).remove();
      }
    }
  } //Reasignar/*///////////////////////////////////////////
  //Boton Reasignar  del modal


  $('#btn-rea').click(function () {
    if (ncks > 0) {
      $('#titulo-error-borrar-admin').html('Reasignación de campos: Editar');
      $('#img-error').hide();
      $('#seguro-borrar').html('¿Seguro que quiere cambiar este tipo/s en toda la tabla numeración?');
      $('#btn-reasigna').html('Sí');
      $('#btn-reasigna').show();
    } else {
      $('#titulo-error-borrar-admin').html('Reasignación de campos: Editar');
      $('#img-error').show();
      $('#seguro-borrar').html('Debe seleccionar un campo original para reasignar');
      $('#btn-reasigna').hide();
    }

    $('#modal-error-borrar-admin').modal('show');
  }); //Boton de reasignar en numeracion

  $('.btn-reasignar-con').click(function () {
    //apagamos checks del listado para evitar confusiones
    $('th>[type=checkbox]').prop('checked', false);
    $('td>[type=checkbox]').prop('checked', false);
    eliminaVacios('[name^=sel-ori-]');
    eliminaVacios('[name^=sel-rea-adm-]');
    $('label+[type=checkbox]').show();
    $('[name^=sel-ori-').prop('disabled', true);
    actDesSelNECon();
    $('#modal-reasignar').modal('show');
    $('#btn-borrar-dato-final').attr('id', 'btn-reasigna'); //Btn Sí, seguro reaasignar

    $('#btn-reasigna').click(function () {
      $('#form-rea').submit();
      $('#btn-reasigna').attr('id', 'btn-borrar-dato-final');
    });
  });
  $('[name=allcheck]').click(function () {
    if ($(this).prop('checked') === true) {
      $('td>[type=checkbox]').prop('checked', true);
    } else $('td>[type=checkbox]').prop('checked', false);
  });
  $('#historial-con').focusin(function () {
    $('[name=numeros_con]').val(ns);
  });
  $('.btn-editar-con').click(function () {
    bindeaSelsAdmin();
    a = 'ne-ck';
    ids = [];
    ns = [];
    $('[name$=-ck]').prop('checked', false); //Recuperamos ids y numeros checked del listado

    cks = $('td > [type=checkbox]');

    for (i = 0; i < cks.length; i++) {
      if ($(cks)[i].checked === true) {
        id = $(cks[i]).parent().next().html();
        n = $(cks[i]).parent().next().next().html();
        ids.push(id);
        ns.push(n);
      }
    } //


    bindeaRecuperadosSelsNEAdm();
    bindeaRecuperadosServidorNEOption('ne-ck');
    $('#sel-id_servidor-ne').change(); //Activamos para editar-con los selects clickeados y sus names

    $('label+[type=checkbox]').show();
    $('[name=id]').val(ids);
    $('[name=numero]').val(ns);
    $('#btn-modal-submit').val('Actualizar');
    $('#btn-modal-submit').attr('name', 'actualiza_con');
    $('#m-titulo').html('Actualizar registro');
    $('[name=numero]').prop('readonly', true);
    $('[id$=-ne]').prop('disabled', true);
    $('#modal').modal('show');
  });
  $('[name=btn_baja_con]').focusin(function () {
    idsi = ids.join(',');
    nsi = ns.join(',');
    $('[name=id_baja]').val(idsi);
    num = $('[name=numero_baja]');
    $(num).val(nsi);
    $('#numeroh-baja').html(nsi); //    $(num).next().html(num)
  }); //act/desactivamos select editar con  

  function actDesSelNECon() {
    name = '';
    ncks = 0;
    $('[name$=-ck]').click(function () {
      if ($(this)[0].checked === true) {
        ncks++;
        sel = $(this).parent().next();
        $(sel).prop('disabled', false);
      } else {
        sel = $(this).parent().next();
        $(sel).prop('disabled', true);
        ncks--;
      }
    });
  } //checkbox del listado


  ids = [];
  ns = [];
  $('td>[type=checkbox]').click(function () {
    tdid = $(this).parent().next();
    id = $(tdid).html();
    num = $(tdid).next().html();

    if ($(this)[0].checked === true) {
      ids.push(id);
      ns.push(num);
    } else {
      ids.splice(id);
      ns.splice(num);
    }
  });
  tablas = ['tipo', 'operador', 'estado', 'tipo_numero', 'entrega', 'servidor', 'motivo_baja'];

  function bindeaSelsAdmin() {
    tablas.forEach(tablasf);

    function tablasf(t) {
      $('#select-' + t + '-admin').html($('#select' + t).html());
    }
  } //bindeaRecuperadosmos hacia el modal info////////////////////////////////////                


  $('.btn_info').focusin(function () {
    re = $(this).attr('re');
    c = ['fecha_alta', 'id_entrega', 'fecha_ultimo_cambio', 'cliente_actual', 'numeros_desvios', 'observaciones', 'id_motivo_baja'];
    c.forEach(function (c) {
      v = $('#' + c + re).html();
      $('#info-' + c).html(v);
      $('#info-' + c).val(v);

      if (c == 'id_motivo_baja') {
        if (v == undefined || v == '') $('#info-id_motivo_baja').parent().hide();else {
          $('#info-id_motivo_baja').parent().show();
        }
      }
    });
    $('#modal-info').modal('show');
  }); //Selects de los filtros

  var selsfil = ['#select-filtro-id_tipos', '#select-filtro-id_operadors', '#select-filtro-id_estados', '#select-filtro-id_tipo_numeros', '#select-filtro-id_servidors', '#select-filtro-id_entregas', '#select-filtro-id_motivo_bajas']; //ides de la tabla listado 

  var campos = ['id_tipo', 'id_operador', 'id_estado', 'id_tipo_numero', 'id_servidor', 'id_entrega', 'id_motivo_baja']; //Boton filtros        

  $('.btn-filtros').click(function () {
    titulo = $('#titulo').html();
    if (titulo == 'numeración') tope = 15;else tope = 16;
    size = $('#filtros').children(':visible').length;
    $(".mas-filtros").toggle(function () {
      $(this).attr('hidden', false);

      if (size < tope) {
        $('.btn-filtros').val('- Filtros');
      } else $('.btn-filtros').val('+ Filtros');
    });
  }); //Qitar filtros

  $('[name=quitar_filtros]').click(function () {
    $('input[class$=filter').val('');
    $('[id*=select-filtro-]').val('Todos');
  }); //Volver

  $('.btn-volver').click(function () {
    $('select').val('Todos');
    $('[type=date]').val('');
  }); //Centramos tabla

  $('td').attr('class', 'text-center');
  $('th').attr('class', 'text-center');
  $('.btn-cerrar-admin').click(function () {
    location.reload();
  });
});