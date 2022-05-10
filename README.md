# Módulo GoCache para Magento 2

> Gerencia configurações e limpeza de cache

## Funcionalidades

- Configurar tempo de cache
- Limpar cache automaticamente após editar um produto
- Limpar cache manualmente

## Instalação

Faça o clone desse repositório e copie a pasta GoCache para dentro da pasta `/app/code` do Magento 2.  
Depois execute os comandos abaixo:

```sh
php bin/magento setup:upgrade
php bin/magento cache:clean
php bin/magento setup:static-content:deploy -f
```

A tela de configuração é acessível pelo menu Stores -> Advanced -> System -> Full Page Cache -> Configurações da GoCache.  
O arquivo de log é armazenado em `var/log/gocache_log.txt` quando o módulo estiver configurado para gravar logs.

## Rodando local via Docker

Os passos para rodar via Docker são:

- Executar o Docker Compose na raiz desse repositório
- Copiar a pasta contendo o módulo para o local correto, dentro do container
- Executar os comandos de instalação

```sh
# Execute os containers
docker-compose up -d

# Aguarde 30s ou mais até que tudo esteja funcionando
sleep 30

# Entre no container como usuário root
docker exec -it magento /bin/bash

# Mude de usuário
su daemon -s /bin/bash

# Crie a pasta onde deve ficar o módulo
mkdir /opt/bitnami/magento/app/code

# Copie o módulo para a pasta que o Magento 2 espera
cp -r /tmp/GoCache /opt/bitnami/magento/app/code/

# Execute os comandos para instalação
cd /opt/bitnami/magento
php bin/magento setup:upgrade
php bin/magento cache:clean
php bin/magento setup:static-content:deploy -f
```

Agora acesse a URL `http://localhost:8080/admin` e efetue login com usuário `user` e senha `Admin123`.

