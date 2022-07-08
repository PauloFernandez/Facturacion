<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();

include_once '../conexion.php';
if ( isset($_REQUEST['busqueda']) && $_REQUEST['busqueda'] == '' ) {
  header("Location: lista_clientes.php");
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
  <title>Lista de Cliente</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <?php
// este comando (strtolower) nos sirve para indicar que todo se convierta en minusculas
    $busqueda = strtolower(($_REQUEST['busqueda']));
    ?>
    <h1><i class="fa-solid fa-users"></i> Lista de Clientes</h1>

    <form class="form_search" action="buscar_cliente.php" method="get">
      <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="<?php echo $busqueda; ?>">
      <button type="submit" class="btn_search" name="button"><i class="fa-solid fa-magnifying-glass"></i> </button>
    </form>
    <table>
      <tr>
        <th>Id</th>
        <th>Documento</th>
        <th>Nombre</th>
        <th>Teléfono</th>
        <th>Dirección</th>
        <th>ACCIONES</th>
      </tr>
      <?php
      //Codigo para armar el paginador puro con php
      $sql_registe= mysqli_query($conection, "SELECT COUNT(*) AS total_registro FROM cliente
                                        WHERE (idcliente LIKE '%$busqueda%' OR doc LIKE '%$busqueda%' OR nombre LIKE '%$busqueda%' OR direccion LIKE '%$busqueda%')
                                        AND estatus = 1 ");

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

      $query = mysqli_query($conection, "SELECT * FROM cliente
                                        WHERE (doc LIKE '%$busqueda%' OR nombre LIKE '%$busqueda%' OR direccion LIKE '%$busqueda%')
                                        AND estatus = 1 ORDER BY idcliente ASC LIMIT $desde,$por_pagina"); /* esta instruccion marca el limite para el paginador*/
        mysqli_close($conection);
        $result = mysqli_num_rows($query);

        if ($result > 0) {
          while ($dato = mysqli_fetch_array($query)) {
      ?>
      <tr>
        <td><?php echo $dato['idcliente']; ?></td>
        <td><?php echo $dato['doc']; ?></td>
        <td><?php echo $dato['nombre']; ?></td>
        <td><?php echo $dato['telefono']; ?></td>
        <td><?php echo $dato['direccion']; ?></td>
        <td>
          <a class="link_edit" href="editar_cliente.php?id=<?php echo $data['idcliente'] ?>"><i class="fa-solid fa-pen-to-square"></i> EDITAR</a>
          |
          <a class="link_delete" href="eliminar_cliente.php?id=<?php echo $data['idcliente'] ?>"><i class="fa-solid fa-trash-can"></i> ELIMINAR</a>
        </td>
      </tr>
      <?php
          }
        }
      ?>
    </table>
<?php
if ($total_registro !=0) {
?>
<!-- bloque para mostrar el paginador en html con cogido php -->
    <div class="paginador">
      <ul>
<!-- si la pagina es diferente de 1 entonces se muestra las flechas que van al principio -->
      <?php if ($pagina != 1){ ?>
        <li><a href="?pagina=<?php echo 1; ?>&busqueda=<?php echo $busqueda; ?>"><i class="fa-solid fa-backward-step"></i> </a></li>
        <li><a href="?pagina=<?php echo $pagina-1; ?>&busqueda=<?php echo $busqueda; ?>"><i class="fa-solid fa-backward"></i> </a></li>
      <?php } ?>

      <?php
          for ($i=1; $i <= $total_paginas; $i++) {
            if ($i == $pagina) {
              echo '<li class="pageSelected">'.$i.'</li>';
            } else {
              echo '<li><a href="?pagina='.$i.'&busqueda='.$busqueda.'">'.$i.'</a></li>';
            }
          }
      ?>
<!-- si la pagina es diferente de la ultima pagina entonces se muestra las flechas que van al final -->
      <?php if ($pagina != $total_paginas) { ?>
        <li><a href="?pagina=<?php echo $pagina+1; ?>&busqueda=<?php echo $busqueda; ?>"><i class="fa-solid fa-forward"></i> </a></li>
        <li><a href="?pagina=<?php echo $total_paginas; ?>&busqueda=<?php echo $busqueda; ?>"><i class="fa-solid fa-forward-step"></i> </a></li>
      <?php } ?>
      </ul>
    </div>
<?php } ?>
<!-- final del bloque paginador -->
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
