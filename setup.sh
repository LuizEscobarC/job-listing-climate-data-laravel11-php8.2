#!/bin/bash

# Script de setup para o ambiente de desenvolvimento da API Laravel

# Cores para formatação de texto
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== SETUP AUTOMÁTICO DA API LARAVEL ===${NC}"

# Verificar se docker e docker-compose estão instalados
if ! command -v docker &> /dev/null || ! command -v docker compose &> /dev/null; then
    echo -e "${RED}❌ Docker e/ou Docker Compose não estão instalados!${NC}"
    echo "Por favor, instale o Docker e o Docker Compose antes de continuar."
    echo "Instruções: https://docs.docker.com/get-docker/"
    exit 1
fi

echo -e "${GREEN}✅ Docker e Docker Compose encontrados${NC}"

# Parar containers se estiverem rodando
echo -e "${YELLOW}🛑 Parando containers existentes...${NC}"
docker compose down --volumes --remove-orphans 2>/dev/null || true

# Construir containers Docker
echo -e "${YELLOW}🏗️ Construindo containers Docker (isso pode demorar alguns minutos)...${NC}"
docker compose build --no-cache
echo -e "${GREEN}✅ Containers construídos${NC}"

# Iniciar os containers
echo -e "${YELLOW}🚀 Iniciando containers...${NC}"
docker compose up -d
echo -e "${GREEN}✅ Containers iniciados${NC}"

# Aguardar containers estarem prontos (tempo mais realista)
echo -e "${YELLOW}⏳ Aguardando containers iniciarem (30s)...${NC}"
sleep 30

# Verificar se containers estão rodando
echo -e "${YELLOW}🔍 Verificando status dos containers...${NC}"
if ! docker compose ps | grep -q "Up"; then
    echo -e "${RED}❌ Erro: Containers não estão rodando corretamente${NC}"
    echo "Verifique os logs:"
    docker compose logs
    exit 1
fi
echo -e "${GREEN}✅ Containers estão rodando${NC}"

# INSTALAR DEPENDÊNCIAS DO COMPOSER (SUA ABORDAGEM)
echo -e "${YELLOW}📦 Instalando dependências do Composer...${NC}"
if ! test -f "vendor/autoload.php"; then
    echo -e "${YELLOW}📦 Executando composer install...${NC}"
    if docker compose exec -T app-estech composer install --optimize-autoloader --no-interaction; then
        echo -e "${GREEN}✅ Dependências instaladas com sucesso!${NC}"
    else
        echo -e "${RED}❌ Falha na instalação das dependências${NC}"
        echo "Logs do container:"
        docker compose logs app-estech
        exit 1
    fi
else
    echo -e "${GREEN}✅ Dependências já existem${NC}"
fi

# Verificar se vendor foi realmente criado e é acessível
echo -e "${YELLOW}🔍 Verificando se vendor está acessível...${NC}"
if docker compose exec -T app-estech test -f "vendor/autoload.php"; then
    echo -e "${GREEN}✅ Vendor acessível no container app${NC}"
else
    echo -e "${RED}❌ Vendor não está acessível no container${NC}"
    exit 1
fi

if docker compose exec -T queue-workers test -f "vendor/autoload.php"; then
    echo -e "${GREEN}✅ Vendor acessível no container queue${NC}"
else
    echo -e "${RED}❌ Vendor não está acessível no container queue${NC}"
    exit 1
fi

# Aguardar Redis estar pronto
echo -e "${YELLOW}🔴 Verificando Redis...${NC}"
until docker compose exec -T app-estech php artisan tinker --execute="Cache::store('redis')->put('test', 'ok'); echo 'Redis OK';" 2>/dev/null | grep -q "Redis OK"; do
    echo -e "${YELLOW}⏳ Aguardando Redis...${NC}"
    sleep 2
done
echo -e "${GREEN}✅ Redis conectado!${NC}"

# Aguardar banco de dados estar pronto
echo -e "${YELLOW}🗄️ Aguardando banco de dados estar pronto...${NC}"
max_attempts=30
attempt=0

until [ $attempt -ge $max_attempts ]; do
    if docker compose exec -T app-estech php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB Connected';" 2>/dev/null | grep -q "DB Connected"; then
        echo -e "${GREEN}✅ Banco de dados conectado!${NC}"
        break
    fi
    
    attempt=$((attempt + 1))
    echo -e "${YELLOW}⏳ Tentativa $attempt/$max_attempts - Aguardando banco...${NC}"
    sleep 2
done

if [ $attempt -ge $max_attempts ]; then
    echo -e "${RED}❌ ERRO: Não foi possível conectar ao banco de dados${NC}"
    echo "Verifique os logs do MySQL:"
    docker compose logs db-estech
    exit 1
fi

# Executar migrações
echo -e "${YELLOW}🗄️ Executando migrações...${NC}"
docker compose exec -T app-estech php artisan migrate --force
echo -e "${GREEN}✅ Migrações executadas${NC}"

# Executar seeders
echo -e "${YELLOW}🌱 Populando banco com dados de teste...${NC}"
docker compose exec -T app-estech php artisan db:seed --force
echo -e "${GREEN}✅ Dados de teste criados${NC}"

# Verificar queue workers
echo -e "${YELLOW}👷 Verificando queue workers...${NC}"
if docker compose exec queue-workers supervisorctl status | grep -q "RUNNING"; then
    echo -e "${GREEN}✅ Queue workers estão rodando${NC}"
else
    echo -e "${YELLOW}⚠️ Queue workers não estão rodando - verificando logs...${NC}"
    docker compose logs queue-workers
fi

# Configurar Git dentro do container
echo -e "${YELLOW}⚙️ Configurando Git...${NC}"
docker compose exec -T app-estech git config --global --add safe.directory /var/www
docker compose exec -T app-estech git config --global user.email "container@localhost"
docker compose exec -T app-estech git config --global user.name "Container User"
echo -e "${GREEN}✅ Git configurado${NC}"

# Publicar assets do Telescope
echo -e "${YELLOW}🔭 Publicando assets do Telescope...${NC}"
docker compose exec -T app-estech php artisan vendor:publish --tag=telescope-assets --force --no-interaction
echo -e "${GREEN}✅ Assets do Telescope publicados${NC}"

# Verificar se tudo está funcionando
echo -e "${YELLOW}🔍 Verificando se a API está respondendo...${NC}"
sleep 5

# Tentar acessar o health check
if curl -f -s http://localhost:8000/api/health >/dev/null 2>&1; then
    echo -e "${GREEN}✅ API está respondendo corretamente!${NC}"
else
    echo -e "${YELLOW}⚠️ API ainda não está respondendo (pode demorar mais alguns segundos)${NC}"
    echo "Tente: curl http://localhost:8000/api/health"
fi

# Informações finais
echo ""
echo -e "${BLUE}🎉 ===== SETUP CONCLUÍDO COM SUCESSO! =====${NC}"
echo ""
echo -e "${GREEN}🌐 API Principal:${NC} http://localhost:8000"
echo -e "${GREEN}🔭 Telescope:${NC} http://localhost:8000/telescope"
echo -e "${GREEN}📊 Health Check:${NC} http://localhost:8000/api/health"
echo ""
echo -e "${YELLOW}👥 Usuários de teste criados:${NC}"
echo -e "   📧 Recrutador: recruiter@example.com | 🔑 Senha: password"
echo -e "   📧 Candidato: candidate@example.com | 🔑 Senha: password"
echo ""
echo -e "${BLUE}📋 Comandos úteis:${NC}"
echo -e "   🐳 ${YELLOW}docker compose ps${NC} - Lista containers"
echo -e "   📋 ${YELLOW}docker compose logs -f${NC} - Exibe logs em tempo real"
echo -e "   🛑 ${YELLOW}docker compose down${NC} - Para os containers"
echo -e "   💻 ${YELLOW}docker compose exec app-estech bash${NC} - Acessa shell do container"
echo -e "   🧪 ${YELLOW}./run-tests.sh${NC} - Executa testes"
echo -e "   🏃 ${YELLOW}docker compose exec app-estech php artisan climate:import example.csv --queue=climate_data${NC} - Testa queue"
echo ""
echo -e "${GREEN}✅ Ambiente pronto para desenvolvimento!${NC}"