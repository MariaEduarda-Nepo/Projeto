create database TechFit;
use TechFit;

CREATE TABLE FILIAIS (
ID_FILIAL int primary key auto_increment not null,
Endereco varchar(100),
Telefone varchar(20)
);

CREATE TABLE USUARIOS (
ID_USUARIO int primary key auto_increment not null,
Nome varchar(100),
Email varchar(100),
Senha varchar(50),
Documento varchar(20),
Tipo varchar(20) default 'nenhum',
ID_FILIAL int not null,
FOREIGN KEY(ID_FILIAL) REFERENCES FILIAIS (ID_FILIAL)
);

CREATE TABLE SUPORTE (
ID_CHAT int primary key auto_increment not null,
DataHora_Inicio datetime,
DataHora_Fim datetime,
ID_USUARIO int not null,
FOREIGN KEY(ID_USUARIO) REFERENCES USUARIOS (ID_USUARIO)
);

CREATE TABLE MENSAGEM (
ID_MENSAGEM int primary key auto_increment not null,
Remetente varchar(100),
Conteudo varchar(300),
Data_Envio datetime,
ID_CHAT int not null,
FOREIGN KEY(ID_CHAT) REFERENCES SUPORTE (ID_CHAT)
);

CREATE TABLE PLANOS (
ID_PLANO int primary key auto_increment not null,
NomeP varchar(10),
Preco decimal(3,2),
Duracao int
);

CREATE TABLE PAGAMENTO (
ID_PAGAMENTO int primary key auto_increment not null,
DataP date,
Valor decimal(3,2),
MetodoP varchar(20),
Status varchar(40) default 'pendente',
ID_USUARIO int not null,
ID_PLANO int not null,
FOREIGN KEY(ID_USUARIO) REFERENCES USUARIOS (ID_USUARIO),
FOREIGN KEY(ID_PLANO) REFERENCES PLANOS (ID_PLANO)
);

CREATE TABLE CALENDARIO (
ID_CALENDARIO int primary key auto_increment not null,
Horario_Fim time,
Horario_Inicio time,
Data_Aula date
);

CREATE TABLE AGENDAMENTOS (
ID_AGENDAMENTO int primary key auto_increment not null,
Stats varchar(40) default 'pendente',
ID_USUARIO int not null,
ID_CALENDARIO int not null,
ID_PLANO int not null,
FOREIGN KEY(ID_USUARIO) REFERENCES USUARIOS (ID_USUARIO),
FOREIGN KEY(ID_CALENDARIO) REFERENCES CALENDARIO (ID_CALENDARIO),
FOREIGN KEY(ID_PLANO) REFERENCES PLANOS (ID_PLANO)
)

-- FILIAIS
INSERT INTO FILIAIS (Endereco, Telefone) VALUES 
('Rua A, 100', '1111-1111'),
('Rua B, 200', '2222-2222'),
('Rua C, 300', '3333-3333'),
('Rua D, 400', '4444-4444'),
('Rua E, 500', '5555-5555'),
('Rua F, 600', '6666-6666'),
('Rua G, 700', '7777-7777'),
('Rua H, 800', '8888-8888'),
('Rua I, 900', '9999-9999'),
('Rua J, 1000', '1010-1010');

-- USUARIOS
INSERT INTO USUARIOS (Nome, Email, Senha, Documento, Tipo, ID_FILIAL) VALUES
('Aluno1','a1@mail.com','123','1111','Aluno',1),
('Aluno2','a2@mail.com','123','2222','Aluno',2),
('Aluno3','a3@mail.com','123','3333','Aluno',3),
('Aluno4','a4@mail.com','123','4444','Aluno',4),
('Aluno5','a5@mail.com','123','5555','Aluno',5),
('Professor1','p1@mail.com','123','6666','Professor',1),
('Professor2','p2@mail.com','123','7777','Professor',2),
('Professor3','p3@mail.com','123','8888','Professor',3),
('Professor4','p4@mail.com','123','9999','Professor',4),
('Professor5','p5@mail.com','123','1010','Professor',5);

-- SUPORTE
INSERT INTO SUPORTE (DataHora_Inicio, DataHora_Fim, ID_USUARIO) VALUES
('2025-10-17 08:00:00','2025-10-17 08:30:00',1),
('2025-10-17 09:00:00','2025-10-17 09:30:00',2),
('2025-10-17 10:00:00','2025-10-17 10:30:00',3),
('2025-10-17 11:00:00','2025-10-17 11:30:00',4),
('2025-10-17 12:00:00','2025-10-17 12:30:00',5),
('2025-10-17 13:00:00','2025-10-17 13:30:00',6),
('2025-10-17 14:00:00','2025-10-17 14:30:00',7),
('2025-10-17 15:00:00','2025-10-17 15:30:00',8),
('2025-10-17 16:00:00','2025-10-17 16:30:00',9),
('2025-10-17 17:00:00','2025-10-17 17:30:00',10);

-- MENSAGEM
INSERT INTO MENSAGEM (Remetente, Conteudo, Data_Envio, ID_CHAT) VALUES
('Aluno1','Oi, preciso de ajuda','2025-10-17 08:05:00',1),
('Aluno2','Minha senha não funciona','2025-10-17 09:05:00',2),
('Aluno3','Quero mudar meu plano','2025-10-17 10:05:00',3),
('Aluno4','Como agendar aula?','2025-10-17 11:05:00',4),
('Aluno5','Problema no pagamento','2025-10-17 12:05:00',5),
('Professor1','Mensagem recebida','2025-10-17 13:05:00',6),
('Professor2','Mensagem recebida','2025-10-17 14:05:00',7),
('Professor3','Mensagem recebida','2025-10-17 15:05:00',8),
('Professor4','Mensagem recebida','2025-10-17 16:05:00',9),
('Professor5','Mensagem recebida','2025-10-17 17:05:00',10);

-- PLANOS
INSERT INTO PLANOS (NomeP, Preco, Duracao) VALUES
('Basico',49.90,30),
('Intermediario',79.90,30),
('Avancado',129.90,30),
('Premium',199.90,30),
('Mensal',59.90,30),
('Trimestral',149.90,90),
('Semestral',299.90,180),
('Anual',549.90,365),
('VIP',399.90,30),
('Ultra',499.90,30);

-- PAGAMENTO
INSERT INTO PAGAMENTO (DataP, Valor, MetodoP, Status, ID_USUARIO, ID_PLANO) VALUES
('2025-10-17',49.90,'Cartao','Pendente',1,1),
('2025-10-17',79.90,'Cartao','Pendente',2,2),
('2025-10-17',129.90,'Pix','Pendente',3,3),
('2025-10-17',199.90,'Boleto','Pendente',4,4),
('2025-10-17',59.90,'Cartao','Pendente',5,5),
('2025-10-17',149.90,'Pix','Pendente',6,6),
('2025-10-17',299.90,'Boleto','Pendente',7,7),
('2025-10-17',549.90,'Cartao','Pendente',8,8),
('2025-10-17',399.90,'Pix','Pendente',9,9),
('2025-10-17',499.90,'Boleto','Pendente',10,10);

-- CALENDARIO
INSERT INTO CALENDARIO (Horario_Fim, Horario_Inicio, Data_Aula) VALUES
('09:00:00','08:00:00','2025-10-18'),
('10:00:00','09:00:00','2025-10-18'),
('11:00:00','10:00:00','2025-10-18'),
('12:00:00','11:00:00','2025-10-18'),
('13:00:00','12:00:00','2025-10-18'),
('14:00:00','13:00:00','2025-10-18'),
('15:00:00','14:00:00','2025-10-18'),
('16:00:00','15:00:00','2025-10-18'),
('17:00:00','16:00:00','2025-10-18'),
('18:00:00','17:00:00','2025-10-18');

-- AGENDAMENTOS
INSERT INTO AGENDAMENTOS (Stats, ID_USUARIO, ID_CALENDARIO, ID_PLANO) VALUES
('Pendente',1,1,1),
('Pendente',2,2,2),
('Pendente',3,3,3),
('Pendente',4,4,4),
('Pendente',5,5,5),
('Pendente',6,6,6),
('Pendente',7,7,7),
('Pendente',8,8,8),
('Pendente',9,9,9),
('Pendente',10,10,10);
