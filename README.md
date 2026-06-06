# Fit Planner 💪

O **Fit Planner** é um sistema web responsivo (Mobile-First) voltado para a gestão unificada de saúde pessoal. Ele centraliza o acompanhamento de atividades físicas (treinos e rotinas) e o controle nutricional (refeições e calorias estimadas).

Este projeto foi desenvolvido como **Projeto Acadêmico de Curricularização** para o curso de Análise e Desenvolvimento de Sistemas da **FATEC Itapetininga**.

---

## ✨ Funcionalidades

- **Autenticação:** Cadastro de usuários e login seguro com hash de senhas.
- **Catálogo de Exercícios:** Lista de exercícios organizados por grupos musculares.
- **Gestão de Rotinas (Templates):** Criação, edição e exclusão de rotinas de treino para preenchimento rápido na academia.
- **Controle de Treinos:** Agendamento de treinos futuros e registro detalhado de execuções reais (carga, repetições, descanso e tempo total).
- **Acompanhamento Nutricional:** Registro diário de refeições com horários e estimativa de calorias consumidas.

---

## 🛠️ Tecnologias Utilizadas

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla / Fetch API)
- **Backend:** PHP 8+ (API RESTful utilizando PDO)
- **Banco de Dados:** MySQL / MariaDB
- **Servidor Local recomendado:** XAMPP

---

## 🚀 Como rodar o projeto localmente

Para rodar o sistema na sua máquina, você precisará configurar um servidor local. Siga o passo a passo abaixo utilizando o **XAMPP**:

### Passo 1: Instalação do XAMPP
1. Baixe e instale o [XAMPP](https://www.apachefriends.org/pt_br/index.html) na sua máquina.
2. Após a instalação, abra o **XAMPP Control Panel**.

### Passo 2: Ligando o Servidor
1. No painel do XAMPP, clique no botão **Start** ao lado de `Apache` (Servidor Web).
2. Clique no botão **Start** ao lado de `MySQL` (Banco de Dados).
3. Ambos devem ficar com o fundo verde, indicando que estão rodando corretamente.

### Passo 3: Posicionando os Arquivos
1. Copie a pasta do projeto (`fit-planner`).
2. Cole a pasta dentro do diretório padrão do XAMPP na sua máquina. Normalmente fica em: 
   `C:\xampp\htdocs\`
   *(Sua estrutura deve ficar assim: `C:\xampp\htdocs\fit-planner\*`)*

### Passo 4: Subindo o Banco de Dados no phpMyAdmin
1. Abra o seu navegador e acesse: `http://localhost/phpmyadmin/`
2. No menu superior, clique na aba **Importar**.
3. Na seção "Arquivo para importar", clique em **Escolher arquivo** e selecione o arquivo `database.sql` que está na raiz da pasta do projeto.
4. Desça até o final da página e clique no botão **Importar** (ou "Executar").
5. *Nota: O script já criará o banco de dados chamado `fit_planner` e todas as 8 tabelas necessárias automaticamente.*

### Passo 5: Configuração de Credenciais (Opcional)
Por padrão, o XAMPP utiliza o usuário `root` e deixa a senha vazia. O projeto já está configurado para isso.
Caso o seu MySQL tenha uma senha diferente, abra o arquivo `config/database.php` no seu editor de código e altere as linhas:
```php
define('DB_USER', 'root');       // Seu usuário MySQL
define('DB_PASS', '');           // Sua senha MySQL (deixe vazio se for padrão)
```

### Passo 6: Acessando o Sistema
Com tudo configurado, abra o seu navegador e acesse:
👉 http://localhost/fit-planner

Crie a sua conta na tela de login e comece a utilizar o sistema!

### 📁 Estrutura do Projeto

```

fit-planner/
│
├── api/                  # Endpoints do Backend em PHP
│   ├── auth.php          # Login e Cadastro
│   ├── exercicios.php    # Catálogo de grupos e exercícios
│   ├── refeicoes.php     # Registro nutricional
│   ├── rotinas.php       # Gestão de templates de treino
│   └── treinos.php       # Registro e agendamento de atividades
│
├── config/
│   └── database.php      # Configuração de conexão PDO com o MySQL
│
├── index.html            # Interface unificada (Frontend)
├── database.sql          # Script de criação do banco de dados (DDL)
└── README.md             # Documentação do projeto

```

### 👨‍💻 Desenvolvedor
Erick Pereira Camargo Estudante de Análise e Desenvolvimento de Sistemas

FATEC Itapetininga - 2026/1
