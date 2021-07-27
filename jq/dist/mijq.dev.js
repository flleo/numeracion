"use strict";

$(document).ready(function () {
  //Administracion///////////////////////////////////////////////////////////////////
  var v = vip = vhost = vdes = vsf = tabla = btn = btnVal = id = op = sel = selRutaIp = selRutaDes = html = accion = existe = ip = des = a = '';
  $('[class^=modal').modal('hide');
  $('form').attr('autocomplete', 'off');
  $('input').attr('autocomplete', 'off');
  $('#sel-id_servidor-ne').focusin(function () {
    sel_id_servidor_ne_checked = true;
  });
  $('#mostrar-desactivados-ck').focusout(function () {
    ops = $('option[class=admin]');

    if ($(this).is(':checked')) {
      for (i = 0; i < ops.length; i++) {
        s = $(ops[i]).attr('style');

        if (s != '') {
          $(ops[i]).show();
        }
      }
    } else {
      for (i = 0; i < ops.length; i++) {
        s = $(ops[i]).attr('style');

        if (s != '') {
          $(ops[i]).hide();
        }
      }
    }
  });
  ocultaDesactivados();

  function ocultaDesactivados() {
    ops = $('option[class=admin]');

    for (i = 0; i < ops.length; i++) {
      s = $(ops[i]).attr('style');

      if (s != '') {
        $(ops[i]).hide();
      }
    }

    $('#mostrar-desactivados-ck').prop('checked', false);
  }

  $('select[id$=admin]').click(function () {
    $(this).attr('style', '');
  }); //Asigna valor al input, y desbloquea botnes, viene de bindeaRecuperadosOptionAdmin()

  function bindeaRecuperadosInputAdmin() {
    inp = $('#' + tabla + '-admin').val(html); //Recuperamos la class de input

    clase = $(inp).attr('class'); //Le anadimos el color 

    $(inp).attr('style', style);
    estado = '';

    if (html != '') {
      desbloqueaBtns();
    }
  } //bindea los selects de admin con su input


  function bindeaRecuperadosOptionAdmin() {
    $('select[id$=admin]').change(function (event) {
      tabla = $(this).attr('tabla');
      id = $(this).val();
      style = $(this).children('[value=' + id + ']').attr('style');
      html = $(this).children(":selected").html();
      bindeaRecuperadosInputAdmin();

      if (tabla == 'servidor') {
        event.preventDefault();
        bindeaRecuperadosServidor('');
      }
    });
  } //Devuelve valores host, ip , descripcion por id 


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

          if (style == '') {
            html = '';
            ocultaDesactivados();
          } else {
            bindeaRecuperadosInputAdmin();
            html = ' (Desactivado)';
          }

          desbloqueaBtns();
          bindeaRecuperadosOptionAdmin();
        } else {
          $('#response-servidor-ne').html(data);

          if (a == 'ne-ck') {
            if (sel_id_servidor_ne_checked) {
              $('[name=servidor-ck').prop('checked', true);
            } else {
              $('[name=servidor-ck').prop('checked', false);
            }

            actDesSelNEConCount();
          }

          bindeaRecuperadosServidorNEOption(a);
        }
      }
    }); //  Hace que el modal siga abierto

    return false;
  } //Acciones Admin////////////////////////////////////////////


  cerrarMenClick(); //aL picar en input admin cerramos el mensaje

  function cerrarMenClick() {
    $('input[id$=-admin]').click(function () {
      $('#mensaje-admin').hide();
    });
  }

  function response() {
    bindeaRecuperadosOptionAdmin(); //Hacemos click en el select para que se bindeen

    if (btnVal == 'Añadir') id = $('#lastId').html();
    btn.attr('disabled', false);
    btn.val(btnVal);
    ocultaDesactivados(); //aL picar en input admin cerramos el mensaje

    cerrarMenClick();
  } //Obligamos a presionar cerrar en admin para que se recargue y los demas modales cojar el array $admins actualizado


  $('.btn-cerrar-admin').click(function () {
    $('[value=Filtrar]').click();
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
  } //Activa Desactiva Admnin


  function borrar() {
    $('#seguro-borrar').html('¿Seguro que quiere Act-Des o borrar el dato <b >' + v + '</b></param>&nbsp;?');
    $('#btn-borrar-dato-final').show();
    $('#img-error').hide(); // $('#modal-error-borrar-admin').show();

    $('#modal-error-borrar-admin').modal('show'); //ok borrar

    $('#btn-borrar-dato-final').click(function (e) {
      $('#modal-error-borrar-admin').modal('hide');
      e.preventDefault();
      envia();
    });
  }

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
            envia();
          } else {
            $('#seguro-borrar').html('El campo <b>' + v + '</b>, ya existe');
            error();
          }
        } else {
          envia();
        }

        break;
    }
  } ////////Comienzo de la ejecucion de acciones Administrador/////////////////////////////////


  $("[class*=btn-action-admin]").click(function (e) {
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

    e.preventDefault();
    btnAction();
  });
  tablas = ['tipo', 'operador', 'estado', 'tipo_numero', 'entrega', 'servidor', 'motivo_baja']; //Desbloquea los botones Actualizar y Borrar de admin

  function desbloqueaBtns() {
    if (html.substring(html.length - 14, html.length) == ' (Desactivado)') {
      $('[class*=btn-warning-' + tabla + ']').prop('disabled', true);
      $('[class*=btn-danger-' + tabla + ']').prop('disabled', false);
    } else {
      $('[class$=' + tabla + ']').prop('disabled', false);
    }

    $('[class$=' + tabla + ']').next().attr('title', '');
  } //Click admin


  $('#btn-admin').focusin(function () {
    bindeaRecuperadosOptionAdmin();
    $('#modal-admin').modal('show');
    modalSinfondo();
  }); ///NUEVO EDITAR BAJA/////////////////////////////////////////////////////////////////

  sel_id_servidor_ne_checked = false; //cambia valor del select servidor en nuevo/editar

  function bindeaRecuperadosServidorNEOption(a) {
    $('#sel-id_servidor-ne').change(function (event) {
      id = $(this).val();
      html = $(this).children(":selected").html();
      tabla = 'servidor';
      event.preventDefault();
      bindeaRecuperadosServidor(a);
    });
  } //Nuevo registro///////////////////////////////////////////////
  //Reseteo de campos


  $('.btn-nuevo').click(function () {
    eliminaVacios('[id$=ne]');
    eliminaVacios('[id$=ne]');
    bindeaRecuperadosServidorNEOption('ne');
    $('select[id$=ne]').change();
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
    $('#modal-en').modal('show');
    modalSinfondo();
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
    modalSinfondo();
  }); //Modal sin fondo oscuro para moviles

  function modalSinfondo() {
    if (window.matchMedia('(max-width: 768px)').matches) {
      $('[class^=modal]').removeClass("modal-backdrop");
    }
  } // cuando enter input filter


  $('input[class$=filter]').on('keydown', function (e) {
    if (e.which == 13) {
      $('input[value=Filtrar]').click();
      return false;
    }
  }); //Editar
  //Asignamos valor del registro a los selects

  $('.btn-editar').focusin(function () {
    //indice para indicar a consultas.php de que modal se trata
    //Indice del registro del listado
    re = $(this).attr('re'); //Campos de los inputs del listado

    cil = ['id', 'numero', 'fecha_alta', 'cliente_actual', 'numeros_desvios', 'observaciones']; //Le devolvemos los name

    $('label+[type=checkbox]').prop('checked', 'true'); //Asignamos valor del registro a los  inputs

    cil.forEach(function (c) {
      v = $('#' + c + re).html();
      $('[name=' + c + ']').val(v);
      $('[name=' + c + ']').prop('disabled', false);
    });
    eliminaVacios('[id$=ne]');
    eliminaVacios('[id$=ne]'); //cambia valor del select servidor en nuevo/editar

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
    $('#modal-en').modal('show');
    modalSinfondo();
  }); //Acciones conjuntas///////////////////////////////////////////////////////////////////////

  function eliminaVacios(sel) {
    b = $(sel + ' > option');

    for (i = 0; i < b.length; i++) {
      if ($(b[i]).val() == '') {
        $(b[i]).remove();
      }
    }
  } //Reasignar/*///////////////////////////////////////////


  actDesSelNEConCount(); //Boton Reasignar  del modalmodalmodalmodal

  $('#btn-reasignar-modal').click(function () {
    if (ncks > 0) {
      $('#titulo-error-borrar-admin').html('Reasignación de campos: Editar');
      $('#img-error').hide();
      $('#seguro-borrar').html('¿Seguro que quiere cambiar este tipo/s en toda la tabla numeración?');
      $('[name=borrar_dato_admin]').html('Sí');
      $('[name=borrar_dato_admin]').show();
    } else {
      $('#titulo-error-borrar-admin').html('Reasignación de campos: Editar');
      $('#img-error').show();
      $('#seguro-borrar').html('Debe seleccionar un campo original para reasignar');
      $('[name=borrar_dato_admin]').hide();
    }

    $('#modal-error-borrar-admin').modal('show');
    modalSinfondo();
    $('[name=borrar_dato_admin]').click(function () {
      $('#form-reasignar').submit();
    });
    return false;
  }); //Obligamos a presionar cerrar en reasignar para que se recargue y se recojan los filtros den uevo

  $('[class$=cerrar-reasignar').click(function () {
    $('[value=Filtrar]').click();
  });

  function cargaSelFilEnSelRea() {
    for (i = 0; i < tablas.length; i++) {
      ops = $('[name=id_' + tablas[i] + 's]').children('option');
      delete ops[0];
      /*quitamos Todos del objeto */

      ops = Object.values(ops);
      /*lo convertiomos en array */

      $('[name=sel-ori-' + tablas[i] + ']').append(ops);
    }
  } //Boton de reasignar en numeracion


  $('.btn-reasignar-con').click(function () {
    // $('[name=borrar_dato_admin]').attr('name', 'reasignar_confirmado')
    //apagamos checks del listado para evitar confusiones
    $('th>[type=checkbox]').prop('checked', false);
    $('td>[type=checkbox]').prop('checked', false); // eliminaVacios('[name^=sel-ori-]')

    cargaSelFilEnSelRea();
    eliminaVacios('[name^=sel-rea-adm-]');
    $('label+[type=checkbox]').show();
    $('[name^=sel-ori-').prop('disabled', true);
    $('#modal-reasignar').modal('show');
    modalSinfondo();
  }); //checkbox del listado

  ids = [];
  ns = [];
  cas = [];
  $('td>[type=checkbox]').click(function () {
    re = $(this).parent().attr('re');
    id = $('#id' + re).html();
    n = $('#numero' + re).html();
    ca = $('#cliente_actual' + re).html();

    if ($(this)[0].checked === true) {
      ids.push(id);
      ns.push(n);
      cas.push(ca);
    } else {
      saca(ids, id);
      saca(ns, n);
      saca(cas, ca);
    }
  });
  $('[name=allcheck]').click(function () {
    $('td>[type=checkbox]').click();
  });
  $('#btn-historial-con').focusin(function () {
    $('[name=numeros]').val(ns.toString());
  });
  $('.btn-editar-con').click(function () {
    $('[name$=-ck]').prop('checked', false);
    eliminaVacios('[id$=ne]');
    eliminaVacios('[id$=ne]');
    a = 'ne-ck';
    bindeaRecuperadosServidorNEOption('ne-ck');
    $('#sel-id_servidor-ne').change(); //Activamos para editar-con los selects clickeados y sus names

    $('label+[type=checkbox]').show();
    $('[name=id]').val(ids);
    $('[name=numero]').val(ns);
    $('[name=cliente_actual]').val(cas);
    $('#btn-modal-submit').val('Actualizar');
    $('#btn-modal-submit').attr('name', 'actualiza_con');
    $('#m-titulo').html('Actualizar registro');
    $('[name=numero]').prop('readonly', true);
    $('[id$=-ne]').prop('disabled', true);
    $('#modal-en').modal('show');
    modalSinfondo();
  });
  $('[name=btn_baja_con]').focusin(function () {
    idsi = ids.join(',');
    nsi = ns.join(',');
    $('[name=id_baja]').val(idsi);
    num = $('[name=numero_baja]');
    $(num).val(nsi);
    $('#numeroh-baja').html(nsi);
  }); //act/desactivamos select editar y con  y cuenta checks de select

  function actDesSelNEConCount() {
    ncks = 0;
    $('[name$=-ck]').click(function () {
      if ($(this)[0].checked === true) {
        ncks++;
        sel = $(this).parent().next();
        $(sel).prop('disabled', false);
      } else {
        sel = $(this).parent().next();
        $(sel).prop('disabled', true);
        $(sel).attr('name', '');
        ncks--;
      }
    });
  } //saca un elemento de un array


  function saca(a, e) {
    for (i = 0; i < a.length; i++) {
      if (a[i] == e) a.splice(i, 1);
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
    modalSinfondo();
  }); //ides de la tabla listado 

  var campos = ['id_tipo', 'id_operador', 'id_estado', 'id_tipo_numero', 'id_servidor', 'id_entrega', 'id_motivo_baja']; //Boton + filtros        

  $('.btn-filtros').click(function () {
    //Abrir cerrar desplegable de filtros
    titulo = $('#titulo').html();
    if (titulo == 'numeración') tope = 15;else tope = 16;
    size = $('#filtros').children(':visible').length;
    $(".mas-filtros").toggle(function () {
      $(this).attr('hidden', false);

      if (size < tope) {
        $('.btn-filtros').val('- Filtros');
      } else $('.btn-filtros').val('+ Filtros');
    }); //
  }); //Quitar filtros focusin

  $('#btn-quitar-filtros').focusin(function () {
    $('input[class$=filter').val('');
    $('[id*=select-filtro-]').val('Todos');
    $(this).attr('name', 'quitar_filtros');
  }); //Volver

  $('.btn-volver').click(function () {
    $('select').val('Todos');
    $('[type=date]').val('');
  }); //Centramos tabla

  $('td').attr('class', 'text-center');
  $('th').attr('class', 'text-center');
});