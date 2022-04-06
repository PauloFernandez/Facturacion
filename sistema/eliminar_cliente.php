<?php
session_start();
include_once '../conexion.php';

if (!empty($_POST)) {
  $idcliente = $_POST['idcliente'];

  $query_delete = mysqli_query($conection, "UPDATE cliente SET estatus = 0 WHERE idcliente = $idcliente");
  mysqli_close($conection);
  if ($query_delete) {
    header('Location: lista_clientes.php');
  } else {
    echo "Error al Deshabilitar";
  }
}

if (empty($_REQUEST['id'])) {
  header("Location: lista_clientes.php");
  mysqli_close($conection);
} else {
  $idcliente = $_REQUEST['id'];
  $query = mysqli_query($conection, "SELECT * FROM cliente WHERE idcliente = $idcliente");
  mysqli_close($conection);

  $result = mysqli_num_rows($query);

  if ($result > 0) {
    while ($dato = mysqli_fetch_array($query)) {
      $doc = $dato['doc'];
      $nombre = $dato['nombre'];
      $telefono = $dato['telefono'];
      $direccion = $dato['direccion'];
    }
  } else {
    header("Location: lista_clientes.php");
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
  <title>Deshabilitar Cliente</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="data_delete">
      <i class="fa-solid fa-user-xmark fa-4x"></i>
      <br>
      <br>
      <h2>Esta seguro de deshabilitar el cliente</h2>
        <p>Documento: <span> <?php echo $doc; ?></span> </p>
        <p>Nombre: <span><?php echo $nombre; ?></span></p>
        <p>Telefono: <span><?php echo $telefono; ?></span></p>
        <p>Direccion: <span><?php echo $direccion; ?></span></p>

        <form method="POST" action="">
          <input type="hidden" name="idcliente" value="<?php echo $idcliente; ?>">
          <a href="lista_clientes.php" class="btn_cancel"><i class="fa-solid fa-ban"></i> Cancelar</a>
          <button type="submit" class="btn_ok"><i class="fa-solid fa-trash-can"></i> Eliminar</button>
        </form>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
