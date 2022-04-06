<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();
if ($_SESSION['rol'] != 1) {
  header("Location: ../");
}

include_once '../conexion.php';

if (!empty($_POST)) {

/*
con este codigo controlamos que el Super usuario que es el que tiene el id 1 no se pueda deshabilitar sin importar que sea Administrador o Supervisor etc. ya que es el primero que se creo para poder ingresar al sistema y el que siempre va a estar habilitado su ingreso.
*/
  if ($_POST['idusuario'] == 1) {
    header('Location: lista_usuario.php');
    mysqli_close($conection);
    exit;
  }
//fin del codigo proteccion super usuario
  $idusuario = $_POST['idusuario'];

  $query_delete = mysqli_query($conection, "UPDATE usuario SET estatus = 0 WHERE idusuario = $idusuario");
  mysqli_close($conection);
  if ($query_delete) {
    header('Location: lista_usuario.php');
  } else {
    echo "Error al Deshabilitar";
  }
}

if (empty($_REQUEST['id']) || $_REQUEST['id'] == 1) {
  header('Location: lista_usuario.php');
  mysqli_close($conection);
} else {
  $idusuario = $_REQUEST['id'];

  $query = mysqli_query($conection, "SELECT u.nombre, u.usuario, r.rol FROM usuario u INNER JOIN rol r
                                      ON u.rol = r.idrol WHERE u.idusuario = $idusuario");

  mysqli_close($conection);
  $result = mysqli_num_rows($query);

  if ($result > 0) {
    while ($data = mysqli_fetch_array($query)) {
      $nombre   = $data['nombre'];
      $usuario  = $data['usuario'];
      $rol      = $data['rol'];
    }
  } else {
    include_once '../conexion.php';
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
  <title>Deshabilitar Usuario</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="data_delete">
      <i class="fa-solid fa-user-xmark fa-4x"></i>
      <h2>Esta seguro de deshabilitar el usuario</h2>
        <p>Nombre: <span><?php echo $nombre; ?></span></p>
        <p>Usuario: <span><?php echo $usuario; ?></span></p>
        <p>Permiso de usuario: <span><?php echo $rol; ?></span></p>

        <form method="POST" action="">
          <input type="hidden" name="idusuario" value="<?php echo $idusuario; ?>">
          <a href="lista_usuario.php" class="btn_cancel"><i class="fa-solid fa-ban"></i> Cancelar</a>
          <button type="submit" class="btn_ok"><i class="fa-solid fa-trash-can"></i> Eliminar</button>
        </form>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
