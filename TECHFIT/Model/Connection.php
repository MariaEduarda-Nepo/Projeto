<?php
class Connection {
    private static $instance = null;

    public static function getInstance() {
        if (!self::$instance) {
            try {
                $host = 'localhost';
                $dbname = 'TECHFIT_charlo';
                $user = 'root';
                $pass = 'senaisp';

                // Conecta no MySQL
                self::$instance = new PDO(
                    "mysql:host=$host;charset=utf8",
                    $user,
                    $pass
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Cria o banco de dados se não existir
                self::$instance->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$instance->exec("USE $dbname");

                // Cria as tabelas
                self::criarTabelas();

            } catch (PDOException $e) {
                die("Erro ao conectar ao MySQL: " . $e->getMessage());
            }
        }
        // Sempre verifica e cria o admin quando necessário
        self::verificarECriarAdmin();
        return self::$instance;
    }

    // -----------------------------
    // CRIAR TODAS AS TABELAS
    // -----------------------------
    private static function criarTabelas() {

        // Tabela de Cadastros (Alunos, Professores e Funcionários)
        // Criar com campos permitindo NULL para admin primário
        self::$instance->exec("
            CREATE TABLE IF NOT EXISTS Cadastros (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo VARCHAR(50) NOT NULL,
                nome VARCHAR(150) NULL,
                email VARCHAR(200) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                cpf VARCHAR(14) NULL,
                telefone VARCHAR(20) NULL,
                datanascimento DATE NULL,
                datacadastro DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Verificar e atualizar estrutura da tabela se necessário (migração)
        self::atualizarEstruturaTabela();
        
        // Criar índice único para CPF apenas se não existir (permite múltiplos NULL)
        try {
            $stmt = self::$instance->query("SHOW INDEX FROM Cadastros WHERE Key_name = 'unique_cpf'");
            if ($stmt->rowCount() == 0) {
                // Não criar índice único se não existir - permite múltiplos NULL
                // O MySQL permite múltiplos NULL em índices únicos
            }
        } catch (PDOException $e) {
            // Ignora se der erro
        }

        // Tabela de Planos
        self::$instance->exec("
            CREATE TABLE IF NOT EXISTS Planos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                preco DECIMAL(10,2) NOT NULL,
                duracao_meses INT NOT NULL,
                ativo TINYINT(1) DEFAULT 1
            )
        ");

        // Tabela de Assinaturas (aluno + plano)
        self::$instance->exec("
            CREATE TABLE IF NOT EXISTS Assinaturas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cadastro_id INT NOT NULL,
                plano_id INT NOT NULL,
                data_inicio DATE NOT NULL,
                data_fim DATE NOT NULL,
                status VARCHAR(50) DEFAULT 'Ativa',
                FOREIGN KEY (cadastro_id) REFERENCES Cadastros(id) ON DELETE CASCADE,
                FOREIGN KEY (plano_id) REFERENCES Planos(id) ON DELETE CASCADE
            )
        ");

        // Tabela de Agendamentos de Aulas (em grupo)
        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS Agendamentos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    aluno_id INT NOT NULL,
                    professor_id INT NOT NULL,
                    modalidade VARCHAR(50) NOT NULL,
                    data_aula DATE NOT NULL,
                    horario TIME NOT NULL,
                    status VARCHAR(50) DEFAULT 'Agendada',
                    data_agendamento DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (aluno_id) REFERENCES Cadastros(id) ON DELETE CASCADE,
                    FOREIGN KEY (professor_id) REFERENCES Cadastros(id) ON DELETE CASCADE
                )
            ");
        } catch (PDOException $e) {
            // Se falhar por causa de foreign key, cria sem foreign key primeiro
            try {
                self::$instance->exec("
                    CREATE TABLE IF NOT EXISTS Agendamentos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        aluno_id INT NOT NULL,
                        professor_id INT NOT NULL,
                        modalidade VARCHAR(50) NOT NULL,
                        data_aula DATE NOT NULL,
                        horario TIME NOT NULL,
                        status VARCHAR(50) DEFAULT 'Agendada',
                        data_agendamento DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } catch (PDOException $e2) {
                // Ignora se já existir
            }
        }
        
        // Adicionar coluna modalidade se não existir (migração)
        try {
            $stmt = self::$instance->query("SHOW COLUMNS FROM Agendamentos LIKE 'modalidade'");
            if ($stmt->rowCount() == 0) {
                self::$instance->exec("ALTER TABLE Agendamentos ADD COLUMN modalidade VARCHAR(50) NOT NULL DEFAULT 'Box' AFTER professor_id");
            }
        } catch (PDOException $e) {
            // Ignora se der erro
        }

        // Migrar horario de TIME para VARCHAR se necessário
        try {
            $stmt = self::$instance->query("SHOW COLUMNS FROM Agendamentos WHERE Field = 'horario' AND Type LIKE 'time'");
            if ($stmt->rowCount() > 0) {
                self::$instance->exec("ALTER TABLE Agendamentos MODIFY COLUMN horario VARCHAR(20) NOT NULL");
            }
        } catch (PDOException $e) {
            // Ignora se der erro
        }

        // Tabela de Lista de Espera
        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS ListaEspera (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    aluno_id INT NOT NULL,
                    modalidade VARCHAR(50) NOT NULL,
                    data_aula DATE NOT NULL,
                    horario VARCHAR(20) NOT NULL,
                    data_inscricao DATETIME DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(50) DEFAULT 'Aguardando',
                    FOREIGN KEY (aluno_id) REFERENCES Cadastros(id) ON DELETE CASCADE
                )
            ");
        } catch (PDOException $e) {
            try {
                self::$instance->exec("
                    CREATE TABLE IF NOT EXISTS ListaEspera (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        aluno_id INT NOT NULL,
                        modalidade VARCHAR(50) NOT NULL,
                        data_aula DATE NOT NULL,
                        horario VARCHAR(20) NOT NULL,
                        data_inscricao DATETIME DEFAULT CURRENT_TIMESTAMP,
                        status VARCHAR(50) DEFAULT 'Aguardando'
                    )
                ");
            } catch (PDOException $e2) {
                // Ignora se já existir
            }
        }

        // Tabela de Avaliações Físicas
        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS AvaliacoesFisicas (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    aluno_id INT NOT NULL,
                    data_avaliacao DATE NOT NULL,
                    peso DECIMAL(5,2),
                    altura DECIMAL(3,2),
                    imc DECIMAL(4,2),
                    percentual_gordura DECIMAL(4,2),
                    massa_muscular DECIMAL(5,2),
                    circunferencia_braco DECIMAL(4,2),
                    circunferencia_cintura DECIMAL(4,2),
                    circunferencia_quadril DECIMAL(4,2),
                    observacoes TEXT,
                    proxima_avaliacao DATE,
                    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (aluno_id) REFERENCES Cadastros(id) ON DELETE CASCADE
                )
            ");
        } catch (PDOException $e) {
            try {
                self::$instance->exec("
                    CREATE TABLE IF NOT EXISTS AvaliacoesFisicas (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        aluno_id INT NOT NULL,
                        data_avaliacao DATE NOT NULL,
                        peso DECIMAL(5,2),
                        altura DECIMAL(3,2),
                        imc DECIMAL(4,2),
                        percentual_gordura DECIMAL(4,2),
                        massa_muscular DECIMAL(5,2),
                        circunferencia_braco DECIMAL(4,2),
                        circunferencia_cintura DECIMAL(4,2),
                        circunferencia_quadril DECIMAL(4,2),
                        observacoes TEXT,
                        proxima_avaliacao DATE,
                        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } catch (PDOException $e2) {
                // Ignora se já existir
            }
        }

        // Tabela de Frequências (Controle de Acesso)
        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS Frequencias (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    aluno_id INT NOT NULL,
                    data_acesso DATE NOT NULL,
                    hora_acesso TIME NOT NULL,
                    tipo_acesso VARCHAR(50) DEFAULT 'Entrada',
                    modalidade VARCHAR(50),
                    observacoes TEXT,
                    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (aluno_id) REFERENCES Cadastros(id) ON DELETE CASCADE
                )
            ");
        } catch (PDOException $e) {
            try {
                self::$instance->exec("
                    CREATE TABLE IF NOT EXISTS Frequencias (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        aluno_id INT NOT NULL,
                        data_acesso DATE NOT NULL,
                        hora_acesso TIME NOT NULL,
                        tipo_acesso VARCHAR(50) DEFAULT 'Entrada',
                        modalidade VARCHAR(50),
                        observacoes TEXT,
                        data_registro DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } catch (PDOException $e2) {
                // Ignora se já existir
            }
        }


        // Tabela de Professor-Modalidade (relaciona professores com modalidades que lecionam)
        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS ProfessorModalidade (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    professor_id INT NOT NULL,
                    modalidade VARCHAR(50) NOT NULL,
                    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (professor_id) REFERENCES Cadastros(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_professor_modalidade (professor_id, modalidade)
                )
            ");
        } catch (PDOException $e) {
            try {
                self::$instance->exec("
                    CREATE TABLE IF NOT EXISTS ProfessorModalidade (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        professor_id INT NOT NULL,
                        modalidade VARCHAR(50) NOT NULL,
                        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_professor_modalidade (professor_id, modalidade)
                    )
                ");
            } catch (PDOException $e2) {
                // Ignora se já existir
            }
        }

        // Criar índices para melhorar performance
        $indices = [
            // Índices para Cadastros
            ["nome" => "idx_cadastros_email", "tabela" => "Cadastros", "sql" => "CREATE INDEX idx_cadastros_email ON Cadastros(email)"],
            ["nome" => "idx_cadastros_cpf", "tabela" => "Cadastros", "sql" => "CREATE INDEX idx_cadastros_cpf ON Cadastros(cpf)"],
            ["nome" => "idx_cadastros_tipo", "tabela" => "Cadastros", "sql" => "CREATE INDEX idx_cadastros_tipo ON Cadastros(tipo)"],
            
            // Índices para Agendamentos
            ["nome" => "idx_agendamentos_aluno", "tabela" => "Agendamentos", "sql" => "CREATE INDEX idx_agendamentos_aluno ON Agendamentos(aluno_id)"],
            ["nome" => "idx_agendamentos_professor", "tabela" => "Agendamentos", "sql" => "CREATE INDEX idx_agendamentos_professor ON Agendamentos(professor_id)"],
            ["nome" => "idx_agendamentos_modalidade", "tabela" => "Agendamentos", "sql" => "CREATE INDEX idx_agendamentos_modalidade ON Agendamentos(modalidade)"],
            ["nome" => "idx_agendamentos_data", "tabela" => "Agendamentos", "sql" => "CREATE INDEX idx_agendamentos_data ON Agendamentos(data_aula)"],
            ["nome" => "idx_agendamentos_horario", "tabela" => "Agendamentos", "sql" => "CREATE INDEX idx_agendamentos_horario ON Agendamentos(horario)"],
            ["nome" => "idx_agendamentos_status", "tabela" => "Agendamentos", "sql" => "CREATE INDEX idx_agendamentos_status ON Agendamentos(status)"],
            ["nome" => "idx_agendamentos_turma", "tabela" => "Agendamentos", "sql" => "CREATE INDEX idx_agendamentos_turma ON Agendamentos(modalidade, data_aula, horario)"],
            
            // Índices para ListaEspera
            ["nome" => "idx_lista_espera_aluno", "tabela" => "ListaEspera", "sql" => "CREATE INDEX idx_lista_espera_aluno ON ListaEspera(aluno_id)"],
            ["nome" => "idx_lista_espera_turma", "tabela" => "ListaEspera", "sql" => "CREATE INDEX idx_lista_espera_turma ON ListaEspera(modalidade, data_aula, horario)"],
            ["nome" => "idx_lista_espera_status", "tabela" => "ListaEspera", "sql" => "CREATE INDEX idx_lista_espera_status ON ListaEspera(status)"]
        ];
        
        foreach ($indices as $indice) {
            try {
                // Verifica se o índice já existe
                $stmt = self::$instance->query("SHOW INDEX FROM " . $indice['tabela'] . " WHERE Key_name = '" . $indice['nome'] . "'");
                if ($stmt->rowCount() == 0) {
                    self::$instance->exec($indice['sql']);
                }
            } catch (PDOException $e) {
                // Ignora se der erro (pode ser que a tabela não exista ainda ou o índice já exista)
            }
        }

        // Inserir planos padrão se não existirem
        $stmt = self::$instance->query("SELECT COUNT(*) FROM Planos");
        if ($stmt->fetchColumn() == 0) {
            self::$instance->exec("
                INSERT INTO Planos (nome, descricao, preco, duracao_meses) VALUES
                ('Plano Básico', 'Acesso à musculação e cardio', 89.90, 1),
                ('Plano Avançado', 'Acesso completo + aulas em grupo + personal', 149.90, 1)
            ");
        }

        // Criar usuário admin padrão será feito em verificarECriarAdmin()
    }

    // -----------------------------
    // VERIFICAR E CRIAR ADMIN
    // -----------------------------
    private static function verificarECriarAdmin() {
        // Verificar se a tabela existe primeiro
        try {
            $stmt = self::$instance->query("SHOW TABLES LIKE 'Cadastros'");
            if ($stmt->rowCount() == 0) {
                // Tabela não existe ainda, não pode criar admin
                return;
            }
        } catch (PDOException $e) {
            // Tabela pode não existir ainda, retorna silenciosamente
            return;
        }
        
        try {
            // Garantir que a estrutura da tabela permite NULL
            self::garantirEstruturaAdmin();
            
            // Verificar se já existe algum funcionário
            $stmtFuncionarios = self::$instance->query("SELECT COUNT(*) FROM Cadastros WHERE tipo = 'Funcionario'");
            $totalFuncionarios = $stmtFuncionarios->fetchColumn();
            
            // Se não existir nenhum funcionário, criar o admin padrão automaticamente
            if ($totalFuncionarios == 0) {
                // Verificar se já existe um admin com esse email (evitar duplicatas)
                $stmtCheck = self::$instance->prepare("SELECT COUNT(*) FROM Cadastros WHERE email = :email");
                $stmtCheck->execute([':email' => 'admin@funcionario.com']);
                $existeAdmin = $stmtCheck->fetchColumn();
                
                if ($existeAdmin == 0) {
                    // Senha padrão: admin123 (hash)
                    $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
                    
                    // Remover constraint UNIQUE do CPF temporariamente se existir
                    try {
                        $stmtIndex = self::$instance->query("SHOW INDEX FROM Cadastros WHERE Key_name = 'unique_cpf'");
                        if ($stmtIndex->rowCount() > 0) {
                            self::$instance->exec("ALTER TABLE Cadastros DROP INDEX unique_cpf");
                        }
                    } catch (PDOException $e) {
                        // Ignora se der erro
                    }
                    
                    // Inserir o admin com nome padrão "Administrador"
                    $stmtAdmin = self::$instance->prepare("
                        INSERT INTO Cadastros (tipo, nome, email, senha, cpf, telefone, datanascimento) 
                        VALUES (:tipo, :nome, :email, :senha, :cpf, :telefone, :datanascimento)
                    ");
                    
                    $resultado = $stmtAdmin->execute([
                        ':tipo' => 'Funcionario',
                        ':nome' => 'Administrador',
                        ':email' => 'admin@funcionario.com',
                        ':senha' => $senhaHash,
                        ':cpf' => NULL,
                        ':telefone' => NULL,
                        ':datanascimento' => NULL
                    ]);
                }
            }
        } catch (PDOException $e) {
            // Se der erro, tenta novamente após garantir estrutura
            try {
                self::garantirEstruturaAdmin();
                
                // Verificar novamente se não há funcionários
                $stmtFuncionarios = self::$instance->query("SELECT COUNT(*) FROM Cadastros WHERE tipo = 'Funcionario'");
                if ($stmtFuncionarios->fetchColumn() == 0) {
                    $stmtCheck = self::$instance->prepare("SELECT COUNT(*) FROM Cadastros WHERE email = :email");
                    $stmtCheck->execute([':email' => 'admin@funcionario.com']);
                    if ($stmtCheck->fetchColumn() == 0) {
                        // Remover constraint UNIQUE do CPF se existir
                        try {
                            $stmtIndex = self::$instance->query("SHOW INDEX FROM Cadastros WHERE Key_name = 'unique_cpf'");
                            if ($stmtIndex->rowCount() > 0) {
                                self::$instance->exec("ALTER TABLE Cadastros DROP INDEX unique_cpf");
                            }
                        } catch (PDOException $e3) {
                            // Ignora
                        }
                        
                        $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
                        $stmtAdmin = self::$instance->prepare("
                            INSERT INTO Cadastros (tipo, nome, email, senha, cpf, telefone, datanascimento) 
                            VALUES (:tipo, :nome, :email, :senha, :cpf, :telefone, :datanascimento)
                        ");
                        $stmtAdmin->execute([
                            ':tipo' => 'Funcionario',
                            ':nome' => 'Administrador',
                            ':email' => 'admin@funcionario.com',
                            ':senha' => $senhaHash,
                            ':cpf' => NULL,
                            ':telefone' => NULL,
                            ':datanascimento' => NULL
                        ]);
                    }
                }
            } catch (PDOException $e2) {
                // Log do erro para debug
                error_log("Erro ao criar admin: " . $e2->getMessage());
            }
        }
    }

    // -----------------------------
    // GARANTIR ESTRUTURA PARA ADMIN
    // -----------------------------
    private static function garantirEstruturaAdmin() {
        try {
            // Permitir NULL em nome, cpf, telefone e datanascimento para admin primário
            $stmtNome = self::$instance->query("SHOW COLUMNS FROM Cadastros WHERE Field = 'nome' AND Null = 'NO'");
            if ($stmtNome->rowCount() > 0) {
                self::$instance->exec("ALTER TABLE Cadastros MODIFY nome VARCHAR(150) NULL");
            }
        } catch (PDOException $e) {
            // Ignora se der erro
        }

        try {
            $stmtCpf = self::$instance->query("SHOW COLUMNS FROM Cadastros WHERE Field = 'cpf' AND Null = 'NO'");
            if ($stmtCpf->rowCount() > 0) {
                // Remover unique constraint temporariamente se existir
                try {
                    $stmtIndex = self::$instance->query("SHOW INDEX FROM Cadastros WHERE Key_name = 'unique_cpf'");
                    if ($stmtIndex->rowCount() > 0) {
                        self::$instance->exec("ALTER TABLE Cadastros DROP INDEX unique_cpf");
                    }
                } catch (PDOException $e) {
                    // Ignora se não existir
                }
                // Modificar para permitir NULL
                self::$instance->exec("ALTER TABLE Cadastros MODIFY cpf VARCHAR(14) NULL");
                // Recriar unique apenas se não existir e houver valores não nulos
                try {
                    $stmtIndexCheck = self::$instance->query("SHOW INDEX FROM Cadastros WHERE Key_name = 'unique_cpf'");
                    if ($stmtIndexCheck->rowCount() == 0) {
                        $stmtNonNull = self::$instance->query("SELECT COUNT(*) FROM Cadastros WHERE cpf IS NOT NULL");
                        if ($stmtNonNull->fetchColumn() > 0) {
                            self::$instance->exec("ALTER TABLE Cadastros ADD UNIQUE KEY unique_cpf (cpf)");
                        }
                    }
                } catch (PDOException $e) {
                    // Ignora se já existir ou der erro
                }
            }
        } catch (PDOException $e) {
            // Ignora se der erro
        }

        try {
            $stmtTel = self::$instance->query("SHOW COLUMNS FROM Cadastros WHERE Field = 'telefone' AND Null = 'NO'");
            if ($stmtTel->rowCount() > 0) {
                self::$instance->exec("ALTER TABLE Cadastros MODIFY telefone VARCHAR(20) NULL");
            }
        } catch (PDOException $e) {
            // Ignora se der erro
        }

        try {
            $stmtData = self::$instance->query("SHOW COLUMNS FROM Cadastros WHERE Field = 'datanascimento' AND Null = 'NO'");
            if ($stmtData->rowCount() > 0) {
                self::$instance->exec("ALTER TABLE Cadastros MODIFY datanascimento DATE NULL");
            }
        } catch (PDOException $e) {
            // Ignora se der erro
        }
    }

    // -----------------------------
    // ATUALIZAR ESTRUTURA DA TABELA (MIGRAÇÃO)
    // -----------------------------
    private static function atualizarEstruturaTabela() {
        try {
            // Verificar se existe a coluna 'documento' (estrutura antiga)
            $stmt = self::$instance->query("SHOW COLUMNS FROM Cadastros LIKE 'documento'");
            if ($stmt->rowCount() > 0) {
                // Migrar dados de 'documento' para 'cpf' se 'cpf' não existir
                $stmt2 = self::$instance->query("SHOW COLUMNS FROM Cadastros LIKE 'cpf'");
                if ($stmt2->rowCount() == 0) {
                    // Adicionar coluna cpf
                    self::$instance->exec("ALTER TABLE Cadastros ADD COLUMN cpf VARCHAR(14) NULL AFTER senha");
                    // Copiar dados de documento para cpf
                    self::$instance->exec("UPDATE Cadastros SET cpf = documento WHERE cpf IS NULL");
                    // Adicionar unique e not null
                    self::$instance->exec("ALTER TABLE Cadastros MODIFY cpf VARCHAR(14) NOT NULL");
                    self::$instance->exec("ALTER TABLE Cadastros ADD UNIQUE KEY unique_cpf (cpf)");
                    // Remover coluna documento
                    self::$instance->exec("ALTER TABLE Cadastros DROP COLUMN documento");
                }
            }

            // Verificar se existe a coluna 'telefone'
            $stmt3 = self::$instance->query("SHOW COLUMNS FROM Cadastros LIKE 'telefone'");
            if ($stmt3->rowCount() == 0) {
                self::$instance->exec("ALTER TABLE Cadastros ADD COLUMN telefone VARCHAR(20) NOT NULL DEFAULT '' AFTER cpf");
            }

            // Permitir NULL em nome, cpf, telefone e datanascimento para admin primário
            try {
                $stmtNome = self::$instance->query("SHOW COLUMNS FROM Cadastros WHERE Field = 'nome' AND Null = 'NO'");
                if ($stmtNome->rowCount() > 0) {
                    self::$instance->exec("ALTER TABLE Cadastros MODIFY nome VARCHAR(150) NULL");
                }
            } catch (PDOException $e) {
                // Ignora se der erro
            }

            try {
                $stmtCpf = self::$instance->query("SHOW COLUMNS FROM Cadastros WHERE Field = 'cpf' AND Null = 'NO'");
                if ($stmtCpf->rowCount() > 0) {
                    // Remover unique constraint temporariamente
                    try {
                        self::$instance->exec("ALTER TABLE Cadastros DROP INDEX unique_cpf");
                    } catch (PDOException $e) {
                        // Ignora se não existir
                    }
                    self::$instance->exec("ALTER TABLE Cadastros MODIFY cpf VARCHAR(14) NULL");
                    // Recriar unique apenas para valores não nulos
                    try {
                        self::$instance->exec("ALTER TABLE Cadastros ADD UNIQUE KEY unique_cpf (cpf)");
                    } catch (PDOException $e) {
                        // Ignora se já existir
                    }
                }
            } catch (PDOException $e) {
                // Ignora se der erro
            }

            try {
                $stmtTel = self::$instance->query("SHOW COLUMNS FROM Cadastros WHERE Field = 'telefone' AND Null = 'NO'");
                if ($stmtTel->rowCount() > 0) {
                    self::$instance->exec("ALTER TABLE Cadastros MODIFY telefone VARCHAR(20) NULL");
                }
            } catch (PDOException $e) {
                // Ignora se der erro
            }

            try {
                $stmtData = self::$instance->query("SHOW COLUMNS FROM Cadastros WHERE Field = 'datanascimento' AND Null = 'NO'");
                if ($stmtData->rowCount() > 0) {
                    self::$instance->exec("ALTER TABLE Cadastros MODIFY datanascimento DATE NULL");
                }
            } catch (PDOException $e) {
                // Ignora se der erro
            }

        } catch (PDOException $e) {
            // Ignora erros de migração se a tabela já estiver atualizada
        }
    }
}