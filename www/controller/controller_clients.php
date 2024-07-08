
    <?php
    include_once "../models/functions.php";
    $identifier=$_POST["identifier"];
    $name_cliente=$_POST["name_cliente"];
    $email_cliente=$_POST["email_cliente"];
    $telefono=$_POST["telefono"];
    $direccion=$_POST["direccion"];
    $Altura=$_POST["altura"];
    $ciudad=$_POST["ciudad"];
    $piso=$_POST["piso"];
    
    $observaciones=$_POST["observaciones"];
    $status=1;
    $department=$_POST["department"];
    
    if (check_existing_cliente($identifier, $email_cliente)) {
        echo '<script>
        localStorage.setItem("mensaje", "El cliente ya existe");
        localStorage.setItem("tipo", "error");
        window.location.href = "../views/crud_cliente.php";
            </script>';
    }
    if(add_cliente($identifier, $name_cliente, $email_cliente, $telefono, $direccion, $Altura, $ciudad, $observaciones, $status,$piso,$department))
    {
        
        echo '<script>
        localStorage.setItem("mensaje", "Cliente creado con éxito");
        localStorage.setItem("tipo", "success");
        window.location.href = "../views/crud_cliente.php";
            </script>';          
    }