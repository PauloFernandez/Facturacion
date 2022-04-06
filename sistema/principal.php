<?php

session_start();
include_once '../conexion.php';

//Quey para traer los datos de la Empresa
$rut = '';
$nameEmpresa = '';
$razonSocial = '';
$telEmpresa = '';
$emailEmpresa = '';
$dirEmpresa = '';
$iva = '';

$query_empresa = mysqli_query($conection,"SELECT * FROM configuracion");
$result_rows = mysqli_num_rows($query_empresa);

if ($result_rows > 0) {
  while ($arrInfoEmpresa = mysqli_fetch_assoc($query_empresa)) {
    $rut          = $arrInfoEmpresa['rut'];
    $nameEmpresa  = $arrInfoEmpresa['nombre'];
    $razonSocial  = $arrInfoEmpresa['razon_social'];
    $telEmpresa   = $arrInfoEmpresa['telefono'];
    $emailEmpresa = $arrInfoEmpresa['email'];
    $dirEmpresa   = $arrInfoEmpresa['direccion'];
    $iva          = $arrInfoEmpresa['iva'];
  }
}


// Quey para recuperar los datos y armar el dashboard
$query_dash = mysqli_query($conection, "CALL dataDashboard();");
$result_dash = mysqli_num_rows($query_dash);

if ($result_dash > 0) {
  $data_dash = mysqli_fetch_assoc($query_dash);
  mysqli_close($conection);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?php include_once 'includes/scripts.php'; ?>
  <title>Sistema Ventas</title>
</head>
<body>
  <?php include_once 'includes/header.php'; ?>
  <section id="container">
    <div class="divContainer">
      <div>
        <h1 class="titlePanelControl">Panel de Control</h1>
      </div>
      <div class="dashboard">
      <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { ?>
        <a href="lista_usuario.php">
          <i class="fa-solid fa-user"></i>
          <p>
            <strong>Usuarios</strong><br>
            <span><?=$data_dash['usuarios']; ?></span>
          </p>
        </a>
      <?php } ?>
        <a href="lista_clientes.php">
          <i class="fa-solid fa-user-tie"></i>
          <p>
            <strong>Clientes</strong><br>
            <span><?=$data_dash['clientes']; ?></span>
          </p>
        </a>
      <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { ?>
        <a href="lista_proveedor.php">
          <i class="fa-solid fa-briefcase"></i>
          <p>
            <strong>Proveedores</strong><br>
            <span><?=$data_dash['proveedores']; ?></span>
          </p>
        </a>
      <?php } ?>
        <a href="lista_productos.php">
          <i class="fa fa-cubes"></i>
          <p>
            <strong>Productos</strong><br>
            <span><?=$data_dash['productos']; ?></span>
          </p>
        </a>
      <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { ?>
        <a href="ventas.php">
          <i class="fa-solid fa-hand-holding-dollar"></i>
          <p>
            <strong>Ventas</strong><br>
            <span><?=$data_dash['ventas']; ?></span>
          </p>
        </a>
      <?php } ?>
      </div>
    </div>
    <div class="divInfoSistema">
      <div>
        <h1 class="titlePanelControl">Configuracion</h1>
      </div>
      <div class="containerPerfil">
        <div class="containerDataUser">
          <div class="logoUser">
            <img src="img/users.jpg" alt="Imagen Usuarios">
          </div>
          <div class="divDataUser">
            <h4>Informacion Personal</h4>
            <div>
              <label>Nombre:</label><span><?= $_SESSION['nombre']; ?></span>
            </div>
            <div>
              <label>Correo:</label><span><?= $_SESSION['email']; ?></span>
            </div>

            <h4>Datos Usuario</h4>
            <div>
              <label>Rol:</label><span><?= $_SESSION['rol_name']; ?></span>
            </div>
            <div>
              <label>Usuario:</label><span><?= $_SESSION['user']; ?></span>
            </div>
          <?php if ($_SESSION['rol'] != 1) { ?>
            <h4>Cambiar contraseña</h4>
            <form action="" method="post" name="frmChangePass" id="frmChangePass">
              <div>
                <input type="password" name="txtPassUser" id="txtPassUser" placeholder="contraseña actual" required>
              </div>
              <div>
                <input class="newPass" type="password" name="txtNewPassUser" id="txtNewPassUser" placeholder="Nueva contraseña" required>
              </div>
              <div>
                <input class="newPass" type="password" name="txtPassConfirm" id="txtPassConfirm" placeholder="Confirmar contraseña" required>
              </div>
              <div class="alertChangePass" style="display: none;">
              </div>
              <div>
                <button type="submit" class="btn_save btnChangePass"><i class="fas fa-key"></i> Cambiar contraseña</button>
              </div>
            </form>
          <?php } ?>
          </div>
        </div>
        <?php if ($_SESSION['rol'] == 1) { ?>
        <div class="containerDataEmpresa">
          <div class="logoEmpresa">
            <img src="img/empresa.jpg" alt="Imagen Empresa">
          </div>
          <h4>Datos de la empresa</h4>
          <form action="" method="post" name="frmEmpresa" id="frmEmpresa">
            <input type="hidden" name="action" value="updateDataEmpresa">
            <div>
              <label>RUT:</label><input type="text" name="txtRut" id="txtRut" placeholder="RUT de la Empresa" value="<?= $rut; ?>" required>
            </div>
            <div>
              <label>Nombre:</label><input type="text" name="txtNombre" id="txtNombre" placeholder="Nombre Fantasia" value="<?= $nameEmpresa; ?>" required>
            </div>
            <div>
              <label>Razon Social:</label><input type="text" name="txtRSocial" id="txtRSocial" placeholder="Razon Social" value="<?= $razonSocial; ?>">
            </div>
            <div>
              <label>Telefono:</label><input type="text" name="txtTelEmpresa" id="txtTelEmpresa" placeholder="Numero de contacto" value="<?= $telEmpresa; ?>" required>
            </div>
            <div>
              <label>Correo Electronico:</label><input type="email" name="txtEmailEmpresa" id="txtEmailEmpresa" placeholder="Correo Electronico" value="<?= $emailEmpresa; ?>" required>
            </div>
            <div>
              <label>Direccion:</label><input type="text" name="txtDirEmpresa" id="txtDirEmpresa" placeholder="Direccion de la empresa" value="<?= $dirEmpresa; ?>" required>
            </div>
            <div>
              <label>IVA (%):</label><input type="text" name="txtIva" id="txtIva" placeholder="Impuesto al valor Agregado (IVA)" value="<?= $iva; ?>" required>
            </div>

            <div class="alertFormEmpresa" style="display: none;"></div>
            <div>
              <button type="submit" class="btn_save btnChangePass"><i class="fa-regular fa-floppy-disk"></i> Guardar datos</button>
            </div>
          </form>
        </div>
    <?php } ?>
      </div>
    </div>
  </section>
  <?php include_once 'includes/footer.php'; ?>
</body>
</html>
