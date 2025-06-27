## Buenas! 

**Tudo o que precisa está aqui.**

Fiz questão de fazer tudo do zero sem pegar nada pronto do meu arsenal ou copiado do que já trabalho hoje, então tudo aqui é legítimo e unicamente pensado únicamente ao projeto.



### Imagens:

**shell para deixar todo ambiente de pé:**

![image](https://github.com/user-attachments/assets/2fd1b649-f708-4100-8f49-dd0b31237c0f)



**Telescope para acompanhar TUDO o que está rolando:**

**Jobs/Queues**

![image](https://github.com/user-attachments/assets/80906ae4-e8c2-4c78-ae3f-589aea6e48e3)

**Cache Tag**

![image](https://github.com/user-attachments/assets/3b93bce0-f4a6-42f0-863f-02b19e54cbbe)


**E por aí vai...**



# 🚀 Teste Prático - API REST Laravel para Job Applications

Este documento descreve como configurar, executar e utilizar a API REST desenvolvida em Laravel para gerenciamento de vagas de emprego, candidaturas e análise de dados climáticos.

Observação: SÓ de subir o container, o docker já configura tudo, mas precisa aguardar e utilizar o docker compose up sem o detach -d se não o processo de instalação do composer vai ficar em segundo plano e não vai dar pra ver.

## 📋 Requisitos

- Docker e Docker Compose instalados
- Git configurado
- Pelo menos 4GB de RAM disponível

## ⚡ Instalação Manual

### 🔧 Configuração Passo a Passo

**1. Clone o repositório e entre na branch:**
```bash
git clone <repository-url>
cd teste-dev-backend
git checkout TDB/luiz_paulo_escobal
```

**2. Aguarde os containers ficarem prontos:**
```bash
# rode o script shell
chmod +x setup.sh
./setup.sh

# Verifique o status dos containers
docker-compose ps
# Aguarde até todos estarem com status "Up"
``` 

**3. (Opcional) Importe dados climáticos de exemplo:**
```bash
docker-compose exec app-estech php artisan climate:import example.csv --chunk-size=1000 --queue=climate_data
```

**4. Execute os testes para validar a instalação:**
```bash
./run-tests.sh
```

**10. Verifique se está funcionando:**
```bash
curl http://localhost:8000/api/health
```

**Tempo estimado:** 3-15 minutos dependendo da maquina

### 🚀 Acesso após Instalação

Após a conclusão, a API estará disponível em:
- **🌐 API Principal: INFOS** http://localhost:8000
- **🔭 Telescope (Debugging):** http://localhost:8000/telescope
- **📊 Health Check:** http://localhost:8000/api/health

### Importação de Dados Climáticos
```bash
# Importar dados do arquivo example.csv
docker-compose exec app-estech php artisan climate:import example.csv --chunk-size=1000 --queue=climate_data
```

### Solução de Problemas
```bash
# Rebuild completo (quando há mudanças no Dockerfile)
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

## 🚨 Troubleshooting

#### 2. "Falha no build" ou "Container não ficou pronto"
```bash
# Limpar tudo e recomeçar
docker-compose down --volumes --remove-orphans
docker system prune -a

# Reconstruir containers
docker-compose up -d --build

# Reinstalar dependências
docker-compose exec app-estech composer install --no-interaction
docker-compose exec app-estech php artisan key:generate
docker-compose exec app-estech php artisan migrate
docker-compose exec app-estech php artisan db:seed
```

#### 3. "API ainda não está respondendo"
```bash
# Verificar logs
docker-compose logs -f app-estech

# Verificar se o container está rodando
docker-compose ps

# Aguardar mais tempo (primeira vez pode demorar)
curl http://localhost:8000/api/health
```

#### 4. "Falha na instalação das dependências"
```bash
# Tentar instalação manual
docker-compose exec app-estech composer install --no-interaction
# FORA DO CONTAINER 
sudo chmod 777 -R ./vendor

# sudo chmod 755 -R ./vendor se preferir...
composer dump-autoload --optimize

# Se persistir, verificar espaço em disco
df -h
```

#### 5. Porta 8000 já está em uso
```bash
# Verificar o que está usando a porta
sudo netstat -tulpn | grep :8000

# Ou mudar a porta no docker-compose.yml
# services:
#   nginx:
#     ports:
#       - "8080:80"  # Usar porta 8080 ao invés de 8000
```

#### 6. Problemas de Permissão
```bash
# Corrigir permissões do diretório
sudo chown -R $USER:$USER .
chmod +x  run-tests.sh
chmod +x  entrypoint-queue.sh
chmod +x  entrypoint-web.sh
chmod +x  setup.sh

# Dentro do container
docker-compose exec app-estech chmod -R 775 storage bootstrap/cache
```




## 🏗️ Padrões de Design Implementados

A aplicação segue várias boas práticas e padrões de design para garantir código limpo, manutenível e testável:

### Repository Pattern
- **Service Layer**: Classes de serviço (`ClimateDataService`, `JobListingService`) encapsulam a lógica de negócios
- **Separation of Concerns**: Controllers são responsáveis apenas por gerenciar requisições HTTP

### Observer Pattern
- **JobListingObserver**: Monitora alterações nas vagas para invalidar cache automaticamente
- **Event-Driven Architecture**: Utiliza observers do Laravel para reagir a mudanças no modelo

### Factory Pattern
- **Model Factories**: Para geração de dados de teste (`UserFactory`, `JobListingFactory`, `JobApplicationFactory`)
- **Facilita testes**: Criação consistente de dados para testes unitários e de integração

### Service Container (Dependency Injection)
- **Injeção de Dependência**: Controllers e serviços recebem dependências via constructor injection
- **Testabilidade**: Facilita mock de dependências em testes

### Policy Pattern
- **Authorization**: Controle de acesso baseado em políticas (`JobListingPolicy`, `JobApplicationPolicy`)
- **Role-based Access**: Diferentes permissões para recrutadores e candidatos

## 🧰 Componentes Laravel Utilizados

### Core Framework
- **Laravel 11**: Framework base com arquitetura MVC
- **Eloquent ORM**: Para interação com banco de dados e relacionamentos
- **Migrations & Seeders**: Controle de versão do banco de dados

### Autenticação e Autorização
- **Laravel Sanctum**: Sistema de autenticação baseada em tokens
- **Policies**: Controle de autorização granular
- **Middleware**: Proteção de rotas e validação de permissões

### Cache e Performance
- **Cache com Tags Laravel**: Sistema de cache inteligente com tags hierárquicas
- **Granularidade Média**: Balanceamento otimizado entre performance e precisão na invalidação
- **Invalidação Seletiva**: Cache tags permitem invalidação por categorias específicas
- **Eager Loading**: Otimização de consultas com relacionamentos

### Validação e Recursos
- **Form Requests**: Validação estruturada de dados de entrada
- **API Resources**: Padronização de respostas da API
- **Soft Deletes**: Exclusão lógica de registros

### Observabilidade e Debug
- **Laravel Telescope**: Debugging e monitoramento de performance
- **Query Log**: Rastreamento de consultas SQL
- **Application Insights**: Monitoramento de erros e performance

### CLI e Jobs
- **Artisan Commands**: Comandos personalizados para importação de dados
- **Queue Jobs**: Processamento assíncrono de tarefas pesadas
- **Job Chunking**: Processamento de grandes volumes de dados em lotes
- **Supervisor**: Gerenciamento de processos de filas, monitoraa e reinicia automaticamente os workers das filas.



## Estrutura da API

### Usuários

A API suporta dois tipos de usuários:
- **Recrutadores**: Podem criar, editar e excluir vagas de emprego
- **Candidatos**: Podem se candidatar às vagas disponíveis

### Autenticação

A API utiliza Laravel Sanctum para autenticação baseada em tokens. Todos os endpoints protegidos requerem um token válido.

### Endpoints Disponíveis

A API possui endpoints organizados em categorias funcionais. Para uma lista completa e detalhada, consulte a seção [📋 Endpoints Completos da API](#-endpoints-completos-da-api) mais adiante neste documento.

## Funcionalidades Implementadas

- [x] CRUD completo para Usuários, Vagas e Candidaturas
- [x] Paginação em todas as listagens
- [x] Filtragem de resultados
- [x] Soft delete
- [x] Cache para otimização (usando Cache Tags do Laravel com granularidade média)
- [x] Autenticação com Laravel Sanctum
- [x] Validação de dados com Form Requests
- [x] Padronização de respostas com API Resources
- [x] Testes automatizados

## Sistema de Cache

O sistema implementa um mecanismo de cache avançado para otimização das consultas de listagem de vagas de emprego. Características principais:

- **Driver**: Utiliza o driver `redis` com suporte a **Cache Tags** do Laravel
- **Cache Tags**: Sistema de tags hierárquicas para invalidação granular e eficiente
- **Granularidade**: **Média** - balanceando performance e precisão na invalidação
- **TTL**: Cache configurado para expirar após 15 minutos
- **Cache keys**: Geradas dinamicamente com base nos parâmetros da requisição (filtros, ordenação, etc.)
- **Invalidação inteligente**: Utiliza cache tags para invalidação seletiva por categorias específicas
- **Override**: É possível ignorar o cache em qualquer requisição adicionando o parâmetro `?skip_cache=true`

### Como Funciona o Cache com Tags

```php
// Trecho do JobListingController@index
public function index(Request $request)
{
    // Create a cache key based on the request parameters
    $cacheKey = 'job_listings:' . md5(json_encode($request->all()));
    $cacheTTL = 60 * 15; // 15 minutes
    
    // Define cache tags for granular invalidation
    $tags = [
        'job_listings',
        'job_listings:type:' . ($request->get('type') ?? 'all'),
        'job_listings:status:' . ($request->get('status') ?? 'active')
    ];
    
    // Check if we have a cached result
    if (!$request->has('skip_cache')) {
        $cached = Cache::tags($tags)->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
    }
    
    // ... processamento da consulta ...
    
    // Cache the result with tags
    Cache::tags($tags)->put($cacheKey, $response, $cacheTTL);
    
    return $response;
}
```

### Invalidação Inteligente via Observer com Cache Tags

```php
// Trecho do JobListingObserver
private function clearJobListingCache(JobListing $jobListing): void
{
    // Invalidação granular usando cache tags
    Cache::tags([
        'job_listings',
        'job_listings:type:' . $jobListing->type,
        'job_listings:status:' . $jobListing->status,
        'job_listings:company:' . $jobListing->company_id
    ])->flush();
    
    // Para mudanças críticas, invalida cache geral
    if ($this->isCriticalChange($jobListing)) {
        Cache::tags(['job_listings'])->flush();
    }
}

private function isCriticalChange(JobListing $jobListing): bool
{
    return $jobListing->wasChanged(['status', 'type', 'salary_range']);
}
```


## 📱 Exemplos de Uso no Postman

### 1. Registrar um usuário

**Método:** `POST`  
**URL:** `http://localhost:8000/api/register`  
**Headers:**
- Content-Type: application/json

**Body (raw JSON):**
```json
{
    "name": "Nome do Usuário",
    "email": "email@exemplo.com",
    "password": "senha123",
    "password_confirmation": "senha123",
    "role": "candidate"
}
```

### 2. Fazer login

**Método:** `POST`  
**URL:** `http://localhost:8000/api/login`  
**Headers:**
- Content-Type: application/json

**Body (raw JSON):**
```json
{
    "email": "email@exemplo.com",
    "password": "senha123"
}
```

### 3. Listar vagas

**Método:** `GET`  
**URL:** `http://localhost:8000/api/job-listings`  
**Headers:**
- Authorization: Bearer {seu_token}

### 4. Listar vagas com filtros

**Método:** `GET`  
**URL:** `http://localhost:8000/api/job-listings?type=CLT&min_salary=3000&location=São Paulo&skip_cache=true`  
**Headers:**
- Authorization: Bearer {seu_token}

#### Parâmetros de Filtragem Disponíveis

- `type`: Filtra por tipo de contratação (CLT, PJ, Freelancer)
- `company`: Filtra pelo nome da empresa
- `location`: Filtra por localização
- `min_salary` / `max_salary`: Filtra por faixa salarial
- `experience_level`: Filtra por nível de experiência
- `is_active`: Filtra apenas vagas ativas (true/false)
- `search`: Busca o termo nos campos título e descrição
- `order_by`: Campo para ordenação (default: created_at)
- `order_direction`: Direção da ordenação (asc/desc)
- `per_page`: Número de itens por página
- `include_user`: Inclui dados do recrutador
- `include_applications`: Inclui candidaturas relacionadas

### 5. Criar uma nova vaga (apenas recrutadores)

**Método:** `POST`  
**URL:** `http://localhost:8000/api/job-listings`  
**Headers:**
- Authorization: Bearer {seu_token}
- Content-Type: application/json

**Body (raw JSON):**
```json
{
    "title": "Desenvolvedor PHP",
    "description": "Desenvolvimento de aplicações web",
    "company_name": "Empresa XYZ",
    "location": "São Paulo",
    "type": "CLT",
    "salary": 5000,
    "requirements": "PHP;Laravel;MySQL",
    "benefits": "VR;VA;Plano de saúde",
    "expiration_date": "2023-12-31",
    "vacancies": 2,
    "experience_level": "Pleno"
}
```

### 6. Atualizar uma vaga

**Método:** `PUT`  
**URL:** `http://localhost:8000/api/job-listings/:id`  
**Headers:**
- Authorization: Bearer {seu_token}
- Content-Type: application/json

**Body (raw JSON):**
```json
{
    "title": "Desenvolvedor PHP Sênior",
    "description": "Desenvolvimento de aplicações web avançadas",
    "company_name": "Empresa XYZ",
    "location": "São Paulo",
    "type": "CLT",
    "salary": 8000,
    "requirements": "PHP;Laravel;MySQL;Redis",
    "benefits": "VR;VA;Plano de saúde;Home office",
    "expiration_date": "2024-03-31",
    "vacancies": 1,
    "experience_level": "Sênior"
}
```

### 7. Candidatar-se a uma vaga (apenas candidatos)

**Método:** `POST`  
**URL:** `http://localhost:8000/api/job-applications`  
**Headers:**
- Authorization: Bearer {seu_token}
- Content-Type: application/json

**Body (raw JSON):**
```json
{
    "job_listing_id": 1,
    "cover_letter": "Estou interessado nesta vaga porque tenho experiência em PHP e Laravel...",
    "resume_url": "https://meusite.com/curriculo.pdf",
    "expected_salary": 5500
}
```

### 8. Atualizar candidatura

**Método:** `PUT`  
**URL:** `http://localhost:8000/api/job-applications/:id`  
**Headers:**
- Authorization: Bearer {seu_token}
- Content-Type: application/json

**Body (raw JSON):**
```json
{
    "cover_letter": "Carta de apresentação atualizada...",
    "resume_url": "https://meusite.com/curriculo-atualizado.pdf",
    "expected_salary": 6000
}
```

### 9. Retirar candidatura (apenas candidatos)

**Método:** `PATCH`  
**URL:** `http://localhost:8000/api/job-applications/:id/withdraw`  
**Headers:**
- Authorization: Bearer {seu_token}

### 10. Deletar candidatura

**Método:** `DELETE`  
**URL:** `http://localhost:8000/api/job-applications/:id`  
**Headers:**
- Authorization: Bearer {seu_token}

### 11. Criar usuário (admin)

**Método:** `POST`  
**URL:** `http://localhost:8000/api/users`  
**Headers:**
- Authorization: Bearer {seu_token}
- Content-Type: application/json

**Body (raw JSON):**
```json
{
    "name": "Novo Usuário",
    "email": "novo@exemplo.com",
    "password": "senha123",
    "password_confirmation": "senha123",
    "role": "recruiter"
}
```

### 12. Atualizar usuário

**Método:** `PUT`  
**URL:** `http://localhost:8000/api/users/:id`  
**Headers:**
- Authorization: Bearer {seu_token}
- Content-Type: application/json

**Body (raw JSON):**
```json
{
    "name": "Nome Atualizado",
    "email": "email-atualizado@exemplo.com",
    "role": "candidate"
}
```

## Usuários de Teste

O sistema vem pré-configurado com os seguintes usuários:

**Recrutador:**
- Email: recruiter@example.com
- Senha: password

**Candidato:**
- Email: candidate@example.com
- Senha: password

## 🧪 Executando Testes

A aplicação possui uma suíte completa de testes automatizados para validar todas as funcionalidades.

### ⚡ Método Recomendado - Script run-tests.sh

O projeto inclui um script otimizado que configura automaticamente o ambiente de teste:

```bash
# Executa todos os testes com configuração automática
sudo ./run-tests.sh

# Executa testes específicos (filtro por nome)
sudo ./run-tests.sh "UserTest"
```


### 🔧 Executando testes manualmente via Docker

Se preferir executar manualmente:
```bash
# Todos os testes
docker-compose exec app-estech php artisan test

# Testes específicos por feature
docker-compose exec app-estech php artisan test --filter=JobListingTest
docker-compose exec app-estech php artisan test --filter=JobApplicationTest
docker-compose exec app-estech php artisan test --filter=UserTest
docker-compose exec app-estech php artisan test --filter=ClimateDataTest

# Executar um teste específico
docker-compose exec app-estech php artisan test tests/Feature/AuthTest.php
```

## 📊 Sistema de Dados Climáticos

A aplicação inclui um sistema completo para importação, análise e gerenciamento de dados climáticos.

### Importação de Dados Climáticos

#### Comando de Importação
```bash

# Importar com chunk size customizado (padrão: 1000)
docker-compose exec app-estech php artisan climate:import example.csv --chunk-size=500 --queue=climate_data
```

#### Processamento Assíncrono
- **Jobs em Background**: A importação usa jobs para processar grandes volumes
- **Chunking**: Processa dados em lotes para otimizar performance
- **Error Handling**: Registra erros sem interromper o processamento completo

### Endpoints para Dados Climáticos

#### Análise Diária de Dados Climáticos
```bash
# Análise completa (últimos 30 dias por padrão)
curl -X GET "http://localhost:8000/api/climate-data/analysis" \
  -H "Authorization: Bearer {seu_token}"

# Análise com período específico
curl -X GET "http://localhost:8000/api/climate-data/analysis?start_date=2022-01-01&end_date=2022-01-31" \
  -H "Authorization: Bearer {seu_token}"

# Análise agrupada por mês
curl -X GET "http://localhost:8000/api/climate-data/analysis?group_by=month" \
  -H "Authorization: Bearer {seu_token}"

# Análise agrupada por ano
curl -X GET "http://localhost:8000/api/climate-data/analysis?group_by=year" \
  -H "Authorization: Bearer {seu_token}"
```

**Parâmetros disponíveis:**
- `start_date`: Data de início (YYYY-MM-DD)
- `end_date`: Data de fim (YYYY-MM-DD)
- `group_by`: Agrupamento (day, month, year)

**Resposta da análise:**
```json
{
  "data": [
    {
      "period": "2022-01-01",
      "average_temperature": 23.45,
      "min_temperature": 18.2,
      "max_temperature": 28.7,
      "records_count": 31
    }
  ],
  "summary": {
    "total_records": 6600,
    "period_start": "2022-01-01",
    "period_end": "2022-12-31",
    "overall_average": 23.8,
    "overall_min": 12.1,
    "overall_max": 35.4
  }
}
```

#### Exclusão em Massa de Dados Climáticos
```bash
# Deletar todos os dados
curl -X DELETE "http://localhost:8000/api/climate-data/bulk-delete" \
  -H "Authorization: Bearer {seu_token}"

# Deletar dados de um período específico
curl -X DELETE "http://localhost:8000/api/climate-data/bulk-delete" \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2022-01-01",
    "end_date": "2022-01-31"
  }'

# Deletar dados por critério de temperatura
curl -X DELETE "http://localhost:8000/api/climate-data/bulk-delete" \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "min_temperature": 30.0,
    "max_temperature": 40.0
  }'
```

**Parâmetros para exclusão:**
- `start_date`: Data de início para exclusão
- `end_date`: Data de fim para exclusão  
- `min_temperature`: Temperatura mínima para exclusão
- `max_temperature`: Temperatura máxima para exclusão

### Workflow Completo: Importação → Análise → Exclusão

#### 1. Importar dados climáticos
```bash
# Importa o arquivo de exemplo default
docker-compose exec app-estech php artisan climate:import example.csv --chunk-size=500 --queue=climate_data
```

#### 2. Analisar dados importados
```bash
# Análise geral dos dados
curl -X GET "http://localhost:8000/api/climate-data/analysis" \
  -H "Authorization: Bearer {seu_token}"

# Análise por mês para identificar padrões
curl -X GET "http://localhost:8000/api/climate-data/analysis?group_by=month" \
  -H "Authorization: Bearer {seu_token}"
```

#### 3. Analisar período específico
```bash
# Analisar apenas o primeiro trimestre de 2022
curl -X GET "http://localhost:8000/api/climate-data/analysis?start_date=2022-01-01&end_date=2022-03-31" \
  -H "Authorization: Bearer {seu_token}"
```

#### 4. Exclusão por ids
```bash
# Remover TODOS OS DADOS
curl -X DELETE "http://localhost:8000/api/climate-data/bulk-delete" \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{"ids": [1,2,3,4]}'
```

#### 4. Exclusão EM MASSA
```bash
# Remover TODOS OS DADOS
curl -X DELETE "http://localhost:8000/api/climate-data/bulk-delete" \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{"delete_all": true}'
```

#### 5. Re-análise após exclusão
```bash
# Verificar o impacto da exclusão nos dados
curl -X GET "http://localhost:8000/api/climate-data/analysis" \
  -H "Authorization: Bearer {seu_token}"
```

### Cache para Dados Climáticos
- **Análises Cached**: Resultados de análise são cached por 30 minutos
- **Invalidação Automática**: Cache é limpo após operações de bulk delete
- **Performance**: Consultas complexas são otimizadas com cache

## 📋 Endpoints Completos da API

### Autenticação e Usuários

#### Endpoints Públicos de Autenticação
- `POST /api/register` - Registra um novo usuário
- `POST /api/login` - Autentica um usuário e retorna um token
- `GET /api/health` - Verifica se a API está funcionando
- `GET /api/ping` - Endpoint de teste que retorna "pong"

#### Endpoints Protegidos de Usuários
- `POST /api/logout` - Invalida o token atual
- `GET /api/profile` - Retorna os dados do usuário autenticado
- `GET /api/users` - Lista todos os usuários (paginado)
- `GET /api/users/{id}` - Exibe detalhes de um usuário
- `POST /api/users` - Cria um novo usuário
- `PUT /api/users/{id}` - Atualiza um usuário
- `DELETE /api/users/{id}` - Remove um usuário (soft delete)

### Vagas de Emprego (Job Listings)

#### Endpoints Públicos
- `GET /api/public/job-listings` - Lista todas as vagas disponíveis
- `GET /api/public/job-listings/{id}` - Exibe detalhes de uma vaga específica

#### Endpoints Protegidos
- `GET /api/job-listings` - Lista todas as vagas (paginado, com cache)
- `GET /api/job-listings/{id}` - Exibe detalhes de uma vaga
- `POST /api/job-listings` - Cria uma nova vaga (apenas recrutadores)
- `PUT /api/job-listings/{id}` - Atualiza uma vaga (apenas recrutadores)
- `DELETE /api/job-listings/{id}` - Remove uma vaga (soft delete, apenas recrutadores)

### Candidaturas (Job Applications)

#### Endpoints Protegidos
- `GET /api/job-applications` - Lista candidaturas (paginado)
- `GET /api/job-applications/{id}` - Exibe detalhes de uma candidatura
- `POST /api/job-applications` - Cria uma nova candidatura (apenas candidatos)
- `PUT /api/job-applications/{id}` - Atualiza uma candidatura (apenas proprietário)
- `DELETE /api/job-applications/{id}` - Remove uma candidatura (soft delete)
- `PATCH /api/job-applications/{id}/withdraw` - Retira uma candidatura (apenas candidatos)

### Dados Climáticos (Climate Data)

#### Endpoints Protegidos
- `GET /api/climate-data/analysis` - Análise diária de dados climáticos
- `DELETE /api/climate-data/bulk-delete` - Exclusão em massa de dados climáticos

## 💡 Exemplos de Uso Avançados

### Workflow Completo de Recrutamento

#### 1. Recrutador cria uma vaga
```bash
curl -X POST http://localhost:8000/api/job-listings \
  -H "Authorization: Bearer {token_recrutador}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Desenvolvedor Full Stack Laravel + React",
    "description": "Desenvolvimento de aplicações web modernas usando Laravel e React",
    "company_name": "TechCorp",
    "location": "São Paulo - SP",
    "type": "CLT",
    "salary": 8000,
    "requirements": "Laravel;React;Docker;MySQL;Git",
    "benefits": "Vale Refeição;Vale Transporte;Plano de Saúde;Home Office",
    "expiration_date": "2024-06-30",
    "vacancies": 3,
    "experience_level": "Sênior"
  }'
```

#### 2. Candidato visualiza vagas disponíveis
```bash
# Busca vagas de desenvolvimento com salário acima de R$ 7000
curl -X GET "http://localhost:8000/api/public/job-listings?search=desenvolvedor&min_salary=7000&location=São%20Paulo" \
  -H "Authorization: Bearer {token_candidato}"
```

#### 3. Candidato se candidata à vaga
```bash
curl -X POST http://localhost:8000/api/job-applications \
  -H "Authorization: Bearer {token_candidato}" \
  -H "Content-Type: application/json" \
  -d '{
    "job_listing_id": 1,
    "cover_letter": "Tenho 5 anos de experiência com Laravel e 3 anos com React. Já trabalhei em projetos similares e tenho conhecimento sólido em Docker e MySQL.",
    "resume_url": "https://meuportfolio.com/curriculo.pdf",
    "expected_salary": 8500
  }'
```

#### 4. Recrutador visualiza candidaturas
```bash
# Lista candidaturas para suas vagas
curl -X GET "http://localhost:8000/api/job-applications?include_job_listing=true" \
  -H "Authorization: Bearer {token_recrutador}"
```

### Workflow Completo de Dados Climáticos

#### 1. Preparação e importação
```bash
# Verifica se o arquivo existe
docker-compose exec app-estech ls -la storage/app/example.csv

# Importa os dados climáticos
docker-compose exec app-estech php artisan climate:import example.csv --chunk-size=1000
```

#### 2. Análise exploratória
```bash
# Análise geral de todo o período
curl -X GET "http://localhost:8000/api/climate-data/analysis" \
  -H "Authorization: Bearer {seu_token}"

# Análise por trimestre
curl -X GET "http://localhost:8000/api/climate-data/analysis?start_date=2022-01-01&end_date=2022-03-31&group_by=month" \
  -H "Authorization: Bearer {seu_token}"

curl -X GET "http://localhost:8000/api/climate-data/analysis?start_date=2022-04-01&end_date=2022-06-30&group_by=month" \
  -H "Authorization: Bearer {seu_token}"
```

#### 3. Análise de extremos climáticos
```bash
# Identificar meses com temperaturas mais altas
curl -X GET "http://localhost:8000/api/climate-data/analysis?group_by=month" \
  -H "Authorization: Bearer {seu_token}" | jq '.data[] | select(.max_temperature > 30)'
```

#### 4. Limpeza de dados anômalos
```bash
# Remover registros com temperaturas extremamente altas (acima de 40°C)
curl -X DELETE "http://localhost:8000/api/climate-data/bulk-delete" \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{"min_temperature": 40.0}'

# Remover registros com temperaturas extremamente baixas (abaixo de 0°C)
curl -X DELETE "http://localhost:8000/api/climate-data/bulk-delete" \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{"max_temperature": 0.0}'
```

#### 5. Re-análise após limpeza
```bash
# Verificar o impacto da limpeza
curl -X GET "http://localhost:8000/api/climate-data/analysis?group_by=year" \
  -H "Authorization: Bearer {seu_token}"
```

## 🔧 Monitoramento e Debug

### Laravel Telescope
A aplicação inclui Laravel Telescope para debugging e monitoramento:

```bash
# Acessar o Telescope (quando habilitado)
http://localhost:8000/telescope
```

**Recursos disponíveis:**
- Monitoramento de requests HTTP
- Análise de queries SQL
- Logs de jobs e queues
- Monitoramento de cache hits/misses
- Debug de autenticação e autorização

### Logs da Aplicação
```bash
# Visualizar logs em tempo real
docker-compose exec app-estech tail -f storage/logs/laravel.log

# Limpar logs antigos
docker-compose exec app-estech php artisan log:clear
```

## 🛡️ Segurança

### Autenticação
- **Laravel Sanctum**: Tokens seguros para autenticação de API
- **Rate Limiting**: Controle de taxa de requisições
- **CSRF Protection**: Proteção contra ataques CSRF
- **Password Hashing**: Senhas hasheadas com bcrypt

### Autorização
- **Policies**: Controle granular de permissões
- **Role-based Access**: Diferentes níveis de acesso (recruiter/candidate)
- **Resource Protection**: Usuários só podem acessar seus próprios recursos

### Validação
- **Form Requests**: Validação estruturada de entrada
- **SQL Injection Protection**: ORM Eloquent previne injeções SQL
- **XSS Protection**: Sanitização automática de dados

## 📈 Performance

### Otimizações Implementadas
- **Database Indexing**: Índices otimizados para consultas frequentes
- **Eager Loading**: Carregamento eficiente de relacionamentos
- **Cache Strategy**: Sistema inteligente de cache com invalidação
- **Pagination**: Paginação para grandes datasets
- **Query Optimization**: Consultas otimizadas para performance

### Métricas de Performance
- **Cache Hit Rate**: Monitorado via Telescope
- **Query Count**: Otimização para reduzir N+1 queries
- **Response Time**: Monitoring de tempo de resposta da API
- **Memory Usage**: Controle de uso de memória em jobs pesados

## Pendências e Melhorias Futuras

- [ ] **Migração para Redis**: Atualmente o sistema utiliza o driver de cache do banco de dados, que é adequado para desenvolvimento mas pode não ser ideal para produção. Migrar para Redis ofereceria melhor desempenho:
  ```php
  // Em .env
  CACHE_DRIVER=redis
  REDIS_HOST=redis-estech
  REDIS_PORT=6379
  
  // Em docker-compose.yml adicionar:
  redis-estech:
    image: redis:alpine
    volumes:
      - redis-data:/data
    ports:
      - "6379:6379"
  ```

- [x] Implementação de deleção em massa para melhorar a eficiência operacional
- [x] Personalização avançada da paginação com opções de navegação aprimoradas
- [x] Expansão da cobertura de testes, incluindo testes unitários para todos os componentes
- [x] Implementação de recursos adicionais para filtragem e ordenação avançadas

---

**🎯 Resumo**: Siga os passos manuais de instalação descritos acima. Em caso de problemas, verifique Docker, portas e permissões. Para debug, use os logs dos containers e comandos de verificação do Laravel. Os testes podem ser executados com `./run-tests.sh`.
