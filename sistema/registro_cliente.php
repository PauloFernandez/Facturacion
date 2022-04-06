<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();
/*
Este codigo es para la balidacion de roles
if ($_SESSION['rol'] != 1) {
  header("Location: ../");
}
*/
include_once '../conexion.php';

if (!empty($_POST)) {
  $alert = '';
  if (empty($_POST['doc']) || empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['direccion'])) {
    $alert = '<p class="msg_error">Todos los campos son obligatorios</p>';
  } else {

    $doc        = $_POST['doc'];
    $nombre     = $_POST['nombre'];
    $telefono   = $_POST['telefono'];
    $direccion  = $_POST['direccion'];
    $usuario_id = $_SESSION['idUser'];

    $result =0;

    if (is_numeric($doc)) {
      $query = mysqli_query($conection,"SELECT * FROM cliente WHERE doc = '$doc'");
      $result = mysqli_fetch_array($query);
    }

    if ($result > 0) {
      $alert = '<p class="msg_error">El número de documento ya existen</p>';
    } else {
      $query_insert = mysqli_query($conection, "INSERT INTO cliente(doc, nombre, telefono, direccion,	usuario_id) VALUES('$doc','$nombre','$telefono','$direccion','$usuario_id')");

      if ($query_insert) {
        $alert = '<p class="msg_save">Cliente registrado correctamente</p>';
      } else {
        $alert = '<p class="msg_error">Error al registrar el cliente</p>';
      }
    }
  }
  mysqli_close($conection);//ver si afecta
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?php include_once 'includes/scripts.php'; ?>
  <title>Registro Cliente</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="form_register">
      <h1><i class="fa-solid fa-user-plus"></i> Registro Cliente</h1>
      <hr>
      <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

      <form class="" action="" method="post">
        <label for="doc">Documento</label>
        <input type="number" name="doc" id="doc" placeholder="Documento Identidad">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" placeholder="Nombre completo">
        <label for="telefono">Teléfono</label>
        <input type="tel" name="telefono" id="telefono" placeholder="Número de contacto">
        <label for="direccion">Dirección</label>
        <input type="text" name="direccion" id="direccion" placeholder="Dirección">

        <button type="submit" name="CrearUsuario" class="btn_save"><i class="fa-solid fa-floppy-disk"></i> Crear Cliente</button>
      </form>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
