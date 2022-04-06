<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();
if ($_SESSION['rol'] != 1) {
  header("Location: ../");
}

include_once '../conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?php include_once 'includes/scripts.php'; ?>
  <title>Lista de Usuario</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <?php
// este comando (strtolower) nos sirve para indicar que todo se convierta en minusculas
    $busqueda = strtolower(($_REQUEST['busqueda']));
    if (empty($busqueda)) {
      header("Location: lista_usuario.php");
      mysqli_close($conection);
    }
    ?>
    <h1><i class="fa-solid fa-users"></i> Lista de Usuario</h1>
    <form class="form_search" action="buscar_usuario.php" method="get">
      <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="<?php echo $busqueda; ?>">
      <button type="submit" name="button" class="btn_search"><i class="fa-solid fa-magnifying-glass"></i> </button>
    </form>
    <table>
      <tr>
        <th>Id</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Usuario</th>
        <th>Rol</th>
        <th>ACCIONES</th>
      </tr>
      <?php
      //Codigo para armar el paginador puro con php
      $rol = '';
      if ($busqueda == 'administrador') {
        $rol = "OR rol LIKE '%1%' ";
      } else if ($busqueda == 'supervisor'){
        $rol = "OR rol LIKE '%2%' ";
      }else if ($busqueda == 'vendedor'){
        $rol = "OR rol LIKE '%3%' ";
      }

      $sql_registe= mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM usuario
                                  WHERE (idusuario LIKE '%$busqueda%' OR
                                         nombre LIKE '%$busqueda%' OR
                                         correo LIKE '%$busqueda%' OR
                                         usuario LIKE '%$busqueda%'
                                         $rol )
                                  AND estatus = 1");

      $result_register = mysqli_fetch_array($sql_registe);
      $total_registro = $result_register['total_registro'];

      $por_pagina = 2;  //esta variable nos sirve para mostrar la cantidad de registros por pagina

      if (empty($_GET['pagina'])) {
        $pagina = 1;
      } else {
        $pagina = $_GET['pagina'];
      }

      $desde = ($pagina-1) * $por_pagina;
      $total_paginas = ceil($total_registro / $por_pagina);
      // fin del codigo para el paginador

      $query = mysqli_query($conection,"SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.rol
                              FROM usuario u INNER JOIN rol r ON u.rol = r.idrol
                              WHERE (u.idusuario LIKE '%$busqueda%' OR
                                     u.nombre LIKE '%$busqueda%' OR
                                     u.correo LIKE '%$busqueda%' OR
                                     u.usuario LIKE '%$busqueda%' OR
                                     r.rol LIKE '%$busqueda%' )
                              AND estatus = 1
                              ORDER BY idusuario ASC /*esta instruccion me ordena de forma acendente la lista */
                              LIMIT $desde,$por_pagina"); /* esta instruccion marca el limite para el paginador*/
        mysqli_close($conection);
        $result = mysqli_num_rows($query);

        if ($result > 0) {
          while ($data = mysqli_fetch_array($query)) {
      ?>
      <tr>
        <td><?php echo $data['idusuario'] ?></td>
        <td><?php echo $data['nombre'] ?></td>
        <td><?php echo $data['correo'] ?></td>
        <td><?php echo $data['usuario'] ?></td>
        <td><?php echo $data['rol'] ?></td>
        <td>
          <a class="link_edit" href="editar_usuario.php?id=<?php echo $data['idusuario'] ?>"><i class="fa-solid fa-pen-to-square"></i> EDITAR</a>
          <?php
          if ($data['idusuario'] != 1) { ?>
          |
          <a class="link_delete" href="eliminar_usuario.php?id=<?php echo $data['idusuario'] ?>"><i class="fa-solid fa-trash-can"></i> ELIMINAR</a>
          <?php } ?>
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
