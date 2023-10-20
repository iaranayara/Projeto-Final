CREATE DATABASE store_db;

USE store_db;

CREATE TABLE Produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  quantidade INT NOT NULL,
  preco DECIMAL(10,2) NOT NULL,
  descricao VARCHAR(255) NOT NULL
);

CREATE TABLE Utilizadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  morada VARCHAR(255) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  funcao VARCHAR(255) NOT NULL,
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Encomendas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_utilizador INT NOT NULL,
  id_produto INT NOT NULL,
  quantidade INT NOT NULL,
  preco_total DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (id_utilizador) REFERENCES Utilizadores(id),
  FOREIGN KEY (id_produto) REFERENCES Produtos(id),
  nome VARCHAR(255) NOT NULL,
  data_nascimento VARCHAR(255) NOT NULL,
  morada VARCHAR(255) NOT NULL
);