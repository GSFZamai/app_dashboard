<?php 

    //classe Dashboard
    class Dashboard {

        public $data_inicio;
        public $data_fim;
        public $numeroVendas;
        public $totalVendas;
        public $clientesAtivos;
        public $clientesInativos;
        public $totalReclamacoes;
        public $totalElogios;
        public $totalSugestoes;
        public $totalDespesas;

        public function __get($atributo) {
            return $this->$atributo;
        }

        public function __set($atributo, $valor) {
            $this->$atributo = $valor;
            return $this;
        }

    }

    //classe de conexão
    class Conexao {
        private $host = 'localhost';
        private $dbname = 'dashboard';
        private $user = 'root';
        private $pass = '';

        public function conectar() {
            try {

                $conexao = new PDO(
                    "mysql:host=$this->host;dbname=$this->dbname",
                    "$this->user",
                    "$this->pass"
                );

                $conexao->exec('set charset set utf8');

                return $conexao;
            }catch(PDOException $e) {
                echo '<p>'.$e->getMessege().'</p>';
            }
        }
    }


    //classe Bd (model)
    class Bd {
        private $conexao;
        private $dashboard;

        public function __construct(Conexao $conexao, Dashboard $dashboard) {
            $this->conexao = $conexao->conectar();
            $this->dashboard = $dashboard;
        }

        public function getNumeroVendas() { 
            $query = "
                SELECT
                    COUNT(*) as numeroVendas
                FROM
                    tb_vendas
                WHERE
                    data_venda between :data_inicio and :data_fim
            ";

            $stmt = $this->conexao->prepare($query);
            $stmt->bindValue(':data_inicio', $this->dashboard->data_inicio);
            $stmt->bindValue(':data_fim', $this->dashboard->data_fim);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ)->numeroVendas;
    
        }

        public function getTotalVendas() {
            $query = "
                SELECT
                    SUM(total) as totalVendas
                FROM 
                    tb_vendas
                WHERE
                    data_venda between :data_inicio and :data_fim
            ";

            $stmt = $this->conexao->prepare($query);
            $stmt->bindValue(':data_inicio', $this->dashboard->data_inicio);
            $stmt->bindValue(':data_fim', $this->dashboard->data_fim);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ)->totalVendas;
        
        
        }

        public function getClientes($status) {
            $query = "
                SELECT
                    COUNT(*) as clientes
                FROM
                    tb_clientes
                WHERE
                    cliente_ativo = $status
            ";

            $stmt = $this->conexao->prepare($query);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ)->clientes;
        }

        public function getTotalContato($tipo_contato) {
            $query = "
                SELECT
                    COUNT(*) as totalContato
                FROM
                    tb_contatos
                WHERE
                    tipo_contato = $tipo_contato
            ";

            $stmt = $this->conexao->prepare($query);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ)->totalContato;
        }

        public function getTotalDespesas() {
            $query = "
                SELECT
                    SUM(total) as totalDespesas
                FROM
                    tb_despesas
                WHERE
                    data_despesa
                BETWEEN
                    :data_inicio
                AND
                    :data_fim
            ";

            $stmt = $this->conexao->prepare($query);
            $stmt->bindValue(':data_inicio', $this->dashboard->data_inicio);
            $stmt->bindValue(':data_fim', $this->dashboard->data_fim);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ)->totalDespesas;
        }

    }

    $competencia = explode('-', $_GET['competencia']);
    $mes = $competencia[1];
    $ano = $competencia[0];
    $dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

    $dashboard = new Dashboard;
    $dashboard->__set('data_inicio', "$ano-$mes-01")
              ->__set('data_fim', "$ano-$mes-$dias_mes");

    $conexao = new Conexao;    

    $bd = new Bd($conexao, $dashboard);

    $dashboard->__set('numeroVendas', $bd->getNumeroVendas())
              ->__set('totalVendas', $bd->getTotalVendas())
              ->__set('clientesAtivos', $bd->getClientes('true'))
              ->__set('clientesInativos', $bd->getClientes('false'))
              ->__set('totalReclamacoes', $bd->getTotalContato(1))
              ->__set('totalElogios', $bd->getTotalContato(2))
              ->__set('totalSugestoes', $bd->getTotalContato(3))
              ->__set('totalDespesas', $bd->getTotalDespesas());

    echo json_encode($dashboard);
?>