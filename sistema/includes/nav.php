<nav>
  <ul>
    <li><a href="principal.php"><i class="fa-solid fa-house-chimney"></i> Inicio</a></li>
  <?php if ($_SESSION['rol'] == 1) { ?>
    <li class="principal"><a href=""><i class="fa-solid fa-users"></i> Usuarios</a>
      <ul>
        <li><a href="registro_usuario.php"><i class="fa-solid fa-user-plus"></i> Nuevo Usuario</a></li>
        <li><a href="lista_usuario.php"><i class="fa-solid fa-users"></i> Lista de Usuarios</a></li>
      </ul>
    </li>
  <?php } ?>
    <li class="principal">
      <a href="#"><i class="fa-solid fa-user-tie"></i> Clientes</a>
      <ul>
        <li><a href="registro_cliente.php"><i class="fa-solid fa-user-plus"></i> Nuevo Cliente</a></li>
        <li><a href="lista_clientes.php"><i class="fa-solid fa-users"></i> Lista de Clientes</a></li>
      </ul>
    </li>
    <li class="principal">
        <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { ?>
      <a href="#"><i class="fa-solid fa-briefcase"></i> Proveedores</a>
      <ul>
        <li><a href="registro_proveedor.php"><i class="fa-solid fa-handshake"></i> Nuevo Proveedor</a></li>
        <li><a href="lista_proveedor.php"><i class="fa-solid fa-clipboard-list"></i> Lista de Proveedores</a></li>
      </ul>
    <?php }  ?>
    </li>
    <li class="principal">
      <a href="#"><i class="fa fa-cubes"></i> Productos</a>
      <ul>
    <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) { ?>
        <li><a href="registro_productos.php"><i class="fa-solid fa-box-open"></i> Nuevo Producto</a></li>
    <?php }  ?>
        <li><a href="lista_productos.php"><i class="fa fa-cubes"></i> Lista de Productos</a></li>
      </ul>
    </li>
    <?php if ($_SESSION['rol'] != 3) { ?>
    <li class="principal">
      <a href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Facturas</a>
      <ul>
        <li><a href="nueva_venta.php"><i class="fa-solid fa-file"></i> Nuevo Factura</a></li>
        <li><a href="ventas.php"><i class="fa-solid fa-hand-holding-dollar"></i> Ventas</a></li>
      </ul>
    </li>
    <?php }  ?>
  </ul>
</nav>
