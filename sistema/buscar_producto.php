<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();

include_once '../conexion.php';

if ( (isset($_REQUEST['busqueda']) && $_REQUEST['busqueda'] == '') || ( isset($_REQUEST['proveedor']) && $_REQUEST['proveedor'] == '' ) ) {
      header("Location: lista_productos.php");
      mysqli_close($conection);
    }

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?php include_once 'includes/scripts.php'; ?>
  <title>Lista de Productos</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <?php
    $busqueda = '';
    $search_proveedor = '';

    if (!empty($_REQUEST['busqueda'])) {
      $busqueda = strtolower($_REQUEST['busqueda']);
      $where = "(p.codproducto LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%') AND p.estatus = 1";
      $buscar = 'busqueda='.$busqueda;
    }
    if (!empty($_REQUEST['proveedor'])) {
      $search_proveedor = $_REQUEST['proveedor'];
      $where = "p.proveedor LIKE $search_proveedor AND p.estatus = 1";
      $buscar = 'proveedor='.$search_proveedor;
    }
    ?>
    <h1><i class="fa fa-cubes"></i> Lista de Productos</h1>

    <form class="form_search" action="buscar_producto.php" method="get">
      <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="<?php echo $busqueda; ?>">
      <button type="submit" class="btn_search" name="button"><i class="fa-solid fa-magnifying-glass"></i> </button>
    </form>
    <table>
      <tr>
        <th>Codigo</th>
        <th>Producto</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>
          <?php
          $pro = 0;
          if (!empty($_REQUEST['proveedor'])) {
            $pro = $_REQUEST['proveedor'];
          }
          $query_proveedor = mysqli_query($conection," SELECT codproveedor, proveedor FROM proveedor
            WHERE estatus = 1 ORDER BY proveedor ASC ");
            $result_Proveedor = mysqli_num_rows($query_proveedor);
            ?>
            <select name="proveedor" id="search_proveedor">
              <option value="" selected>PROVEEDOR</option>
              <?php
              if ($result_Proveedor > 0) {
                while ($prov = mysqli_fetch_array($query_proveedor)) {
                  if ($pro == $prov["codproveedor"]) {
                    ?>
                    <option value="<?php echo $prov['codproveedor']; ?>" selected><?php echo $prov['proveedor']; ?></option>
                    <?php
                  } else {
                    ?>
                    <option value="<?php echo $prov['codproveedor']; ?>"><?php echo $prov['proveedor']; ?></option>
                    <?php
                  }
                }
              }
              ?>
            </select>
          </th>
          <th>Foto</th>
          <th>ACCIONES</th>
        </tr>
        <?php
        //Codigo para armar el paginador puro con php
        $sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM producto AS p WHERE $where");

        $result_register = mysqli_fetch_array($sql_registe);
        $total_registro = $result_register['total_registro'];

        $por_pagina = 10;  //esta variable nos sirve para mostrar la cantidad de registros por pagina

        if (empty($_GET['pagina'])) {
          $pagina = 1;
        } else {
          $pagina = $_GET['pagina'];
        }

        $desde = ($pagina-1) * $por_pagina;
        $total_paginas = ceil($total_registro / $por_pagina);
        // fin del codigo para el paginador

        $query = mysqli_query($conection,"SELECT p.codproducto, p.descripcion, p.precio, p.existencia, pr.proveedor, p.foto
          FROM producto p INNER JOIN proveedor pr ON p.proveedor = pr.codproveedor
          WHERE $where
          ORDER BY p.codproducto ASC /*esta instruccion me ordena de forma acendente la lista */
          LIMIT $desde, $por_pagina"); /* esta instruccion marca el limite para el paginador*/

          mysqli_close($conection);
          $result = mysqli_num_rows($query);

          if ($result > 0) {
            while ($data = mysqli_fetch_array($query)) {
              if ($data['foto'] != 'img_producto.png') {
                $foto = 'img/uploads/'.$data['foto'];
              } else {
                $foto = 'img/'.$data['foto'];
              }
              ?>
              <tr class="row <?php echo $data['codproducto'] ?>">
                <td><?php echo $data['codproducto'] ?></td>
                <td><?php echo $data['descripcion'] ?></td>
                <td class="celPrecio"><?php echo $data['precio'] ?></td>
                <td class="celExistencia"><?php echo $data['existencia'] ?></td>
                <td><?php echo $data['proveedor'] ?></td>
                <td><img src="<?php echo $foto; ?>" alt="<?php echo $data['descripcion'] ?>" style="width: 90px;"></td>
                <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {?>
                  <td>
                    <a class="link_add add_product" product="<?php echo $data['codproducto'] ?>" href="#"><i class="fa-solid fa-plus"></i> AGREGAR</a>
                    |
                    <a class="link_edit" href="editar_producto.php?id=<?php echo $data['codproducto'] ?>"><i class="fa-solid fa-pen-to-square"></i> EDITAR</a>
                    |
                    <a class="link_delete del_product" href="#" product="<?php echo $data['codproducto'] ?>" ><i class="fa-solid fa-trash-can"></i> ELIMINAR</a>
                  </td>
                <?php } ?>
              </tr>
              <?php
            }
          }
          ?>
        </table>
        <?php
        if ($total_paginas != 0) {
          ?>
          <!-- bloque para mostrar el paginador en html con cogido php -->
          <div class="paginador">
            <ul>
              <!-- si la pagina es diferente de 1 entonces se muestra las flechas que van al principio -->
              <?php if ($pagina != 1){ ?>
                <li><a href="?pagina=<?php echo 1; ?>&<?php echo $buscar; ?>"><i class="fa-solid fa-backward-step"></i> </a></li>
                <li><a href="?pagina=<?php echo $pagina-1; ?>&<?php echo $buscar; ?>"><i class="fa-solid fa-backward"></i> </a></li>
                <?php
              }
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
                <li><a href="?pagina=<?php echo $pagina+1; ?>&<?php echo $buscar; ?>"><i class="fa-solid fa-forward"></i> </a></li>
                <li><a href="?pagina=<?php echo $total_paginas; ?>&<?php echo $buscar; ?>"><i class="fa-solid fa-forward-step"></i> </a></li>
              <?php } ?>
            </ul>
          </div>
        <?php } ?>
<!-- final del bloque paginador -->
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
