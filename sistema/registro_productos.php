<?php
//Controlamos el rol del usuario que se esta conectando para darle permisos
session_start();

if ($_SESSION['rol'] == 3) {
  header("Location: ../");
}

include_once '../conexion.php';

if (!empty($_POST)) {
  $alert = '';
  if (empty($_POST['proveedor']) || empty($_POST['producto']) || empty($_POST['precio']) || $_POST['precio'] <= 0
                                 || empty($_POST['cantidad']) || $_POST['cantidad'] <= 0) {
                                   
    $alert = '<p class="msg_error">Todos los campos son obligatorios</p>';
  } else {

    $proveedor  = $_POST['proveedor'];
    $producto   = $_POST['producto'];
    $precio     = $_POST['precio'];
    $cantidad   = $_POST['cantidad'];
    $usuario_id = $_SESSION['idUser'];

//--------------------- Pasos para guardar la foto a nuestro sistema paso a paso -----------------------
    /*
    para obtener los datos del archivo(la foto) nesesitamos $_FILES esto nos entrega el arreglo con los datos.
    Ej: Array ( [name] => bg-showcase-1.jpg [type] => image/jpeg [tmp_name] => C:\xampp\tmp\phpB412.tmp [error] => 0 [size] => 304839 )
    Esto es igual a lo que hicimos con los datos del usuario cuando inicia sesion.
    */
    $foto        = $_FILES['foto'];// accedemos al arreglo de la foto y lo guardamos en la variable $foto
    $nombre_foto = $foto['name']; // accedemos al nombre de la foto atraves del indice ->[name]
    $type        = $foto['type']; // accedemos al tipo de archivo (extencion) de la foto atraves del indice ->[type]
    $url_temp    = $foto['tmp_name']; // accedemos a la URL temporal atraves del indice ->[tmp_name]

    $imgProducto = 'img_producto.png'; // si el usuario no selleciono niguna si guardara esta por defecto.
// validamos los datos del arreglo foto
    if ($nombre_foto != '') {

      $destino = 'img/uploads/'; // indicamos la direccion en la que se almacenaria la foto en nuestra carpeta "uploads"
      $img_nombre = 'img_'.md5(date('d-m-Y H:m:s')); // generamos un nombre aleatorio para que sea unico
      $imgProducto = $img_nombre.'.jpg'; // al nombre del archivo(foto) le concatenamos la extencion .jpg
      $src         = $destino.$imgProducto; //guardamos el destino y el nombre del archivo (foto) para moverlo luego

    }
//--------------------- FIN Pasos para guardar la foto  ------------------------------------------------------


    $query = mysqli_query($conection,"SELECT * FROM producto WHERE descripcion = '$producto'");
    $result = mysqli_fetch_array($query);

    if ($result > 0) {
      $alert = '<p class="msg_error">El producto ya existen</p>';
    } else {
      $query_insert = mysqli_query($conection, "INSERT INTO
                      producto(descripcion, proveedor, precio, existencia, usuario_id,	foto) VALUES('$producto','$proveedor','$precio','$cantidad ','$usuario_id','$imgProducto')");

      if ($query_insert) {
        if ($nombre_foto != '') {
          move_uploaded_file($url_temp, $src);
        }
        $alert = '<p class="msg_save">Producto registrado correctamente</p>';
      } else {
        $alert = '<p class="msg_error">Error al registrar el producto</p>';
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
  <title>Nuevo Producto</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="form_register">
      <h1><i class="fa-solid fa-circle-plus"></i> Nuevo Producto</h1>
      <hr>
      <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>

      <form action="" method="post" enctype="multipart/form-data"> <!-- enctype="multipart/form-data" le da la capacidad al fomulario de adjuntar archivos y guardarlos en la base de datos -->
        <label for="proveedor">Proveedor</label>
    <?php
    $query_proveedor = mysqli_query($conection," SELECT codproveedor, proveedor FROM proveedor
                                                 WHERE estatus = 1 ORDER BY proveedor ASC ");
    mysqli_close($conection);
    $result_Proveedor = mysqli_num_rows($query_proveedor);
    ?>
        <select name="proveedor" id="proveedor">
    <?php
    if ($result_Proveedor > 0) {
      while ($prov = mysqli_fetch_array($query_proveedor)) { ?>
          <option value="<?php echo $prov['codproveedor']; ?>"><?php echo $prov['proveedor']; ?></option>
    <?php }
      }
    ?>
        </select>
        <label for="producto">Producto</label>
        <input type="text" name="producto" id="producto" placeholder="Descripcion del Producto">
        <label for="precio">Precio</label>
        <input type="number" name="precio" id="precio" placeholder="Precio unitario producto">
        <label for="cantidad">Cantidad</label>
        <input type="number" name="cantidad" id="cantidad" placeholder="Cantidad de producto">

        <div class="photo">
          <label for="foto">Foto</label>
          <div class="prevPhoto">
            <span class="delPhoto notBlock">X</span>
            <label for="foto"></label>
          </div>
          <div class="upimg">
            <input type="file" name="foto" id="foto">
          </div>
          <div id="form_alert"></div>
        </div>

        <button type="submit" name="CrearProducto" class="btn_save"><i class="fa-solid fa-floppy-disk"></i> Crear Producto</button>
      </form>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
