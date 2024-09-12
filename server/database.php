<?php

class Database
{

    private $db_host;
    private $db_name;
    private $db_username;
    private $db_password;
    private $conn;
    private $data;

    public function __construct($data = [])
    {
        $this->db_host = "localhost";
        $this->db_name = "sorteo";
        $this->db_username = "root";
        $this->db_password = "";
        $this->data = $data;
        $this->DBConnection();
    }



    private function DBConnection()
    {

        try {
            $this->conn = new PDO("mysql:host=$this->db_host;dbname=$this->db_name", $this->db_username, $this->db_password);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo json_encode(["error" => true, "msg" => $e->getMessage()]);
        }
    }

    public function getComprador()
    {
        try {
            $query = "SELECT * FROM comprador WHERE cedula='" . $this->data['payer']['cedula'] . "'";
            $response = $this->conn->query($query);

            print($response);

            if ($response->rowCount() > 0)
                return ["error" => false, "data" => $response->fetch(PDO::FETCH_ASSOC)];
            else
                return ["error" => true];
        } catch (PDOException $e) {
            print_r($e->getMessage());

            return ["error" => false];
            $this->closeConnection();
        }
    }

    public function existsComprador()
    {

        try {
            $query = "SELECT * FROM comprador WHERE cedula='" . $this->data['payer']['cedula'] . "'";
            $response = $this->conn->query($query);

            print_r($response);

            if ($response->rowCount() > 0)
                return ["error" => false, "exists" => true];
            else
                return ["error" => false, "exists" => false];
        } catch (PDOException $e) {
            print_r($e->getMessage());
            return ["error" => true, "exists" => false];
            $this->closeConnection();
        }
    }

    public function createComprador()
    {
        try {
            $query = "INSERT INTO comprador (cedula, nombre, apellido, correo, telefono) VALUES ('" . $this->data['payer']['cedula'] . "', '" . $this->data['payer']['name'] . "', '" . $this->data['payer']['surname'] . "', '" . $this->data['payer']['email'] . "', '" . $this->data['payer']['phone'] . "')";

            $response = $this->conn->query($query);

            print_r($response);

            if ($response)
                return ["error" => false, "created" => true];
            else
                return ["error" => false, "created" => false];
        } catch (PDOException $e) {

            print_r($e->getMessage());

            return ["error" => true, "created" => false];
            $this->closeConnection();
        }
    }

    public function updateComprador()
    {
    }

    public function createVenta()
    {

        try {

            $query = "SELECT id FROM comprador WHERE cedula=" . $this->data['payer']['cedula'] . "";
            $response = $this->conn->query($query);

            $id = $response->fetch(PDO::FETCH_ASSOC);

            print_r($id);

            $query = "INSERT INTO venta (idPago, canTickets, fechaVenta, comprador) VALUES ('" . $this->data["sell"]["order"] . "', '" . $this->data["sell"]["canTicket"] . "', '" . $this->data["sell"]["dateOrder"] . "', " . $id['id'] . ")";
            $response = $this->conn->query($query);

            print_r($response);

            if ($response)
                return ["error" => false, "created" => true];
            else
                return ["error" => false, "created" => false];
        } catch (PDOException $e) {
            print_r($e->getMessage());
            $this->closeConnection();
            return ["error" => true, "created" => false];
        }
    }

    public function existsVenta(){
        try {
            $query = "SELECT * FROM venta WHERE idPago='" . $this->data['sell']['order'] . "'";
            $response = $this->conn->query($query);

            print_r($response);

            if ($response->rowCount() > 0)
                return ["error" => false, "exists" => true];
            else
                return ["error" => false, "exists" => false];
        } catch (PDOException $e) {
            print_r($e->getMessage());
            return ["error" => true, "exists" => false];
            $this->closeConnection();
        }
    }

    public function getAllTickets()
    {
        try {
            $query = "SELECT ticket FROM ticket";
            $response = $this->conn->query($query);
            $used_tickets = $response->fetchAll(PDO::FETCH_ASSOC);

            return ["error" => false, "tickets" => $used_tickets];
        } catch (Exception $e) {
            print_r($e->getMessage());
            $this->closeConnection();
            return ["error" => true, "created" => false];
        }
    }

    public function createTicket($tickets)
    {
        try {

            $query = "SELECT id FROM venta  WHERE idPago='" . $this->data['sell']['order'] . "'";
            $response = $this->conn->query($query);
            $venta = $response->fetch(PDO::FETCH_ASSOC);

            for ($i = 0; $i < count($tickets); $i++) {
                $value = $tickets[$i];
                $query = "INSERT INTO ticket (ticket, venta) VALUES ('" . $value . "', '" . $venta['id'] . "')";
                $response = $this->conn->query($query);
            }
            return ["error" => false, "created" => true, "tickets" => $tickets];
        } catch (PDOException $e) {
            print_r($e->getMessage());
            $this->closeConnection();
            return ["error" => true, "created" => false];
        }
    }

    public function closeConnection()
    {
        try {
            $this->conn->close();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function moveAll(){
        try {
            $query = "SELECT ticket FROM ticket";
            $response = $this->conn->query($query);
            $values = $response->fetchAll(PDO::FETCH_ASSOC);

            #print_r($values);

            for ($i=0; $i < count($values) ; $i++) { 
                $query = "UPDATE numbers SET vendido='s' WHERE ticket=' " .$values[$i]['ticket']. " '";
                $response = $this->conn->query($query);
            }
        } catch (\Throwable $th) {
            print_r($th->getMessage());
        }
    }

    public function stackNumberColumn(){
        try {
            for ($i=0; $i <=9998 ; $i++) { 
                $query = "INSERT INTO numbers (ticket, vendido) VALUES ('" .$i. "', 'n')";
                $response = $this->conn->query($query);
            }


        } catch (\Throwable $th) {
            print_r($th->getMessage());
        }
    }

    public function noUsedTickets(){
        try {
            $query = "SELECT ticket FROM numbers WHERE vendido='n'";
            $response = $this->conn->query($query);
            $used_tickets = $response->fetchAll(PDO::FETCH_ASSOC);

            return ["error" => false, "tickets" => $used_tickets];
        } catch (Exception $e) {
            print_r($e->getMessage());
            $this->closeConnection();
            return ["error" => true, "created" => false];
        }
    }

    public function ticketSold($tickets){
        try {

            for ($i=0; $i < count($tickets); $i++) { 
                $query = "UPDATE numbers SET vendido='s' WHERE ticket=' " .$tickets[$i]. " '";
                $response = $this->conn->query($query);
            }
            return ["error" => false];
        } catch (\Throwable $th) {
            return ["error" => true];
        }
    }


    public function getInfo(){
        try {
            $query = "SELECT * FROM comprador";

            $response = $this->conn->query($query);
            $compradores = $response->fetchAll(PDO::FETCH_ASSOC);


            if (count($compradores)>0){
                for ($i=0; $i < count($compradores); $i++) { 
                    $query = "SELECT id FROM venta WHERE comprador='".$compradores[$i]['id']."'";
                    $response = $this->conn->query($query);
                    $ventas = $response->fetchAll(PDO::FETCH_ASSOC);


                    if (count($ventas)>0){
                        for ($j=0; $j < count($ventas); $j++) { 
                            $query = "SELECT ticket FROM ticket WHERE venta='" .$ventas[$i]['id']."'";

                            $response = $this->conn->query($query);
                            $tickets = $response->fetchAll(PDO::FETCH_ASSOC);

                            if(count($tickets)>0){
                                for ($k=0; $k < count($tickets); $k++) { 
                                    echo $compradores[$i]['nombre'] ." ". $compradores[$i]['apellido']." Numero: ".$tickets[$k]['ticket'] ."<br>";
                                }
                            }
                        }

                    }
                }
            }


        } catch (Exception $e) {
            //throw $th;
        }
    }
}
