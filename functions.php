<?php

// Função para estabelecer a ligação à base de dados
function dbConnection() {
    // Configurações da base de dados
    $host = "localhost"; // Endereço do servidor de base de dados
    $username = "root"; // Nome de utilizador da base de dados
    $password = ""; // Palavra-passe da base de dados
    $database = "escola_conducao_pap"; // Nome da base de dados

  $port = 3309; 
// Conexão com o banco de dados
$conn = new mysqli($host, $username, $password, $database, $port);

    // Verifica a ligação
    if ($conn->connect_error) {
        // Se falhar, mostra uma mensagem de erro
        die("Falha na ligação à base de dados: " . $conn->connect_error);
    }

    return $conn; // Retorna a ligação para ser usada noutras partes do código
}

// Funções para verificar a sessão do utilizador
function verificarSessaoSecretario() {
    session_start();

    // Verifica se a sessão está iniciada e se o tipo de utilizador é 3 (Secretário)
    if (!isset($_SESSION['id_utilizador']) || $_SESSION['fk_tipoutilizador'] != 3) {
        // Se não for um secretário, redireciona para a página de login
        header("Location: index.php");
        exit;
    }
}

function verificarSessaoProfessor() {
    session_start();

    // Verifica se a sessão está iniciada e se o tipo de utilizador é 2 (Professor)
    if (!isset($_SESSION['id_utilizador']) || $_SESSION['fk_tipoutilizador'] != 2) {
        // Se não for um professor, redireciona para a página de login
        header("Location: index.php");
        exit;
    }
}

function verificarSessaoAluno() {
    session_start();

    // Verifica se a sessão está iniciada e se o tipo de utilizador é 1 (Aluno)
    if (!isset($_SESSION['id_utilizador']) || $_SESSION['fk_tipoutilizador'] != 1) {
        // Se não for um aluno, redireciona para a página de login
        header("Location: index.php");
        exit;
    }
}

function terminarSessao() {
    // Verifica se o utilizador solicitou terminar a sessão
    if (isset($_GET['logout'])) {
        // Termina a sessão atual
        session_destroy();

        // Redireciona para a página de login após terminar a sessão
        header("Location: index.php");
        exit;
    }
}
?>
