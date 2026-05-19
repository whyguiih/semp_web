<?php
// conexao.php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "db_estoque";

try {
    // Cria a conexão no mesmo padrão do Java JDBC
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
    // Configura para mostrar os erros caso o SQL falhe (igual ao e.printStackTrace())
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
}
?>