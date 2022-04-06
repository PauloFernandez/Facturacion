<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();

include_once '../conexion.php';

if (!empty($_POST)) {
  $alert = '';
  if (empty($_POST['rut']) || empty($_POST['proveedor']) || empty($_POST['contacto']) || empty($_POST['telefono']) || empty($_POST['direccion'])) {
    $alert = '<p class="msg_error">Todos los campos son obligatorios</p>';
  } else {

    $rut        = $_POST['rut'];
    $proveedor  = $_POST['proveedor'];
    $contacto   = $_POST['contacto'];
    $telefono   = $_POST['telefono'];
    $direccion  = $_POST['direccion'];
    $usuario_id = $_SESSION['idUser'];

    $result =0;

    if (is_numeric($rut)) {
      $query = mysqli_query($conection,"SELECT * FROM proveedor WHERE rut = '$rut'");
      $result = mysqli_fetch_array($query);
    }

    if ($result > 0) {
      $alert = '<p class="msg_error">El número de RUT ya existen</p>';
    } else {
      $query_insert = mysqli_query($conection, "INSERT INTO proveedor(rut, proveedor, contacto, telefono, direccion,	usuario_id) VALUES('$rut','$proveedor','$contacto','$telefono','$direccion','$usuario_id')");

      if ($query_insert) {
        $alert = '<p class="msg_save">Proveedor registrado correctamente</p>';
      } else {
        $alert = '<p class="msg_error">Error al registrar el proveedor</p>';
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
  <title>Registro Proveedor</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="form_register">
      <h1><i class="fa-solid fa-circle-plus"></i> Registro Proveedor</h1>
      <hr>
      <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

      <form class="" action="" method="post">
        <label for="rut">RUT</label>
        <input type="number" name="rut" id="rut" placeholder="Numero de RUT">
        <label for="proveedor">Proveedor</label>
        <input type="text" name="proveedor" id="proveedor" placeholder="Razon Social del Proveedor">
        <label for="contacto">Contacto</label>
        <input type="text" name="contacto" id="contacto" placeholder="Nombre contacto">
        <label for="telefono">Teléfono</label>
        <input type="tel" name="telefono" id="telefono" placeholder="Número de contacto">
        <label for="direccion">Dirección</label>
        <input type="text" name="direccion" id="direccion" placeholder="Dirección">

        <button type="submit" name="CrearProveedor" class="btn_save"><i class="fa-solid fa-floppy-disk"></i> Crear Proveedor</button>
      </form>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
