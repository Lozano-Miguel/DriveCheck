<?php

// Configurações da base de dados
$host = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "escola_conducao_pap"; 
$port = 3309; 
// Conexão com o banco de dados
$conn = new mysqli($host, $username, $password, $database, $port);

// Verifica a conexão
if ($conn->connect_error) {
    // Se houver falha na conexão, a execução do script é interrompida e uma mensagem de erro é exibida
    die("Falha na conexão com a base de dados: " . $conn->connect_error);
}

?>
