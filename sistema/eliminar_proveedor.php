<?php
session_start();
include_once '../conexion.php';

if (!empty($_POST)) {
  $idproveedor = $_POST['idproveedor'];

  $sql_delete = mysqli_query($conection,"UPDATE proveedor SET estatus = 0 WHERE codproveedor = $idproveedor");
  mysqli_close($conection);
  
  if ($sql_delete) {
    header("Location: lista_proveedor.php");
  } else {
    echo "Error al deshabilitar al proveedor";
  }
}

//Mostrar datos del Proveedor
if (empty($_REQUEST['id'])) {
  header("Location: lista_proveedor.php");
  mysqli_close($conection);
} else {
  $idproveedor = $_REQUEST['id']; //Recupero el id que envio desde el listado de proveedor

  $query = mysqli_query($conection,"SELECT * FROM proveedor WHERE codproveedor = $idproveedor"); //buscos todos los datos
  mysqli_close($conection);

  $result = mysqli_num_rows($query);

  if ($result > 0) {
    while ($dato = mysqli_fetch_array($query)) { //con el ciclo while busco los datos en un arreglo y muestro lo especifico

      $idproveedor = $dato['codproveedor'];
      $proveedor   = $dato['proveedor'];
      $contacto    = $dato['contacto'];
      $telefono    = $dato['telefono'];
      $direccion   = $dato['direccion'];
    }
  } else {
    header("Location: lista_proveedor.php");
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
  <title>Deshabilitar Proveedor</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="data_delete">
      <i class="fa-solid fa-user-xmark fa-4x"></i>
      <br>
      <br>
      <h2>Esta seguro de deshabilitar el proveedor</h2>
        <p>Proveedor: <span><?php echo $proveedor; ?></span> </p>
        <p>Contacto: <span><?php echo $contacto; ?></span></p>
        <p>Telefono: <span><?php echo $telefono; ?></span></p>
        <p>Direccion: <span><?php echo $direccion; ?></span></p>

        <form method="POST" action="">
          <input type="hidden" name="idproveedor" value="<?php echo $idproveedor; ?>">
          <a href="lista_proveedor.php" class="btn_cancel"><i class="fa-solid fa-ban"></i> Cancelar</a>
          <button type="submit" class="btn_ok"><i class="fa-solid fa-trash-can"></i> Eliminar</button>
        </form>
    </div>
  </section>
<?php include_once 'includes/footer.php'; ?>
</body>
</html>
