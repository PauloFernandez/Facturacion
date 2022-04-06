<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();

include '../conexion.php';

if (!empty($_POST)) {
  $alert = '';
  if (empty($_POST['rut']) || empty($_POST['proveedor']) || empty($_POST['contacto']) || empty($_POST['telefono']) || empty($_POST['direccion'])) {
    $alert = '<p class="msg_error">Todos los campos son obligatorios</p>';
  } else {

    $idproveedor = $_POST['id'];
    $rut        = $_POST['rut'];
    $proveedor  = $_POST['proveedor'];
    $contacto   = $_POST['contacto'];
    $telefono   = $_POST['telefono'];
    $direccion  = $_POST['direccion'];
    $usuario_id = $_SESSION['idUser'];

    $sql_update = mysqli_query($conection, "UPDATE proveedor
      SET rut = $rut, proveedor = '$proveedor', contacto = '$contacto', telefono = '$telefono', direccion = '$direccion', usuario_id = $usuario_id WHERE codproveedor = $idproveedor");

      if ($sql_update) {
        $alert = '<p class="msg_save">Proveedor actualizado correctamente</p>';
        header('Location: lista_proveedor.php');
      } else {
        $alert = '<p class="msg_error">Error al actualizar el proveedor</p>';
      }
    }
}

//Mostrar datos
if (empty($_REQUEST['id'])) {
  header('Location: lista_proveedor.php');
  mysqli_close($conection);
}
$idproveedor = $_REQUEST['id'];

$sql = mysqli_query($conection,"SELECT * FROM proveedor WHERE codproveedor = $idproveedor AND estatus = 1");
mysqli_close($conection);

$result_sql = mysqli_num_rows($sql);

if ($result_sql == 0) {
  header('Location: lista_proveedor.php');
} else {
  while ($data = mysqli_fetch_array($sql)) {

    $idproveedor = $data['codproveedor'];
    $rut         = $data['rut'];
    $proveedor   = $data['proveedor'];
    $contacto    = $data['contacto'];
    $telefono    = $data['telefono'];
    $direccion   = $data['direccion'];

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
  <title>Editar Proveedor</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="form_register">
      <h1><i class="fa-solid fa-pen-to-square"></i> Editar Proveedor</h1>
      <hr>
      <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

      <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $idproveedor; ?>">
        <label for="rut">RUT</label>
        <input type="number" name="rut" id="rut" value="<?php echo $rut; ?>">
        <label for="proveedor">Proveedor</label>
        <input type="text" name="proveedor" id="proveedor" value="<?php echo $proveedor; ?>">
        <label for="contacto">Contacto</label>
        <input type="text" name="contacto" id="contacto" value="<?php echo $contacto; ?>">
        <label for="telefono">Telefono</label>
        <input type="tel" name="telefono" id="telefono" placeholder="Telefono" value="<?php echo $telefono; ?>">
        <label for="direccion">Direccion</label>
        <input type="text" name="direccion" id="direccion" placeholder="Direccion" value="<?php echo $direccion; ?>">

        <button type="submit" name="editarProveedor" class="btn_save"><i class="fa-solid fa-pen-to-square"></i> Actualizar</button>
      </form>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
