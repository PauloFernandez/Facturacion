<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();
include_once '../conexion.php';

$busqueda = '';
$fecha_de = '';
$fecha_a  = '';

if ( isset($_REQUEST['busqueda']) && $_REQUEST['busqueda'] == '' ) {
  header("Location: ventas.php");
}

if ( isset($_REQUEST['fecha_de']) || isset($_REQUEST['fecha_a'])) {
  if ($_REQUEST['fecha_de'] == '' || $_REQUEST['fecha_a'] == '') {
    header("Location: ventas.php");
  }
}

if (!empty($_REQUEST['busqueda'])) {
  if (!is_numeric($_REQUEST['busqueda'])) {
    header("Location: ventas.php");
  }
  $busqueda = strtolower($_REQUEST['busqueda']);
  $where    = "nofactura = $busqueda";
  $buscar   = "busqueda = $busqueda";
}

if (!empty($_REQUEST['fecha_de']) && !empty($_REQUEST['fecha_a'])) {
  $fecha_de = $_REQUEST['fecha_de'];
  $fecha_a  = $_REQUEST['fecha_a'];
  $busqueda = '';

  if ($fecha_de > $fecha_a) {
    header("Location: ventas.php");
  } else if ($fecha_de == $fecha_a) {
    $where  = "fecha LIKE '$fecha_de%'";
    $buscar = "fecha_de=$fecha_de&fecha_a=$fecha_a";
  } else {
    $f_de   = $fecha_de.' 00:00:00';
    $f_a    = $fecha_a.' 23:59:59';
    $where  = "fecha BETWEEN '$f_de' AND '$f_a'";
    $buscar = "fecha_de=$fecha_de&fecha_a=$fecha_a";
  }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?php include_once 'includes/scripts.php'; ?>
  <title>Lista de Ventas</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <h1><i class="fa-solid fa-money-check-dollar"></i> Lista de Ventas</h1>
    <a href="nueva_venta.php" class="btn_new"><i class="fa-solid fa-file"></i> Nueva venta</a>

    <form class="form_search" action="buscar_venta.php" method="get">
      <input type="text" name="busqueda" id="busqueda" placeholder="No. Factura" value="<?php echo $busqueda; ?>">
      <button type="submit" class="btn_search"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <div>
      <h5>Buscar por fecha</h5>
      <form class="form_search_date" action="buscar_venta.php" method="get">
        <label>De: </label>
        <input type="date" name="fecha_de" id="fecha_de" value="<?php echo $fecha_de; ?>" required>
        <label> A </label>
        <input type="date" name="fecha_a" id="fecha_a" value="<?php echo $fecha_a; ?>" required>
        <button type="submit" class="btn_view"><i class="fa-solid fa-magnifying-glass"></i> </button>
      </form>
    </div>

    <table>
      <tr>
        <th>No.</th>
        <th>Fecha / Hora</th>
        <th>Cliente</th>
        <th>Vendedor</th>
        <th>Estado</th>
        <th class="textright">Total Factura</th>
        <th class="textright">ACCIONES</th>
      </tr>
      <?php
      //Codigo para armar el paginador puro con php
      $sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM factura WHERE $where");

      $result_register = mysqli_fetch_array($sql_registe);
      $total_registro = $result_register['total_registro'];

      $por_pagina = 5;  //esta variable nos sirve para mostrar la cantidad de registros por pagina

      if (empty($_GET['pagina'])) {
        $pagina = 1;
      } else {
        $pagina = $_GET['pagina'];
      }

      $desde = ($pagina-1) * $por_pagina;
      $total_paginas = ceil($total_registro / $por_pagina);
      // fin del codigo para el paginador

        $query = mysqli_query($conection,"SELECT f.nofactura, f.fecha, f.totalfactura, f.codcliente, f.estatus,
                                                 u.nombre AS vendedor, cl.nombre AS cliente
                                          FROM factura f INNER JOIN  usuario u ON f.usuario = u.idusuario
                                          INNER JOIN cliente cl ON f.codcliente = cl.idcliente
                                          WHERE $where AND f.estatus != 10 ORDER BY f.fecha DESC LIMIT $desde,$por_pagina ");

        mysqli_close($conection);
        $result = mysqli_num_rows($query);

        if ($result > 0) {
          while ($data = mysqli_fetch_array($query)) {
            if ($data['estatus'] == 1) {
              $estado = '<span class="pagada">Pagada</span>';
            } else {
              $estado = '<span class="anulada">Anulada</span>';
            }

      ?>
      <tr id="row_<?php echo ['nofactura']; ?>">
        <td><?php echo $data['nofactura']; ?></td>
        <td><?php echo $data['fecha']; ?></td>
        <td><?php echo $data['cliente']; ?></td>
        <td><?php echo $data['vendedor']; ?></td>
        <td class="estado"><?php echo $estado; ?></td>
        <td class="textright totalfactura"><span>$ </span><?php echo $data['totalfactura']; ?></td>

        <td>
          <div class="div_acciones">
            <div>
              <button class="btn_view view_factura" type="button" cl="<?php echo $data['codcliente']; ?>" f="<?php echo $data['nofactura']; ?>"><i class="fa-solid fa-eye"></i></button>
            </div>

      <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] ==2) {
              if ($data['estatus'] == 1) { ?>
                <div class="div_factura">
                  <button class="btn_anular anular_factura" fac="<?php echo $data['nofactura']; ?>">
                    <i class="fa-solid fa-ban"></i>
                  </button>
                </div>
      <?php   } else { ?>
                <div class="div_factura">
                  <button type="button" class="btn_anular inactive"><i class="fa-solid fa-ban"></i></button>
                </div>
      <?php   }
            }
      ?>
          </div>
        </td>
      </tr>
      <?php
          }
        }
      ?>
    </table>
<!-- bloque para mostrar el paginador en html con cogido php -->
    <div class="paginador">
      <ul>
<!-- si la pagina es diferente de 1 entonces se muestra las flechas que van al principio -->
      <?php if ($pagina != 1){ ?>
        <li><a href="?pagina=<?php echo 1; ?>&<?php echo $buscar; ?>"><i class="fa-solid fa-backward-step"></i> </a></li>
        <li><a href="?pagina=<?php echo $pagina - 1; ?>&<?php echo $buscar; ?>"><i class="fa-solid fa-backward"></i> </a></li>
      <?php } ?>

      <?php
          for ($i=1; $i <= $total_paginas; $i++) {
            if ($i == $pagina) {
              echo '<li class="pageSelected">'.$i.'</li>';
            } else {
              echo '<li><a href="?pagina='.$i.'&'.$buscar.'">'.$i.'</a></li>';
            }
          }
      ?>
<!-- si la pagina es diferente de la ultima pagina entonces se muestra las flechas que van al final -->
      <?php if ($pagina != $total_paginas) { ?>
        <li><a href="?pagina=<?php echo $pagina + 1; ?>&<?php echo $buscar; ?>"><i class="fa-solid fa-forward"></i> </a></li>
        <li><a href="?pagina=<?php echo $total_paginas; ?>&<?php echo $buscar; ?>"><i class="fa-solid fa-forward-step"></i> </a></li>
      <?php } ?>
      </ul>
    </div>
<!-- final del bloque paginador -->
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
