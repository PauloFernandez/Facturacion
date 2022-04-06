<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();
/*
if ($_SESSION['rol'] != 1) {
  header("Location: ../");
}
*/
include '../conexion.php';

if (!empty($_POST)) {
  $alert = '';
  if (empty($_POST['doc']) || empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['direccion'])) {
    $alert = '<p class="msg_error">Todos los campos son obligatorios</p>';
  } else {

    $idcliente = $_POST['id'];
    $doc       = $_POST['doc'];
    $nombre    = $_POST['nombre'];
    $telefono  = $_POST['telefono'];
    $direccion = $_POST['direccion'];

    $sql_update = mysqli_query($conection, "UPDATE cliente
      SET doc = $doc, nombre = '$nombre', telefono = '$telefono', direccion = '$direccion'
      WHERE idcliente = $idcliente ");

      if ($sql_update) {
        $alert = '<p class="msg_save">Usuario actualizado correctamente</p>';
        header('Location: lista_clientes.php');
      } else {
        $alert = '<p class="msg_error">Error al actualizar el usuario</p>';
      }
    }
  }

//Mostrar datos
if (empty($_GET['id'])) {
  header('Location: lista_clientes.php');
  mysqli_close($conection);
}
$idcliente = $_GET['id'];

$sql = mysqli_query($conection,"SELECT * FROM cliente WHERE idcliente = $idcliente AND estatus = 1 ");
mysqli_close($conection);

$result_sql = mysqli_num_rows($sql);

if ($result_sql == 0) {
  header('Location: lista_clientes.php');
} else {
  while ($data = mysqli_fetch_array($sql)) {

    $idcliente = $data['idcliente'];
    $doc       = $data['doc'];
    $nombre    = $data['nombre'];
    $telefono  = $data['telefono'];
    $direccion = $data['direccion'];

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
  <title>Editar Cliente</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="form_register">
      <h1><i class="fa-solid fa-pen-to-square"></i> Editar Cliente</h1>
      <hr>
      <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

      <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $idcliente; ?>">
        <label for="doc">Documento</label>
        <input type="number" name="doc" id="doc" placeholder="Docimento de identidad" value="<?php echo $doc; ?>">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" placeholder="Nombre completo" value="<?php echo $nombre; ?>">
        <label for="telefono">Telefono</label>
        <input type="tel" name="telefono" id="telefono" placeholder="Telefono" value="<?php echo $telefono; ?>">
        <label for="direccion">Direccion</label>
        <input type="text" name="direccion" id="direccion" placeholder="Direccion" value="<?php echo $direccion; ?>">

        <button type="submit" name="CrearUsuario" class="btn_save"><i class="fa-solid fa-pen-to-square"></i> Actualizar</button>
      </form>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
