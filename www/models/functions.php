<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
function database()
{
    $user_password = getenv("MYSQLPASSWORD");
    $user_name = getenv("MYSQLUSER");
    $databasename = getenv("MYSQLDB");
    $hostname = getenv("MYSQLSERVER");
    $database = new PDO("mysql:host=" . $hostname . ";dbname=" . $databasename, $user_name, $user_password);
    $database->query("set names utf8;");
    $database->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    return $database;
}

function add_cliente($identifier, $name_cliente, $email_cliente, $telefono, $direccion, $Altura, $ciudad, $observaciones, $status, $piso, $numero_de_piso)
{
    $bd = database();
    $sentence = $bd->prepare("INSERT INTO customers (tax_identifier, customer_name, email_customer, phone_customer, street, height, location, observaciones, id_status, floor, departament) VALUES (:identifier, :name_cliente, :email_cliente, :telefono, :direccion, :Altura, :ciudad, :observaciones, :status, :piso, :numero_de_piso)");

    $sentence->bindParam(':identifier', $identifier);
    $sentence->bindParam(':name_cliente', $name_cliente);
    $sentence->bindParam(':email_cliente', $email_cliente);
    $sentence->bindParam(':telefono', $telefono);
    $sentence->bindParam(':direccion', $direccion);
    $sentence->bindParam(':Altura', $Altura);
    $sentence->bindParam(':ciudad', $ciudad);
    $sentence->bindParam(':observaciones', $observaciones);
    $sentence->bindParam(':status', $status);
    $sentence->bindParam(':piso', $piso);
    $sentence->bindParam(':numero_de_piso', $numero_de_piso);

    return $sentence->execute();
}
function add_category($name_category, $status)
{
    $bd = database();
    $sentence = $bd->prepare("INSERT INTO categorys (detail, id_status) VALUES (:detail, :id_status)");

    $sentence->bindParam(':detail', $name_category);
    $sentence->bindParam(':id_status', $status);

    return $sentence->execute();
}
function getCustomer($id_customer)
{
    try {
        $bd = database();
        $query = "SELECT id_customer, tax_identifier, customer_name, email_customer, phone_customer, street, height, location, observaciones, id_status, floor, departament FROM customers WHERE id_customer = :id_customer";
        $statement = $bd->prepare($query);
        $statement->bindParam(':id_customer', $id_customer, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener el cliente: " . $e->getMessage();
        return null;
    }
}

function obtenerclientes()
{
    $bd = database();
    $sentence = $bd->query("SELECT id_customer, tax_identifier, customer_name, email_customer, phone_customer, street, height, location, observaciones, id_status, floor, departament FROM customers  WHERE id_status=1");
    return $sentence->fetchAll(PDO::FETCH_ASSOC);
}
function obtenercategorys()
{
    $bd = database();
    $sentence = $bd->query("SELECT id_category, detail, id_status FROM categorys");
    return $sentence->fetchAll(PDO::FETCH_ASSOC);
}
function obtenerusuarios()
{
    $bd = database();
    $sentence = $bd->query("SELECT id_user,email_user,password,phone,date,id_status,id_rol FROM users");
    return $sentence->fetchAll(PDO::FETCH_ASSOC);
}
function obtenerroles()
{
    $bd = database();
    $sentence = $bd->query("SELECT id_rol,detail FROM roles");
    return $sentence->fetchAll(PDO::FETCH_ASSOC);
}
function Updatecliente($id, $name, $email, $cuil, $phone, $street, $height, $floor, $departament, $status, $location, $observaciones)
{
    try {
        $bd = database();

        // Obtener el email y el CUIL actuales del cliente
        $stmt = $bd->prepare("SELECT email_customer, tax_identifier FROM customers WHERE id_customer = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentEmail = $currentData['email_customer'];
        $currentCuil = $currentData['tax_identifier'];

        // Verificar si el email o el CUIL han cambiado antes de hacer la validación
        if ($email !== $currentEmail && emailExistsCliente($email, $id, $bd)) {
            return ['success' => false, 'message' => 'El email ya está en uso.'];
        }

        if ($cuil !== $currentCuil && cuilExistsCliente($cuil, $id, $bd)) {
            return ['success' => false, 'message' => 'El CUIL ya está en uso.'];
        }

        $query = $bd->prepare("UPDATE customers SET 
            tax_identifier = :tax_identifier, 
            customer_name = :customer_name, 
            email_customer = :email_customer, 
            phone_customer = :phone_customer, 
            street = :street, 
            height = :height, 
            location = :location, 
            observaciones = :observations, 
            floor = :floor, 
            departament = :departament,
            id_status = :id_status 
        WHERE id_customer = :id");

        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':tax_identifier', $cuil, PDO::PARAM_STR);
        $query->bindParam(':customer_name', $name, PDO::PARAM_STR);
        $query->bindParam(':email_customer', $email, PDO::PARAM_STR);
        $query->bindParam(':phone_customer', $phone, PDO::PARAM_STR);
        $query->bindParam(':street', $street, PDO::PARAM_STR);
        $query->bindParam(':height', $height, PDO::PARAM_INT);
        $query->bindParam(':floor', $floor, PDO::PARAM_STR);
        $query->bindParam(':departament', $departament, PDO::PARAM_STR);
        $query->bindParam(':location', $location, PDO::PARAM_STR);
        $query->bindParam(':observations', $observaciones, PDO::PARAM_STR);
        $query->bindParam(':id_status', $status, PDO::PARAM_INT);

        $result = $query->execute();

        if ($result) {
            return ['success' => true, 'message' => 'Cliente editado con éxito.'];
        } else {
            return ['success' => false, 'message' => 'Error al editar el cliente.'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error al actualizar el cliente: ' . $e->getMessage()];
    }
}
// Verifica si el email ya está en uso por otro cliente
function emailExistsCliente($email, $id, $bd)
{
    $stmt = $bd->prepare("SELECT COUNT(*) FROM customers WHERE email_customer = ? AND id_customer != ?");
    $stmt->bindParam(1, $email, PDO::PARAM_STR);
    $stmt->bindParam(2, $id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count > 0;
}

// Verifica si el CUIL ya está en uso por otro cliente
function cuilExistsCliente($cuil, $id, $bd)
{
    $stmt = $bd->prepare("SELECT COUNT(*) FROM customers WHERE tax_identifier = ? AND id_customer != ?");
    $stmt->bindParam(1, $cuil, PDO::PARAM_STR);
    $stmt->bindParam(2, $id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count > 0;
}
function Updatecategory($id, $detail, $status)
{
    $bd = database();
    $query = $bd->prepare("UPDATE categorys SET 
           detail = :detail, 
           id_status = :id_status 
       WHERE id_category = :id");

    $query->bindParam(':id', $id);
    $query->bindParam(':detail', $detail);
    $query->bindParam(':id_status', $status);
    $query->execute();
}
function Updateusuario($id, $email, $phone, $status, $password, $id_rol)
{
    $bd = database();
    $query = $bd->prepare("UPDATE users 
                           SET email_user = :email, 
                               phone = :phone, 
                               id_status = :status,
                               password = :password,
                               id_rol = :id_rol
                           WHERE id_user = :id");

    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':phone', $phone, PDO::PARAM_STR);
    $query->bindParam(':status', $status, PDO::PARAM_INT);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);

    return $query->execute();
}

function deletecliente($id)
{
    $bd = database();
    $query = $bd->prepare("UPDATE customers SET id_status = 2 WHERE id_customer = :id");
    $query->bindParam(':id', $id);
    $query->execute();
}
function deletecategory($id)
{
    $bd = database();
    $query = $bd->prepare("UPDATE categorys SET id_status = 2 WHERE id_category = :id");
    $query->bindParam(':id', $id);
    $query->execute();
}
function deleteusuarios($id, $id_rol)
{
    if ($id_rol != 1) {
        $bd = database();
        $query = $bd->prepare("UPDATE users SET id_status = 2 WHERE id_user = :id");
        $query->bindParam(':id', $id);
        $query->execute();
    } else {
        echo "no";
    }
}

function addUsuario($email_user, $phone, $password, $id_status, $id_rol)
{
    $bd = database();
    $sql = "INSERT INTO users (email_user, phone, password,id_status,id_rol) VALUES (:email_user, :phone, :password, :id_status, :id_rol)";
    $stmt = $bd->prepare($sql);
    $stmt->bindParam(':email_user', $email_user);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':id_status', $id_status);
    $stmt->bindParam(':id_rol', $id_rol);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function login($email, $password)
{
    $bd = database();
    $sentence = $bd->prepare("SELECT email_user, password, id_rol, id_status FROM users WHERE email_user = :email");
    $sentence->execute([$email]);
    // Obtiene la fila asociada al correo electrónico proporcionado
    $row = $sentence->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if ($password == $row['password']) {
            return $row;
        }
    }
    return false;
}
function check_existing_supplier($cuil, $email_Proveedor)
{
    $bd = database();
    $sentence = $bd->prepare("SELECT COUNT(*) AS count FROM suppliers WHERE (tax_identifier = ? OR email_supplier = ?) AND id_status != 0");
    $sentence->execute([$cuil, $email_Proveedor]);
    $row = $sentence->fetch(PDO::FETCH_ASSOC);
    return $row['count'] > 0;
}
function insert_suppliers($name_Proveedor, $telefono, $email_Proveedor, $direccion, $altura, $piso, $numero_de_piso, $ciudad, $observaciones, $cuil)
{
    try {
        $bd = database();
        $sentence = $bd->prepare("INSERT INTO suppliers (name_supplier, phone_supplier, email_supplier, street, height, floor, departament, location, id_status, observations, tax_identifier) VALUES (:name_Proveedor, :telefono, :email_Proveedor, :direccion, :altura, :piso, :numero_de_piso, :ciudad, 1, :observaciones, :cuil)");

        $sentence->bindParam(':name_Proveedor', $name_Proveedor, PDO::PARAM_STR);
        $sentence->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sentence->bindParam(':email_Proveedor', $email_Proveedor, PDO::PARAM_STR);
        $sentence->bindParam(':direccion', $direccion, PDO::PARAM_STR);
        $sentence->bindParam(':altura', $altura, PDO::PARAM_STR);
        $sentence->bindParam(':piso', $piso, PDO::PARAM_STR);
        $sentence->bindParam(':numero_de_piso', $numero_de_piso, PDO::PARAM_STR);
        $sentence->bindParam(':ciudad', $ciudad, PDO::PARAM_STR);
        $sentence->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
        $sentence->bindParam(':cuil', $cuil, PDO::PARAM_STR);

        return $sentence->execute();
    } catch (PDOException $e) {
        echo "Error al insertar proveedor: " . $e->getMessage();
        return false;
    }
}

function show_state($table)
{
    $bd = database();
    $query = $bd->prepare("SELECT * FROM $table WHERE id_status = 1");
    $query->execute();
    $list_data = $query->fetchAll();

    return $list_data;
}

function getSupplier($id_supplier)
{
    try {
        $bd = database();
        $query = "SELECT * FROM suppliers WHERE id_supplier = :id_supplier and id_status=1";
        $statement = $bd->prepare($query);
        $statement->bindParam(':id_supplier', $id_supplier, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener el proveedor: " . $e->getMessage();
        return null;
    }
}

// Verifica si el email ya está en uso por otro proveedor
function emailExists($email, $id_supplier, $bd)
{
    $stmt = $bd->prepare("SELECT COUNT(*) FROM suppliers WHERE email_supplier = ? AND id_supplier != ?");
    $stmt->bindParam(1, $email, PDO::PARAM_STR);
    $stmt->bindParam(2, $id_supplier, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count > 0;
}

// Verifica si el CUIL ya está en uso por otro proveedor
function cuilExists($cuil, $id_supplier, $bd)
{
    $stmt = $bd->prepare("SELECT COUNT(*) FROM suppliers WHERE tax_identifier = ? AND id_supplier != ?");
    $stmt->bindParam(1, $cuil, PDO::PARAM_STR);
    $stmt->bindParam(2, $id_supplier, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count > 0;
}

// Función para actualizar los datos de un proveedor en la base de datos
function updateSupplier($id_supplier, $name, $phone, $email, $observation, $tax, $street, $height, $floor, $departament, $location)
{
    try {
        $bd = database();

        // Verificar si el email o el CUIL están en uso por otro proveedor
        if (emailExists($email, $id_supplier, $bd)) {
            return ['success' => false, 'message' => 'El email ya está en uso.'];
        }

        if (cuilExists($tax, $id_supplier, $bd)) {
            return ['success' => false, 'message' => 'El CUIL ya está en uso.'];
        }

        $query = "UPDATE suppliers SET
        name_supplier = :name_supplier, 
        phone_supplier = :phone_supplier, 
        email_supplier = :email_supplier,
        observations = :observations,
        tax_identifier = :tax_identifier,
        street = :street,
        height = :height,
        floor = :floor,
        departament = :departament,
        location = :location
        WHERE id_supplier = :id_supplier";

        $statement = $bd->prepare($query);
        $statement->bindParam(':id_supplier', $id_supplier, PDO::PARAM_INT);
        $statement->bindParam(':name_supplier', $name, PDO::PARAM_STR);
        $statement->bindParam(':phone_supplier', $phone, PDO::PARAM_STR); // Cambiado a STR
        $statement->bindParam(':email_supplier', $email, PDO::PARAM_STR);
        $statement->bindParam(':observations', $observation, PDO::PARAM_STR);
        $statement->bindParam(':tax_identifier', $tax, PDO::PARAM_STR);
        $statement->bindParam(':street', $street, PDO::PARAM_STR);
        $statement->bindParam(':height', $height, PDO::PARAM_INT);
        $statement->bindParam(':floor', $floor, PDO::PARAM_STR);
        $statement->bindParam(':departament', $departament, PDO::PARAM_STR);
        $statement->bindParam(':location', $location, PDO::PARAM_STR);

        $result = $statement->execute();

        if ($result) {
            return ['success' => true, 'message' => 'Proveedor editado con éxito.'];
        } else {
            return ['success' => false, 'message' => 'Error al editar al proveedor.'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error al actualizar el proveedor: ' . $e->getMessage()];
    }
}



function eliminated_Suppliers($table, $id_user)
{
    try {

        $bd = database();

        $query = "UPDATE $table SET id_status = 0 WHERE id_supplier = :id_supplier";
        $updateStatement = $bd->prepare($query);
        $updateStatement->bindParam(':id_supplier', $id_user, PDO::PARAM_INT);
        $updateStatement->execute();
        $rowCount = $updateStatement->rowCount();

        return ($rowCount > 0);
    } catch (PDOException $e) {
        // Manejar errores de base de datos
        echo "Error al actualizar: " . $e->getMessage();
        return false;
    }
}

function getSuppliers($id_supplier)
{
    $bd = database();
    $query = "SELECT * FROM suppliers WHERE id_supplier = :id_supplier and id_status=1";
    $statement = $bd->prepare($query);
    $statement->bindParam(':id_supplier', $id_supplier, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetch(PDO::FETCH_ASSOC);
}

function insert_products($number_product, $name_product, $description, $id_brand, $id_category)
{
    $bd = database();
    $query = "INSERT INTO products (number_product,name_product, description, id_status, id_brand ,id_category) VALUES (:number_product,:name_product, :description, 1, :id_brand, :id_category)";

    $consulta = $bd->prepare($query);

    $consulta->bindParam(':number_product', $number_product, PDO::PARAM_STR);
    $consulta->bindParam(':name_product', $name_product, PDO::PARAM_STR);
    $consulta->bindParam(':description', $description, PDO::PARAM_STR);
    $consulta->bindParam(':id_brand', $id_brand, PDO::PARAM_INT);
    $consulta->bindParam(':id_category', $id_category, PDO::PARAM_INT);

    try {
        if ($consulta->execute()) {
            return true; // Devuelve verdadero si la inserción fue exitosa
        }
    } catch (PDOException $e) {
        echo "Error en la inserción: " . $e->getMessage();
        return false;
    }
}
function insert_sender($id_supplier, $number_remito, $date_remito, $number_invoice, $date_invoice, $id_product, $quantity, $line_number = null)
{
    $bd = database();

    try {
        $bd->beginTransaction();

        $query = "INSERT INTO purchases (id_supplier, remito_number, remito_date, invoice_number, invoice_date, id_product, qty, line_number) 
                  VALUES (:id_supplier, :remito_number, :remito_date, :invoice_number, :invoice_date, :id_product, :qty, :line_number)";
        $consulta = $bd->prepare($query);
        $consulta->bindParam(':id_supplier', $id_supplier, PDO::PARAM_INT);
        $consulta->bindParam(':remito_number', $number_remito, PDO::PARAM_STR);
        $consulta->bindParam(':remito_date', $date_remito, PDO::PARAM_STR);
        $consulta->bindParam(':invoice_number', $number_invoice, PDO::PARAM_STR);
        $consulta->bindParam(':invoice_date', $date_invoice, PDO::PARAM_STR); // Agregado campo 'invoice_date'
        $consulta->bindParam(':id_product', $id_product, PDO::PARAM_INT);
        $consulta->bindParam(':qty', $quantity, PDO::PARAM_INT);
        $consulta->bindParam(':line_number', $line_number, PDO::PARAM_INT);

        if (!$consulta->execute()) {
            throw new Exception("Error al insertar en purchases.");
        }

        $query_update = "UPDATE products SET stock = stock + :qty WHERE id_product = :id_product";
        $consulta_update = $bd->prepare($query_update);
        $consulta_update->bindParam(':qty', $quantity, PDO::PARAM_INT);
        $consulta_update->bindParam(':id_product', $id_product, PDO::PARAM_INT);

        if (!$consulta_update->execute()) {
            throw new Exception("Error al actualizar el stock en products.");
        }

        $bd->commit();
        return true;
    } catch (Exception $e) {
        $bd->rollBack();
        echo "Error en la inserción/actualización: " . $e->getMessage();
        return false;
    }
}

function insert_date_sender($date_invoice)
{
    $bd = database();

    $query_sales = "SELECT id_purchase FROM purchases ORDER BY id_purchase DESC LIMIT 1";
    $consulta_sales = $bd->prepare($query_sales);
    $consulta_sales->execute();
    $id_purchase = $consulta_sales->fetchColumn();

    $query_type = "SELECT id_type FROM motions_type WHERE motion_type = 'Compra'";
    $consulta_type = $bd->prepare($query_type);
    $consulta_type->execute();
    $id_type = $consulta_type->fetchColumn();

    $query = "INSERT INTO motions (date_sales, id_type, id_purchase) 
              VALUES (:date_sales, :id_type, :id_purchase)";
    $consulta = $bd->prepare($query);
    $consulta->bindParam(':date_sales', $date_invoice, PDO::PARAM_STR);
    $consulta->bindParam(':id_type', $id_type, PDO::PARAM_INT);
    $consulta->bindParam(':id_purchase', $id_purchase, PDO::PARAM_INT);

    try {
        if ($consulta->execute()) {
            return true;
        }
    } catch (PDOException $e) {
        echo "Error en la inserción: " . $e->getMessage();
        return false;
    }
}
function getproducts($id_product)
{
    try {
        $bd = database();
        $query = "SELECT * FROM products WHERE id_product = :id_product and id_status=1";
        $statement = $bd->prepare($query);
        $statement->bindParam(':id_product', $id_product, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener el proveedor: " . $e->getMessage();
        return null;
    }
}
function update_products($number_product, $id_product, $name_product, $description)
{
    try {
        $bd = database();
        $query = "UPDATE products SET
        
        number_product = :number_product,
        name_product = :name_product, 
        description = :description
        WHERE id_product = :id_product";

        $consulta = $bd->prepare($query);
        $consulta->bindParam(':id_product', $id_product, PDO::PARAM_INT);

        $consulta->bindParam(':number_product', $number_product, PDO::PARAM_STR);
        $consulta->bindParam(':name_product', $name_product, PDO::PARAM_STR);
        $consulta->bindParam(':description', $description, PDO::PARAM_STR);

        $result = $consulta->execute();

        return $result;
    } catch (PDOException $e) {
        echo "Error al actualizar el proveedor: " . $e->getMessage();
        return false;
    }
}
function eliminated_product($table, $id_user)
{
    try {


        $bd = database();
        $query = "UPDATE $table SET id_status = 0 WHERE id_product = :id_product";
        $updateStatement = $bd->prepare($query);
        $updateStatement->bindParam(':id_product', $id_user, PDO::PARAM_INT);
        $updateStatement->execute();
        $rowCount = $updateStatement->rowCount();

        return ($rowCount > 0);
    } catch (PDOException $e) {
        // Manejar errores de base de datos
        echo "Error al actualizar: " . $e->getMessage();
        return false;
    }
}
function insert_brand($detail)
{
    $bd = database();
    $query = "INSERT INTO brands (detail,id_status) VALUES (:detail, 1)";
    $consulta = $bd->prepare($query);
    $consulta->bindParam(':detail', $detail, PDO::PARAM_STR);

    try {
        if ($consulta->execute()) {
            return true; // Devuelve verdadero si la inserción fue exitosa
        }
    } catch (PDOException $e) {
        echo "Error en la inserción: " . $e->getMessage();
        return false;
    }
}
function getbrands($id_brand)
{
    try {
        $bd = database();
        $query = "SELECT * FROM brands WHERE id_brand = :id_brand and id_status=1";
        $statement = $bd->prepare($query);
        $statement->bindParam(':id_brand', $id_brand, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al obtener el proveedor: " . $e->getMessage();
        return null;
    }
}
function update_brands($id_brand, $detail)
{
    try {
        $bd = database();
        $query = "UPDATE brands SET
        detail = :detail
        WHERE id_brand = :id_brand";

        $consulta = $bd->prepare($query);
        $consulta->bindParam(':id_brand', $id_brand, PDO::PARAM_INT);
        $consulta->bindParam(':detail', $detail, PDO::PARAM_STR);


        $result = $consulta->execute();

        return $result;
    } catch (PDOException $e) {
        echo "Error al actualizar el proveedor: " . $e->getMessage();
        return false;
    }
}
function eliminated_brand($table, $id_brand)
{
    try {

        $bd = database();
        $query = "DELETE FROM $table WHERE id_brand = :id_brand";
        $deleteStatement = $bd->prepare($query);
        $deleteStatement->bindParam(':id_brand', $id_brand, PDO::PARAM_INT);
        $deleteStatement->execute();
        $rowCount = $deleteStatement->rowCount();

        return ($rowCount > 0);
    } catch (PDOException $e) {
        // Manejar errores de base de datos
        return false;
    }
}


function deletecategorys($table, $id_brands)
{
    try {

        $bd = database();

        $query = "DELETE FROM $table WHERE id_category = :id_category";
        $deleteStatement = $bd->prepare($query);
        $deleteStatement->bindParam(':id_category', $id_brands, PDO::PARAM_INT);

        $deleteStatement->execute();
        $rowCount = $deleteStatement->rowCount();

        return ($rowCount > 0);
    } catch (PDOException $e) {
        // Manejar errores de base de datos
        return false;
    }
}

function brand_exists($detail)
{
    try {
        $bd = database();
        $query = "SELECT COUNT(*) FROM brands WHERE detail = :detail";
        $statement = $bd->prepare($query);
        $statement->bindParam(':detail', $detail, PDO::PARAM_STR);
        $statement->execute();
        $count = $statement->fetchColumn();

        return $count > 0;
    } catch (PDOException $e) {
        echo "Error al verificar la marca: " . $e->getMessage();
        return false;
    }
}
function category_exists($name_category)
{
    try {
        $bd = database();
        $query = "SELECT COUNT(*) FROM categorys WHERE detail = :detail";
        $statement = $bd->prepare($query);
        $statement->bindParam(':detail', $name_category, PDO::PARAM_STR);
        $statement->execute();
        $count = $statement->fetchColumn();
        return $count > 0;
    } catch (PDOException $e) {
        echo "Error al verificar la categoría: " . $e->getMessage();
        return false;
    }
}



//Email-Config

function getConnection()
{
    $user_password = getenv("MYSQLPASSWORD");
    $user_name = getenv("MYSQLUSER");
    $databasename = getenv("MYSQLDB");
    $hostname = getenv("MYSQLSERVER");

    try {
        $database = new PDO("mysql:host=" . $hostname . ";dbname=" . $databasename, $user_name, $user_password);
        $database->query("set names utf8;");
        $database->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $database;
    } catch (PDOException $e) {
        echo "Error en la conexión: " . $e->getMessage();
        die();
    }
}

function saveConfig($email, $email_password, $email_receive, $smtp_address, $smtp_port)
{
    $db = getConnection();
    $config = getConfig();

    if ($config) {
        // Actualizar configuración existente
        $query = "UPDATE email_config SET email = :email, email_password = :email_password, email_receive= :email_receive, smtp_address = :smtp_address, smtp_port = :smtp_port WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $config->id);
    } else {
        // Insertar nueva configuración
        $query = "INSERT INTO email_config (email, email_password, email_receive, smtp_address, smtp_port) VALUES (:email, :email_password,:email_receive, :smtp_address, :smtp_port)";
        $stmt = $db->prepare($query);
    }

    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':email_password', $email_password);
    $stmt->bindParam(':email_receive', $email_receive);
    $stmt->bindParam(':smtp_address', $smtp_address);
    $stmt->bindParam(':smtp_port', $smtp_port);

    return $stmt->execute();
}

function getConfig()
{
    $db = getConnection();
    $query = "SELECT * FROM email_config ORDER BY id DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetch();
}


function clients_exists($email_cliente)
{
    try {
        $bd = database();
        $query = "SELECT COUNT(*) FROM customers WHERE email_customer = :detail";
        $statement = $bd->prepare($query);
        $statement->bindParam(':detail', $email_cliente, PDO::PARAM_STR);
        $statement->execute();
        $count = $statement->fetchColumn();
        return $count > 0;
    } catch (PDOException $e) {
        echo "Error al verificar al cliente: " . $e->getMessage();
        return false;
    }
}
function user_exists($email_user)
{
    try {
        $bd = database();
        $query = "SELECT COUNT(*) FROM users WHERE email_user = :detail";
        $statement = $bd->prepare($query);
        $statement->bindParam(':detail', $email_user, PDO::PARAM_STR);
        $statement->execute();
        $count = $statement->fetchColumn();
        return $count > 0;
    } catch (PDOException $e) {
        echo "Error al verificar el usuario: " . $e->getMessage();
        return false;
    }
}

function check_existing_cliente($cuil, $email_Proveedor)
{
    $bd = database();
    $sentence = $bd->prepare("SELECT COUNT(*) AS count FROM customers WHERE (tax_identifier = ? OR email_customer = ?) AND id_status != 0");
    $sentence->execute([$cuil, $email_Proveedor]);
    $row = $sentence->fetch(PDO::FETCH_ASSOC);
    return $row['count'] > 0;
}
function obtenerUsuarioPorEmail($email)
{
    $bd = database();
    $sentence = $bd->prepare("SELECT u.id_user, u.email_user, u.id_rol, r.detail as rol
                              FROM users u
                              JOIN roles r ON u.id_rol = r.id_rol
                              WHERE u.email_user = :email");
    $sentence->bindParam(':email', $email, PDO::PARAM_STR);
    $sentence->execute();
    return $sentence->fetch(PDO::FETCH_ASSOC);
}

function add_serial_number($id_product, $serial_number, $remito_number, $line_number, $id_supplier)
{
    $bd = database();
    $sentence = $bd->prepare("INSERT INTO serial_numbers (id_product, serial_number, remito_number, line_number, id_supplier)
        VALUES (:id_product, :serial_number, :remito_number, :line_number, :id_supplier)
    ");

    $sentence->bindParam(':id_product', $id_product);
    $sentence->bindParam(':serial_number', $serial_number);
    $sentence->bindParam(':remito_number', $remito_number);
    $sentence->bindParam(':line_number', $line_number);
    $sentence->bindParam(':id_supplier', $id_supplier);  // Agregar esta línea

    return $sentence->execute();
}

function get_serial_numbers($id_product, $remito_number, $id_supplier)
{
    $bd = database();
    $sentence = $bd->prepare("SELECT id_product, serial_number, remito_number, id_supplier, line_number
                              FROM serial_numbers
                              WHERE id_product = :id_product 
                              AND remito_number = :remito_number 
                              AND id_supplier = :id_supplier");

    $sentence->bindParam(':id_product', $id_product);
    $sentence->bindParam(':remito_number', $remito_number);
    $sentence->bindParam(':id_supplier', $id_supplier);

    $sentence->execute();

    // Verificar si se obtuvieron resultados
    $results = $sentence->fetchAll(PDO::FETCH_ASSOC);
    error_log(print_r($results, true));  // Imprimir en el log para verificar

    return $results;
}
function update_serial_number($id_product, $serial_number, $remito_number, $id_supplier, $line_number)
{
    $bd = database();
    $sentence = $bd->prepare("
        UPDATE serial_numbers 
        SET serial_number = :serial_number
        WHERE id_product = :id_product 
          AND remito_number = :remito_number 
          AND id_supplier = :id_supplier
          AND line_number = :line_number
    ");

    // Enlazar parámetros
    $sentence->bindParam(':id_product', $id_product);
    $sentence->bindParam(':serial_number', $serial_number);
    $sentence->bindParam(':remito_number', $remito_number);
    $sentence->bindParam(':id_supplier', $id_supplier);
    $sentence->bindParam(':line_number', $line_number);

    return $sentence->execute();
}
function obtenerFechasLimite()
{
    $today = date('Y-m-d');
    $minDate = date('Y-m-d', strtotime('-7 days'));
    $maxDate = date('Y-m-d', strtotime('+7 days'));

    return [
        'today' => $today,
        'maxDate' => $maxDate,
        'minDate' => $minDate
    ];
}
function insert_sales($id_customer, $sales_number, $id_product, $quantity)
{
    $bd = database();

    // Seleccionamos el id_status correspondiente al estado 'Despacho'
    $query_status = "SELECT id_status FROM status WHERE detail = 'Despacho'";
    $consulta_status = $bd->prepare($query_status);
    $consulta_status->execute();
    $id_status = $consulta_status->fetchColumn();

    if (!$id_status) {
        echo "Error: No se encontró el estado 'Despacho'";
        return false;
    }

    // Inserción en la tabla de ventas
    $query = "INSERT INTO sales (id_customer, sales_number, id_product, quantity, id_status) 
              VALUES (:id_customer, :sales_number, :id_product, :quantity, :id_status)";

    $consulta = $bd->prepare($query);
    $consulta->bindParam(':id_customer', $id_customer, PDO::PARAM_INT);
    $consulta->bindParam(':sales_number', $sales_number, PDO::PARAM_STR);
    $consulta->bindParam(':id_product', $id_product, PDO::PARAM_INT);
    $consulta->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $consulta->bindParam(':id_status', $id_status, PDO::PARAM_INT); // Se agrega el id_status

    try {
        if ($consulta->execute()) {
            return true; // Devuelve verdadero si la inserción fue exitosa
        }
    } catch (PDOException $e) {
        echo "Error en la inserción: " . $e->getMessage();
        return false;
    }
}

function check_remito_exists($number_remito)
{

    $bd = database();
    $query = $bd->prepare("SELECT COUNT(*) FROM purchases WHERE remito_number = :remito_number");
    $query->bindParam(':remito_number', $number_remito);
    $query->execute();

    return $query->fetchColumn() > 0;
}
function insert_date_sales($date_sales)
{
    $bd = database();
    $query_sales = "SELECT id_sales FROM sales ORDER BY id_sales DESC LIMIT 1";
    $consulta_sales = $bd->prepare($query_sales);
    $consulta_sales->execute();
    $id_sales = $consulta_sales->fetchColumn();

    $query_type = "SELECT id_type FROM motions_type WHERE motion_type = 'Venta'";
    $consulta_type = $bd->prepare($query_type);
    $consulta_type->execute();
    $id_type = $consulta_type->fetchColumn();

    $query = "INSERT INTO motions (date_sales, id_type, id_sales) 
              VALUES (:date_sales, :id_type, :id_sales)";
    $consulta = $bd->prepare($query);
    $consulta->bindParam(':date_sales', $date_sales, PDO::PARAM_STR);
    $consulta->bindParam(':id_type', $id_type, PDO::PARAM_INT);

    $consulta->bindParam(':id_sales', $id_sales, PDO::PARAM_INT);

    // Ejecutar la inserción
    try {
        if ($consulta->execute()) {
            return true;
        }
    } catch (PDOException $e) {
        echo "Error en la inserción: " . $e->getMessage();
        return false;
    }
}

function obtener_number_sales()
{
    $bd = database();

    $sentence = $bd->query("SELECT sales_number FROM sales ORDER BY sales_number DESC LIMIT 1");
    $result = $sentence->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['sales_number'])) {
        return $result['sales_number'] + 1;
    } else {
        return 1;
    }
}
function update_product_stock($id_product, $quantity_sold)
{
    try {
        $bd = database();
        // convierto quantity_sold a entero por que en la bd esta en int 
        $quantity_sold = (int)$quantity_sold;
        $id_product = (int)$id_product;
        if (empty($id_product) || $quantity_sold <= 0) {
            throw new Exception("El ID del producto o la cantidad son inválidos.");
        }

        $query = "UPDATE products SET stock = stock - :quantity_sold WHERE id_product = :id_product AND stock >= :min_stock";
        $stmt = $bd->prepare($query);
        $stmt->bindValue(':quantity_sold', $quantity_sold, PDO::PARAM_INT);
        $stmt->bindValue(':min_stock', $quantity_sold, PDO::PARAM_INT);
        $stmt->bindValue(':id_product', $id_product, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new PDOException("Error al ejecutar la consulta de actualización de stock: " . $errorInfo[2]);
        }
        return true;
    } catch (Exception $e) {
        echo "Error en la actualización del stock: " . $e->getMessage();
        return false;
    }
}
function add_custommer_sale($identifier, $name_cliente, $email_cliente, $telefono = null, $direccion = null, $altura = null, $ciudad = null, $piso = null, $observaciones = null, $status, $departamento = null)
{
    $bd = database();

    $sentence = $bd->prepare("INSERT INTO customers (tax_identifier, customer_name, email_customer, phone_customer, street, height, location, floor, observaciones, id_status, departament) 
        VALUES (:identifier, :name_cliente, :email_cliente, :telefono, :direccion, :altura, :ciudad, :piso, :observaciones, :status, :departamento)");

    $sentence->bindParam(':identifier', $identifier);
    $sentence->bindParam(':name_cliente', $name_cliente);
    $sentence->bindParam(':email_cliente', $email_cliente);
    $sentence->bindValue(':telefono', !empty($telefono) ? $telefono : null, PDO::PARAM_STR);
    $sentence->bindValue(':direccion', !empty($direccion) ? $direccion : null, PDO::PARAM_STR);
    $sentence->bindValue(':altura', !empty($altura) ? $altura : null, PDO::PARAM_STR);
    $sentence->bindValue(':ciudad', !empty($ciudad) ? $ciudad : null, PDO::PARAM_STR);
    $sentence->bindValue(':piso', !empty($piso) ? $piso : null, PDO::PARAM_STR);
    $sentence->bindValue(':observaciones', !empty($observaciones) ? $observaciones : null, PDO::PARAM_STR);
    $sentence->bindValue(':status', !empty($status) ? (int)$status : 1, PDO::PARAM_INT);
    $sentence->bindValue(':departamento', !empty($departamento) ? $departamento : null, PDO::PARAM_STR);

    return $sentence->execute();
}

//DESPACHOS
// Obtener ventas por id_status y mostrar el nombre del cliente en lugar del id_customer
function get_sales_by_status($status_id)
{
    $bd = database();
    $stmt = $bd->prepare("
        SELECT sales.sales_number, customers.customer_name, SUM(sales.quantity) as total_qty 
        FROM sales 
        JOIN customers ON sales.id_customer = customers.id_customer
        WHERE sales.id_status = :status_id 
        GROUP BY sales.sales_number, customers.customer_name
        ORDER BY customers.customer_name ASC, sales.sales_number ASC
    ");
    $stmt->bindParam(':status_id', $status_id, PDO::PARAM_INT);
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result ? $result : [];
}
// Obtener los detalles de una venta (incluyendo cantidad, producto y concatenación de nombre y descripción)
function get_sale_details($sales_number)
{
    $bd = database();
    $stmt = $bd->prepare("SELECT sales.id_product,CONCAT(products.name_product, ' - ', products.description) AS full_product_name, 
            sales.quantity,sales.id_customer FROM sales
        JOIN products ON sales.id_product = products.id_product
        WHERE sales.sales_number = :sales_number
    ");
    $stmt->bindParam(':sales_number', $sales_number, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function update_serial_numbers($id_product, $serial_numbers, $sales_number)
{
    $bd = database();
    $stmt = $bd->prepare("UPDATE serial_numbers 
                           SET used = 1, sales_number = :sales_number, updated_at = NOW() 
                           WHERE id_product = :id_product AND serial_number = :serial_number");

    foreach ($serial_numbers as $serial_number) {
        $stmt->bindParam(':id_product', $id_product);
        $stmt->bindParam(':serial_number', $serial_number);
        $stmt->bindParam(':sales_number', $sales_number);
        if (!$stmt->execute()) {
            return false;  // Si falla la actualización, retornamos false
        }
    }

    return true;  // Si todo va bien, retornamos true
}
// Actualizar el estado de una venta
function update_sales_status($sales_number, $status)
{
    $bd = database();
    // Preparar la consulta SQL para actualizar el id_status de la venta
    $stmt = $bd->prepare("UPDATE sales SET id_status = :status WHERE sales_number = :sales_number");

    // Enlazar los parámetros a la consulta preparada
    $stmt->bindParam(':status', $status, PDO::PARAM_INT); // Asegurarse de que es un entero
    $stmt->bindParam(':sales_number', $sales_number, PDO::PARAM_STR); // Asumimos que sales_number es una cadena

    // Ejecutar la consulta y comprobar si tuvo éxito
    if ($stmt->execute()) {
        return true; // Retornar verdadero si la actualización fue exitosa
    } else {
        return false; // Retornar falso si hubo un error
    }
}
// Obtener los seriales no utilizados para un producto
function get_available_serials($product_id)
{
    $bd = database();
    $stmt = $bd->prepare("SELECT serial_number, created_at FROM serial_numbers WHERE id_product = :id_product AND used = 0");
    $stmt->bindParam(':id_product', $product_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Insertar un nuevo número de serie y marcarlo como utilizado
function add_serial_numbers($product_id, $serial_number)
{
    $bd = database();

    // Obtiene la fecha actual en el formato adecuado
    $created_at = date('Y-m-d H:i:s'); // Ajusta el formato según tu necesidad
    $updated_at = $created_at; // Para 'updated_at', puedes usar la misma fecha

    $stmt = $bd->prepare("INSERT INTO serial_numbers 
        (id_product, serial_number, remito_number, line_number, id_supplier, created_at, updated_at, sales_number, used) 
        VALUES (:id_product, :serial_number, 'Ingreso Manual', 1, 0, :created_at, :updated_at, NULL, 0)");

    $stmt->bindParam(':id_product', $product_id);
    $stmt->bindParam(':serial_number', $serial_number);
    $stmt->bindParam(':created_at', $created_at); // Vincula la fecha de creación
    $stmt->bindParam(':updated_at', $updated_at); // Vincula la fecha de actualización

    return $stmt->execute();
}
// Función para registrar un nuevo despacho (una línea por producto)
function insert_dispatch($sales_number, $customer_id, $qty, $product_name, $serial_numbers)
{
    $bd = database();

    // Concatenar los números de serie en un solo string
    $serial_numbers_string = implode(', ', $serial_numbers);

    // Modificar el nombre del producto para incluir los números de serie
    $full_product_name = $product_name . ' (Seriales: ' . $serial_numbers_string . ')';

    // **Aquí nos aseguramos de que se inserte solo una línea con la cantidad correcta**
    $stmt = $bd->prepare("INSERT INTO dispatches (sales_number, id_customer, qty, product_name) 
                          VALUES (:sales_number, :id_customer, :qty, :product_name)");

    // Vincular los parámetros
    $stmt->bindParam(':sales_number', $sales_number, PDO::PARAM_INT);
    $stmt->bindParam(':id_customer', $customer_id, PDO::PARAM_INT);
    $stmt->bindParam(':qty', $qty, PDO::PARAM_INT);  // Cantidad total vendida, no la cantidad de seriales
    $stmt->bindParam(':product_name', $full_product_name, PDO::PARAM_STR);

    return $stmt->execute();
}

// Función para obtener los datos del remito
function get_remito_data($sales_number)
{
    $bd = database();

    // Consulta para obtener la información de dispatches
    $stmt = $bd->prepare("
        SELECT 
            d.id_dispatch, 
            d.sales_number, 
            d.id_customer, 
            d.dispatch_date, 
            d.qty, 
            d.product_name 
        FROM dispatches d 
        WHERE d.sales_number = :sales_number
    ");
    $stmt->bindParam(':sales_number', $sales_number, PDO::PARAM_INT);
    $stmt->execute();

    // Obtener los datos de dispatches
    $dispatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($dispatches)) {
        return null; // No se encontró información de dispatches
    }

    // Obtener el ID del cliente desde el primer despacho
    $customer_id = $dispatches[0]['id_customer'];

    // Consulta para obtener los datos del cliente
    $stmt = $bd->prepare("
        SELECT 
            id_customer, 
            tax_identifier, 
            customer_name, 
            email_customer, 
            phone_customer, 
            CONCAT(street, ' ', height) AS customer_address, 
            location 
        FROM customers 
        WHERE id_customer = :id_customer
    ");
    $stmt->bindParam(':id_customer', $customer_id, PDO::PARAM_INT);
    $stmt->execute();

    // Obtener los datos del cliente
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'dispatches' => $dispatches,
        'customer' => $customer
    ];
}

function get_purchase_history()
{
    $bd = database();
    $query = $bd->prepare("
SELECT 
    p.remito_number, 
    p.remito_date, 
    p.invoice_number, 
    s.name_supplier, 
    SUM(p.qty) AS total_qty
FROM purchases p
JOIN suppliers s ON p.id_supplier = s.id_supplier
GROUP BY 
    p.remito_number, 
    p.remito_date, 
    p.invoice_number, 
    s.name_supplier
ORDER BY p.remito_date DESC;

    ");
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
function get_product_details_by_remito($remito_number)
{
    $bd = database();
    $query = $bd->prepare("
        SELECT prod.name_product, dp.qty, dp.remito_date
        FROM purchases dp
        JOIN products prod ON dp.id_product = prod.id_product
        WHERE dp.remito_number = :remito_number
        AND dp.qty > 0
    ");
    $query->bindParam(':remito_number', $remito_number);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
function get_sales_history()
{
    $bd = database();
    $query = $bd->prepare("
        SELECT 
        sales.sales_number, 
        customers.customer_name, 
        SUM(sales.quantity) AS total_qty, 
        MAX(motions.date_sales) AS sale_date
        FROM sales
        JOIN customers ON sales.id_customer = customers.id_customer
        LEFT JOIN motions ON sales.id_sales = motions.id_sales
        GROUP BY sales.sales_number, customers.customer_name
        ORDER BY sales.sales_number DESC;
    ");
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
function get_product_details_by_sale($sale_number)
{
    $bd = database();
    $query = $bd->prepare("
        SELECT prod.name_product, ds.quantity
        FROM sales ds
        JOIN products prod ON ds.id_product = prod.id_product
        WHERE ds.sales_number = :sales_number
        AND ds.quantity > 0  
    ");
    $query->bindParam(':sales_number', $sale_number);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
function get_warranty_by_serial_number($serial_number)
{
    $bd = database();
    $query = $bd->prepare("
        SELECT 
            p.name_product, 
            p.description, 
            sn.created_at, 
            s.name_supplier, 
            d.dispatch_date, 
            c.customer_name,
            sn.sales_number,
            sn.remito_number
        FROM serial_numbers sn
        JOIN products p ON sn.id_product = p.id_product
        JOIN suppliers s ON sn.id_supplier = s.id_supplier
        JOIN dispatches d ON sn.sales_number = d.sales_number
        JOIN customers c ON d.id_customer = c.id_customer
        WHERE sn.serial_number = :serial_number
    ");
    $query->bindParam(':serial_number', $serial_number);
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);
}
// Verificar si un número de serie está disponible para un producto
function validate_serial_number($product_id, $serial_number)
{
    $bd = database();
    $stmt = $bd->prepare("SELECT serial_number FROM serial_numbers WHERE id_product = :id_product AND serial_number = :serial_number AND used = 0");
    $stmt->bindParam(':id_product', $product_id);
    $stmt->bindParam(':serial_number', $serial_number);
    $stmt->execute();

    // Si encontramos un registro, significa que el serial está disponible
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
}
function obtenerDatosGraficos()
{
    $bd = database();
    try {
        // Consulta para obtener el producto más vendido
        $query1 = $bd->prepare("SELECT p.name_product, SUM(s.quantity) AS total_vendido
                                 FROM sales s
                                 JOIN products p ON s.id_product = p.id_product
                                 GROUP BY p.name_product
                                 ORDER BY total_vendido DESC
                                 LIMIT 4");
        $query1->execute();
        $productosMasVendidos = $query1->fetchAll(PDO::FETCH_ASSOC);

        // Consulta para obtener las ganancias mensuales
        $query2 = $bd->prepare("SELECT MONTH(remito_date) AS mes, SUM(qty) AS total_vendido
                                 FROM purchases
                                 GROUP BY mes");
        $query2->execute();
        $vendidoEnElMes = $query2->fetchAll(PDO::FETCH_ASSOC);

        // Consulta para obtener el stock actual
        $query3 = $bd->prepare("SELECT name_product, stock FROM products");
        $query3->execute();
        $stockProductos = $query3->fetchAll(PDO::FETCH_ASSOC);

        // Consulta para obtener las ganancias anuales
        $query4 = $bd->prepare("SELECT YEAR(remito_date) AS año, SUM(qty) AS total_anual
                                 FROM purchases
                                 GROUP BY año");
        $query4->execute();
        $gananciasAnuales = $query4->fetchAll(PDO::FETCH_ASSOC);

        return [
            'productosMasVendidos' => $productosMasVendidos,
            'vendidoenelmes' => $vendidoEnElMes,
            'stockProductos' => $stockProductos,
            'gananciasAnuales' => $gananciasAnuales
        ];
    } catch (PDOException $e) {
        // Manejo de excepciones: puedes registrar el error o manejarlo como desees
        return [
            'error' => 'Error al obtener los datos gráficos: ' . $e->getMessage()
        ];
    }
}
function validate_and_upload_image($file, $product_id)
{
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $upload_dir = '../assets/img/';

    // Verifica si hay error en la subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "Error en la subida de archivo.";
    }

    // Verifica el tamaño máximo del archivo
    if ($file['size'] > 5000000) { // 5MB
        return "El archivo es demasiado grande.";
    }

    // Obtiene la extensión del archivo
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);

    // Verifica si la extensión es válida
    if (!in_array($extension, $allowed_extensions)) {
        return "Solo se permiten archivos JPG, JPEG o PNG.";
    }

    // Crea el nuevo nombre de archivo basado en el ID del producto
    $new_filename = $product_id . '.' . $extension;
    $destination = $upload_dir . $new_filename;

    // Mueve el archivo a la carpeta de destino
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination;  // Devuelve la URL o la ruta del archivo de la imagen subida
    } else {
        return "Error al mover el archivo.";
    }
}

function save_card_data($product_id, $image_url, $price)
{
    $db = database(); // Conexión a la base de datos

    // Validar parámetros
    $product_id = intval($product_id);
    $price = floatval($price);
    $image_url = filter_var($image_url, FILTER_SANITIZE_URL);

    // Verifica que el precio sea válido
    if ($price <= 0) {
        return "El precio no es válido.";
    }

    // Insertar o actualizar los datos en la tabla landing_product
    $sql = "INSERT INTO landing_product (product_id, image_url, price)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                image_url = VALUES(image_url),
                price = VALUES(price);";

    $stmt = $db->prepare($sql);
    return $stmt->execute([$product_id, $image_url, $price]);
}
function get_all_products2()
{
    $db = database();
    // Consulta SQL para obtener todos los registros de la tabla `products`
    $sql = "SELECT * FROM products";

    $stmt = $db->query($sql);
    // Retorna los resultados
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function get_all_landing()
{
    $db = database();
    // Consulta SQL para obtener todos los registros de la tabla `products`
    $sql = "SELECT 
products.name_product,
products.description,
products.description,
products.stock,
landing_product.id_landing,
landing_product.image_url,
landing_product.price
FROM landing_product
join products on landing_product.product_id= products.id_product
";

    $stmt = $db->query($sql);
    // Retorna los resultados
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function update_card_data($id_landing, $product_id, $image_url, $price)
{
    $db = database();
    $query = "UPDATE landing_product 
              SET product_id = :product_id, image_url = :image_url, price = :price 
              WHERE id_landing = :id_landing";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':image_url', $image_url);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':id_landing', $id_landing);

    return $stmt->execute();
}


function delete_card_data($id_landing)
{
    $db = database();
    $query = "DELETE FROM landing_product WHERE id_landing = :id_landing";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_landing', $id_landing);

    return $stmt->execute();
}
