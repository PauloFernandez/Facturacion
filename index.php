<?php
session_start();
$alert = '';

if (!empty($_SESSION['active'])) {
  header('location: sistema/principal.php');
} else {

  if (!empty($_POST)) {
    if (empty($_POST['usuario']) && empty($_POST['clave'])) {
      $alert = "Ingrese Usuario y Contraseña";
    } else {
      require_once "conexion.php";

      //mysqli_real_escape_string esta funcion sirve para evitar el ingreso de caracteres raros como comillas o simbolos que puedan hacer haqueable la cuenta
      $user = mysqli_real_escape_string($conection, $_POST['usuario']);
      $pass = md5(mysqli_real_escape_string($conection, $_POST['clave']));

      $query = mysqli_query($conection, "SELECT u.idusuario, u.nombre, u.correo, u.usuario, r.idrol, r.rol
                                         FROM usuario u INNER JOIN rol r ON u.rol = r.idrol
                                         WHERE u.usuario = '$user' AND u.clave = '$pass' ");
      mysqli_close($conection);
      $result = mysqli_num_rows($query);

      if ($result > 0) {
        $data = mysqli_fetch_array($query);

        $_SESSION['active'] = true;
        $_SESSION['idUser'] = $data['idusuario'];
        $_SESSION['nombre'] = $data['nombre'];
        $_SESSION['email']  = $data['correo'];
        $_SESSION['user']   = $data['usuario'];
        $_SESSION['rol']    = $data['idrol'];
        $_SESSION['rol_name']    = $data['rol'];

        header('location: sistema/principal.php');

      } else {
        $alert = 'El Usuario o la Contraseña son incorrectos';
        session_destroy();
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
  <title>Login | Sistema Facturacion</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <section id="container">
    <form class="" action="" method="post">
      <h3>Iniciar Sesion</h3>
      <img src="img/iniciar.png" alt="Login">

      <input type="text" name="usuario" placeholder="Usuario">
      <input type="password" name="clave" placeholder="Contraseña">
      <div class="alert"><?php echo isset($alert) ? $alert : ''; ?></div>
      <input type="submit" value="INGRESAR">
    </form>

  </section>
</body>
</html>
