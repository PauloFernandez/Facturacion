<?php
if (empty($_SESSION['active'])) {
  header('location: ../');
}
?>
<header>
  <div class="header">
    <h1>Sistema Facturación</h1>
    <div class="optionsBar">
      <p>Montevideo, <?php echo fechaC(); ?></p>
      <span>|</span>
      <span class="user"><?php echo $_SESSION['nombre'].' - '.$_SESSION['rol']; ?></span>
      <img src="img/user.png" alt="Usuario" class="photouser">
      <a href="salir.php"><img src="img/salir.png" alt="Salir del sistema" class="close" title="Salir"></a>
    </div>
  </div>
<?php include_once 'nav.php'; ?>
</header>
<div class="modal">
  <div class="bodyModal">
    
  </div>
</div>
