<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();
if ($_SESSION['rol'] != 1) {
  header("Location: ../");
}

include_once '../conexion.php';

if (!empty($_POST)) {
  $alert = '';
  if (empty($_POST['nombre']) || empty($_POST['correo']) || empty($_POST['usuario']) || empty($_POST['clave']) || empty($_POST['rol'])) {
    $alert = '<p class="msg_error">Todos los campos son obligatorios</p>';
  } else {

    $nombre = $_POST['nombre'];
    $email  = $_POST['correo'];
    $user   = $_POST['usuario'];
    $clave  = md5($_POST['clave']);
    $rol    = $_POST['rol'];

    $query = mysqli_query($conection,"SELECT * FROM usuario WHERE usuario = '$user' OR correo = '$email'");
    $result = mysqli_fetch_array($query);

    if ($result > 0) {
      $alert = '<p class="msg_error">El usuario o el correo ya existen</p>';
    } else {
      $query_insert = mysqli_query($conection, "INSERT INTO usuario(nombre, correo, usuario, clave, rol)
                                                VALUES('$nombre','$email','$user','$clave','$rol')");
      if ($query_insert) {
        $alert = '<p class="msg_save">Usuario creado correctamente</p>';
      } else {
        $alert = '<p class="msg_error">Error al crear un usuario</p>';
      }
    }
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
  <title>Registro Usuario</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="form_register">
      <h1><i class="fa-solid fa-user-plus"></i> Registro Usuario</h1>
      <hr>
      <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

      <form class="" action="" method="post">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" placeholder="Nombre completo">
        <label for="correo">Email</label>
        <input type="email" name="correo" id="correo" placeholder="Email">
        <label for="usuario">Usuario</label>
        <input type="text" name="usuario" id="usuario" placeholder="Nombre de Usuario">
        <label for="clave">Contraseña</label>
        <input type="password" name="clave" id="clave" placeholder="Contraseña">
        <label for="rol">Permiso de usuario</label>

        <?php
          $query_rol = mysqli_query($conection,"SELECT * FROM rol");
          mysqli_close($conection);
          $result_rol = mysqli_num_rows($query_rol);
        ?>
        <select name="rol" id="rol">
        <?php
          if ($result_rol > 0) {
            while ($rol = mysqli_fetch_array($query_rol)) {
        ?>
          <option value="<?php echo $rol["idrol"]; ?>"><?php echo $rol["rol"]; ?></option>
        <?php
            }
          }
        ?>
        </select>
        <button type="submit" class="btn_save"><i class="fa-solid fa-floppy-disk"></i> Crear usuario</button>
      </form>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
