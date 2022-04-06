<?php
session_start();
include_once "../conexion.php";
//print_r($_POST); exit;

if (!empty($_POST)) {

  // Extraer datos del Producto
  if ($_POST['action'] == 'infoProducto') {
    $producto_id = $_POST['producto']; // ['producto'] es la variable que esta en el archivo functions.js

    $query = mysqli_query($conection, "SELECT codproducto, descripcion, precio, existencia FROM producto
                                       WHERE codproducto = $producto_id
                                       AND estatus = 1");
    mysqli_close($conection);

    $result = mysqli_num_rows($query);
    if ($result > 0) {
      $data = mysqli_fetch_assoc($query); // assoc indica que asocia el arreglo
      echo json_encode($data,JSON_UNESCAPED_UNICODE);
      // json_encode - Retorna la representación (codifica a formato JSON) JSON del valor dado.
      // JSON_UNESCAPED_UNICODE - es para ignorar los carateres especiales
      exit;
    }
    echo "error";
    exit;
  }

  // Agregar productos a la tabla entrada
  if ($_POST['action'] == 'addProduct') {
    if (!empty($_POST['cantidad']) || !empty($_POST['precio']) || !empty($_POST['producto_id'])) {

      $cantidad    = $_POST['cantidad'];
      $precio      = $_POST['precio'];
      $producto_id = $_POST['producto_id'];
      $usuario_id = $_SESSION['idUser'];

      $query_insert = mysqli_query($conection,"INSERT INTO entradas(codproducto, cantidad, precio, usuario_id)
                                              VALUES ('$producto_id','$cantidad','$precio','$usuario_id')");

      if ($query_insert) {
        // Ejecutamos el procedimiento almacenado de la base de datos
        $query_upd = mysqli_query($conection, "CALL actualizar_precio_producto($cantidad, $precio, $producto_id)");
        $result_procedimiento = mysqli_num_rows($query_upd);
        if ($result_procedimiento > 0) {
          $data = mysqli_fetch_assoc($query_upd);
          // recuperamos el id del producto posicionandonos en el arreglo $data indicando con el parametro producto_id para setear otro valor en el JSON y debuelva 3 datos (el id, la nueva cantidad y el nuevo precio).
          $data['producto_id'] = $producto_id;

          echo json_encode($data, JSON_UNESCAPED_UNICODE);
          exit;
        }
      } else {
        echo 'error';
      }
      mysqli_close($conection);
    } else {
      echo 'error';
    }
    exit;
  }

  // Eliminar del Producto
  if ($_POST['action'] == 'delProduct') {

    if (empty($_POST['producto_id']) || !is_numeric($_POST['producto_id'])) {
      echo "erro";
    } else {
      $producto_id = $_POST['producto_id'];

      $query_delete = mysqli_query($conection, "UPDATE producto SET estatus = 0 WHERE codproducto = $producto_id");
      mysqli_close($conection);

      $result = mysqli_num_rows($query);
      if ($query_delete) {
        echo "EL producto se elimino correctamente";
      } else {
        echo "Error al eliminar";
      }

    }
    echo "Error";
    exit;
  }

// ----------------------------------------------------------------------------------------------------------
// ---Block IMPORTANTE PARA CAPTURAR LOS DATOS DEL (CLIENTE) SIN ACTUALIZAR LA PAGINA usando JQuery y JS-----
// ----------------------------------------------------------------------------------------------------------
//  Buscar Cliente
  if ($_POST['action'] == 'searchCliente') {
    if (!empty($_POST['cliente'])) {
      $doc = $_POST['cliente'];

      $query = mysqli_query($conection,"SELECT * FROM cliente WHERE doc LIKE '$doc' AND estatus = 1");
      mysqli_close($conection);

      $result = mysqli_num_rows($query);

      $data = '';
      if ($result > 0) {
        $data = mysqli_fetch_assoc($query);
      } else {
        $data = 0;
      }
      echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    exit;
  }
// ----------------------------------------------------------------------------------------------------------
// ---------------- FIN Block (CLIENTE) SIN ACTUALIZAR LA PAGINA usando JQuery y JS--------------------------
// ----------------------------------------------------------------------------------------------------------

//Registrar cliente desde el formulario ventas
  if ($_POST['action'] == 'addCliente') {

    $doc        = $_POST['doc_cliente'];
    $nombre     = $_POST['nom_cliente'];
    $telefono   = $_POST['tel_cliente'];
    $direccion  = $_POST['dir_cliente'];
    $usuario_id = $_SESSION['idUser'];

    $query_insert = mysqli_query($conection,"INSERT INTO cliente(doc,nombre,telefono,direccion,usuario_id)
                                            VALUES('$doc','$nombre','$telefono','$direccion','$usuario_id')");

    if ($query_insert) {
      $codCliente =  mysqli_insert_id($conection);
      $msg = $codCliente;
    } else {
      $msg = 'error';
    }
    mysqli_close($conection);
    echo $msg;
    exit;
  }

// Agregar productos al detalle temporal en la tabla de (detalle_temp) de la base de datos
  if ($_POST['action'] == 'addProductoDetalle'){
    //print_r($_POST); exit;

    if (empty($_POST['producto']) || empty($_POST['cantidad'])) {
      echo "error";
    } else {
      $codproducto = $_POST['producto'];
      $cantidad    = $_POST['cantidad'];
      $tokken      = md5($_SESSION['idUser']);

      $query_iva = mysqli_query($conection,"SELECT iva FROM configuracion");
      $result_iva = mysqli_num_rows($query_iva);

      $query_detalle_temp = mysqli_query($conection,"CALL add_detalle_temp($codproducto,$cantidad ,'$tokken')");
      $result = mysqli_num_rows($query_detalle_temp);

      $detalleTabla = '';
      $sub_total    = 0;
      $iva          = 0;
      $total        = 0;
      $arrayData    = array();

      if ($result > 0) {
        if ($result_iva > 0) {
          $info_iva = mysqli_fetch_assoc($query_iva);
          $iva      = $info_iva['iva'];
        }

        while ($data = mysqli_fetch_assoc($query_detalle_temp)) {
          $precioTotal = round( $data['cantidad'] * $data['precio_venta'], 2);
          $sub_total   = round( $sub_total + $precioTotal, 2);
          $total       = round( $total + $precioTotal, 2);

        // Mostramos el producto que se va a vender
          $detalleTabla .= '
            <tr>
              <td>'.$data['codproducto'].'</td>
              <td colspan="2">'.$data['descripcion'].'</td>
              <td class="textcenter">'.$data['cantidad'].'</td>
              <td class="textright">'.$data['precio_venta'].'</td>
              <td class="textright">'.$precioTotal.'</td>
              <td class="">
              <a href="#" class="link_delete" onclick="event.preventDefault();
              del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
              </td>
            </tr>
          ';
        }

        $impuesto = round( $sub_total * ($iva / 100), 2 );
        $tl_sniva = round( $sub_total - $impuesto, 2 );
        $total    = round( $tl_sniva + $impuesto, 2 );
        // Mostramos los totales que tendra la factura
        $detalleTotales = '
          <tr>
            <td colspan="5" class="textright">Sub Total</td>
            <td class="textright">'.$tl_sniva.'</td>
          </tr>
          <tr>
            <td colspan="5" class="textright">IVA ('.$iva.')</td>
            <td class="textright">'.$impuesto.'</td>
          </tr>
          <tr>
            <td colspan="5" class="textright">Total</td>
            <td class="textright">'.$total.'</td>
          </tr>
        ';

        $arrayData['detalle'] = $detalleTabla;
        $arrayData['totales'] = $detalleTotales;

        echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
      } else {
        echo "error";
      }
      mysqli_close($conection);

    }
    exit;
  }

//Extrae datos del detalle de la tabla detalle_temp
  if ($_POST['action'] == 'serchForDetalle'){
    //print_r($_POST); exit;

    if (empty($_POST['user'])) {
      echo "error";
    } else {
      $token = md5($_SESSION['idUser']);

      $query = mysqli_query($conection,"SELECT tmp.correlativo, tmp.token_user, tmp.cantidad, tmp.precio_venta,
                                               p.codproducto, p.descripcion
                                               FROM detalle_temp tmp INNER JOIN producto p
                                               ON tmp.codproducto = p.codproducto
                                               WHERE token_user = '$token' ");

      $result = mysqli_num_rows($query);

      $query_iva = mysqli_query($conection,"SELECT iva FROM configuracion");
      $result_iva = mysqli_num_rows($query_iva);

      $detalleTabla = '';
      $sub_total    = 0;
      $iva          = 0;
      $total        = 0;
      $arrayData    = array();

      if ($result > 0) {
        if ($result_iva > 0) {
          $info_iva = mysqli_fetch_assoc($query_iva);
          $iva      = $info_iva['iva'];
        }

        while ($data = mysqli_fetch_assoc($query)) {
          $precioTotal = round( $data['cantidad'] * $data['precio_venta'], 2);
          $sub_total   = round( $sub_total + $precioTotal, 2);
          $total       = round( $total + $precioTotal, 2);

          // Mostramos el producto que se va a vender concatenando el HTML
          $detalleTabla .= '
          <tr>
          <td>'.$data['codproducto'].'</td>
          <td colspan="2">'.$data['descripcion'].'</td>
          <td class="textcenter">'.$data['cantidad'].'</td>
          <td class="textright">'.$data['precio_venta'].'</td>
          <td class="textright">'.$precioTotal.'</td>
          <td class="">
          <a href="#" class="link_delete" onclick="event.preventDefault();
          del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
          </td>
          </tr>
          ';
        }

        $impuesto = round( $sub_total * ($iva / 100), 2 );
        $tl_sniva = round( $sub_total - $impuesto, 2 );
        $total    = round( $tl_sniva + $impuesto, 2 );
        // Mostramos los totales que tendra la factura
        $detalleTotales = '
        <tr>
        <td colspan="5" class="textright">Sub Total</td>
        <td class="textright">'.$tl_sniva.'</td>
        </tr>
        <tr>
        <td colspan="5" class="textright">IVA ('.$iva.')</td>
        <td class="textright">'.$impuesto.'</td>
        </tr>
        <tr>
        <td colspan="5" class="textright">Total</td>
        <td class="textright">'.$total.'</td>
        </tr>
        ';

        $arrayData['detalle'] = $detalleTabla;
        $arrayData['totales'] = $detalleTotales;

        echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
      } else {
        echo "error";
      }
      mysqli_close($conection);

    }
    exit;
  }

//Eliminar datos del detalle de la tabla detalle_temp que esta en la pantalla Nueva venta
  if ($_POST['action'] == 'del_product_detalle'){
    //print_r($_POST); exit;

    if (empty($_POST['id_detalle'])) {
      echo "error";
    } else {

      $id_detalle = $_POST['id_detalle'];
      $token      = md5($_SESSION['idUser']);

        $query_iva = mysqli_query($conection,"SELECT iva FROM configuracion");
        $result_iva = mysqli_num_rows($query_iva);

        $query_detalle_temp = mysqli_query($conection, "CALL del_detalle_temp($id_detalle, '$token') ");
        $result = mysqli_num_rows($query_detalle_temp);

        $detalleTabla = '';
        $sub_total    = 0;
        $iva          = 0;
        $total        = 0;
        $arrayData    = array();

        if ($result > 0) {
          if ($result_iva > 0) {
            $info_iva = mysqli_fetch_assoc($query_iva);
            $iva      = $info_iva['iva'];
          }

          while ($data = mysqli_fetch_assoc($query_detalle_temp)) {
            $precioTotal = round( $data['cantidad'] * $data['precio_venta'], 2);
            $sub_total   = round( $sub_total + $precioTotal, 2);
            $total       = round( $total + $precioTotal, 2);

            // Mostramos el producto que se va a vender concatenando el HTML
            $detalleTabla .= '
                <tr>
                <td>'.$data['codproducto'].'</td>
                <td colspan="2">'.$data['descripcion'].'</td>
                <td class="textcenter">'.$data['cantidad'].'</td>
                <td class="textright">'.$data['precio_venta'].'</td>
                <td class="textright">'.$precioTotal.'</td>
                <td class="">
                <a href="#" class="link_delete" onclick="event.preventDefault();
                del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                </td>
                </tr>
                ';
          }

          $impuesto = round( $sub_total * ($iva / 100), 2 );
          $tl_sniva = round( $sub_total - $impuesto, 2 );
          $total    = round( $tl_sniva + $impuesto, 2 );
          // Mostramos los totales que tendra la factura
          $detalleTotales = '
                <tr>
                <td colspan="5" class="textright">Sub Total</td>
                <td class="textright">'.$tl_sniva.'</td>
                </tr>
                <tr>
                <td colspan="5" class="textright">IVA ('.$iva.')</td>
                <td class="textright">'.$impuesto.'</td>
                </tr>
                <tr>
                <td colspan="5" class="textright">Total</td>
                <td class="textright">'.$total.'</td>
                </tr>
                ';

          $arrayData['detalle'] = $detalleTabla;
          $arrayData['totales'] = $detalleTotales;

          echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
        } else {
          echo "error";
        }
        mysqli_close($conection);
      }
      exit;
  }

//Anular datos de la tabla detalle_temp desde la pantalla Nueva venta
if ($_POST['action'] == 'anularVenta') {

  $token = md5($_SESSION['idUser']);

  $query_del = mysqli_query($conection,"DELETE FROM detalle_temp WHERE token_user = '$token' ");
  mysqli_close($conection);

  if ($query_del) {
    echo 'ok';
  } else {
    echo 'error';
  }
  exit;
}

//Procesar Venta
if ($_POST['action'] == 'procesarVenta'){

  $codCliente = $_POST['codCliente'];
  $token  = md5($_SESSION['idUser']);
  $usuario = $_SESSION['idUser'];

  $query = mysqli_query($conection,"SELECT * FROM detalle_temp WHERE token_user = '$token' ");
  $result = mysqli_num_rows($query);

  if ($result > 0) {
    $query_procesar = mysqli_query($conection,"CALL procesar_venta($usuario,$codCliente,'$token') ");
    $result_detalle = mysqli_num_rows($query_procesar);

    if ($result_detalle > 0) {
      $data = mysqli_fetch_assoc($query_procesar);
      echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }else {
      echo "error";
    }
  }else {
    echo "error";
  }
  mysqli_close($conection);

  exit;
}

// Traemos la informacion de la factura cuando apretamos algun boton de la ventana Ventas
if ($_POST['action'] == 'infoFactura'){
  //print_r($_POST); exit;

  if (!empty($_POST['nofactura'])) {

    $nofactura = $_POST['nofactura'];
    $query     = mysqli_query($conection,"SELECT * FROM factura WHERE nofactura = '$nofactura' AND estatus = 1");
    mysqli_close($conection);

    $result = mysqli_num_rows($query);

      if ($result > 0) {
        $data = mysqli_fetch_assoc($query);
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
        exit;
      }
    }
    echo "error";
    exit;
}

// Boton anular factura de la ventana VENTAS
if ($_POST['action'] == 'anularFactura') {
  if (!empty($_POST['noFactura'])) {
    $noFactura = $_POST['noFactura'];

    $query_anular = mysqli_query($conection,"CALL anular_factura($noFactura)");
    mysqli_close($conection);
    $result = mysqli_num_rows($query_anular);

    if ($result > 0) {
      $data = mysqli_fetch_assoc($query_anular);
      echo json_encode($data,JSON_UNESCAPED_UNICODE);
      exit;
    }
  }
  echo "error";
  exit;
}

// Boton cambiar contraseña de la ventana PRINCIPAL
if ($_POST['action'] == 'changePassword') {
  //print_r($_POST);
  if (!empty($_POST['passActual']) && !empty($_POST['passNuevo'])) {
    $password = md5($_POST['passActual']);
    $newPass = md5($_POST['passNuevo']);
    $idUser = $_SESSION['idUser'];

    $code = '';
    $msg = '';
    $arrayData = array();

    $query_user = mysqli_query($conection,"SELECT * FROM usuario WHERE clave = '$password' AND idusuario = $idUser ");
    $result = mysqli_num_rows($query_user);

    if ($result > 0) {
      $query_update = mysqli_query($conection,"UPDATE usuario SET clave = '$newPass' WHERE idusuario = $idUser ");
      mysqli_close($conection);

      if ($query_update) {
        $code = '00';
        $msg = "Su contraseña se ha actualizado con exito";
      } else {
        $code = '2';
        $msg = "No es posible cambiar su contraseña";
      }
    } else {
      $code = '1';
      $msg = "La contraseña actual es incorrecta";
    }

    $arrayData = array('cod' => $code, 'msg' => $msg );
    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);

  } else {
    echo "error";
  }
  exit;
}

// Codigo Actualizar los datos de la Empresa
if ($_POST['action'] == 'updateDataEmpresa'){

  if (empty($_POST['txtRut']) || empty($_POST['txtNombre']) || empty($_POST['txtRSocial']) || empty($_POST['txtTelEmpresa'])
      || empty($_POST['txtEmailEmpresa']) || empty($_POST['txtDirEmpresa']) || empty($_POST['txtIva']) ) {

    $code = '1';
    $msg  = "Todos los campos son obligatorios";

  } else {

    $intRut     = intval($_POST['txtRut']);
    $strNombre  = $_POST['txtNombre'];
    $strRSocial = $_POST['txtRSocial'];
    $intTel     = intval($_POST['txtTelEmpresa']);
    $strEmail   = $_POST['txtEmailEmpresa'];
    $strDir     = $_POST['txtDirEmpresa'];
    $strIva     = $_POST['txtIva'];

    $query_upd = mysqli_query($conection,"UPDATE configuracion SET rut = $intRut, nombre = '$strNombre',
                                          razon_social = '$strRSocial', telefono = $intTel, email = '$strEmail',
                                          direccion = '$strDir', iva = '$strIva' WHERE id_configuracion = 1 ");
    mysqli_close($conection);

    if ($query_upd) {
      $code = '00';
      $msg  = "Datos actualizados correctamente";
    } else {
      $code = '2';
      $msg  = "Error al actualizar los datos";
    }
  }

  $arrData = array('cod' => $code, 'msg' => $msg);
  echo json_encode($arrData,JSON_UNESCAPED_UNICODE);
  exit;
}




//llave de Fin del ajax.php
}
exit;
?>
