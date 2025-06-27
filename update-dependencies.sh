#!/bin/bash

# Script para atualização segura das dependências do projeto

# Cores para formatação de texto
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Iniciando atualização segura das dependências...${NC}"

# Verificar se os contêineres Docker estão em execução
if ! docker compose ps | grep -q "app-estech"; then
    echo -e "${RED}Os contêineres Docker não parecem estar em execução.${NC}"
    echo -e "Por favor, inicie os contêineres com ${YELLOW}docker compose up -d${NC} antes de continuar."
    exit 1
fi

# Fazer backup do composer.lock
echo -e "${YELLOW}Fazendo backup do composer.lock...${NC}"
cp composer.lock composer.lock.backup

# Atualizar dependências dentro do container
echo -e "${YELLOW}Atualizando dependências do Composer...${NC}"
docker compose exec app-estech composer update

# Verificar se os testes passam após a atualização
echo -e "${YELLOW}Executando testes para garantir compatibilidade...${NC}"
if ! docker compose exec app-estech php artisan test; then
    echo -e "${RED}Atenção: Os testes falharam após a atualização de dependências!${NC}"
    echo -e "Restaurando composer.lock do backup..."
    mv composer.lock.backup composer.lock
    docker compose exec app-estech composer install
    echo -e "${YELLOW}As dependências foram restauradas ao estado anterior.${NC}"
    echo -e "Por favor, verifique manualmente quais dependências estão causando problemas."
    exit 1
fi

# Atualização bem-sucedida
echo -e "${GREEN}Atualização de dependências concluída com sucesso!${NC}"
echo -e "Arquivo de backup: ${YELLOW}composer.lock.backup${NC} (você pode removê-lo se tudo estiver funcionando corretamente)"

# Limpar caches
echo -e "${YELLOW}Limpando caches...${NC}"
docker compose exec app-estech php artisan optimize:clear
echo -e "${GREEN}Cache limpo.${NC}"
