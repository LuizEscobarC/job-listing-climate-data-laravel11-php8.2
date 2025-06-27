<?php

return [
    'welcome' => 'Bem-vindo à nossa aplicação!',
    'goodbye' => 'Até logo!',
    'app_name' => 'Minha Aplicação Laravel',

    // Termos comuns
    'actions' => 'Ações',
    'save' => 'Salvar',
    'edit' => 'Editar',
    'delete' => 'Excluir',
    'cancel' => 'Cancelar',
    'confirm' => 'Confirmar',
    'back' => 'Voltar',
    'submit' => 'Enviar',
    'success' => 'Sucesso',
    'error' => 'Erro',
    'warning' => 'Aviso',
    'info' => 'Informação',

    // Termos específicos da aplicação
    'users' => 'Usuários',
    'job_listings' => 'Vagas de Emprego',
    'job_applications' => 'Candidaturas',
    'profile' => 'Perfil',
    'dashboard' => 'Painel',

    // Operações CRUD
    'created_successfully' => ':resource criado(a) com sucesso.',
    'updated_successfully' => ':resource atualizado(a) com sucesso.',
    'deleted_successfully' => ':resource excluído(a) com sucesso.',
    'restored_successfully' => ':resource restaurado(a) com sucesso.',
    'status_updated_successfully' => 'Status do(a) :resource atualizado com sucesso.',

    // Operações em massa
    'bulk_deleted_successfully' => ':count :resource excluído(s) com sucesso.',
    'bulk_restored_successfully' => ':count :resource restaurado(s) com sucesso.',
    'bulk_status_updated_successfully' => 'Status de :count :resource atualizado(s) com sucesso.',

    // Erros de autorização
    'unauthorized' => 'Não autorizado.',
    'forbidden' => 'Você não tem permissão para realizar esta ação.',
    'access_denied' => 'Acesso negado.',

    // Erros de recursos
    'resource_not_found' => ':resource não encontrado(a).',
    'user_not_found' => 'Usuário não encontrado.',
    'job_listing_not_found' => 'Vaga de emprego não encontrada.',
    'job_application_not_found' => 'Candidatura não encontrada.',

    // Validação específica
    'invalid_credentials' => 'Credenciais inválidas.',
    'email_already_taken' => 'Este e-mail já está em uso.',
    'application_already_exists' => 'Você já se candidatou a esta vaga.',
    'cannot_apply_own_listing' => 'Você não pode se candidatar à sua própria vaga.',
    'application_deadline_passed' => 'O prazo para candidaturas a esta vaga já expirou.',
    'cannot_update_application_status' => 'A candidatura não pode ser atualizada no status atual.',
    'invalid_status_transition' => 'Transição de status inválida.',
    'cannot_withdraw_application' => 'A candidatura não pode ser retirada no status atual.',

    // Status de candidaturas
    'application_status_pending' => 'Pendente',
    'application_status_under_review' => 'Em análise',
    'application_status_accepted' => 'Aceito',
    'application_status_rejected' => 'Rejeitado',

    // Roles
    'role_admin' => 'Administrador',
    'role_recruiter' => 'Recrutador',
    'role_candidate' => 'Candidato',

    // Cache
    'cache_cleared' => 'Cache limpo com sucesso.',

    // Autenticação
    'login_successful' => 'Login realizado com sucesso.',
    'logout_successful' => 'Logout realizado com sucesso.',
    'token_expired' => 'Token expirado.',
    'token_invalid' => 'Token inválido.',

    // E-mail
    'email_verified' => 'E-mail verificado com sucesso.',
    'email_not_verified' => 'E-mail não verificado.',
    'verification_email_sent' => 'E-mail de verificação enviado.',

    // Paginação
    'no_records_found' => 'Nenhum registro encontrado.',
    'showing_results' => 'Mostrando :from a :to de :total resultados.',

    // Recursos específicos
    'User' => 'Usuário',
    'JobListing' => 'Vaga de Emprego', 
    'JobApplication' => 'Candidatura',

    // Importação de dados climáticos
    'file_not_found' => 'Arquivo não encontrado: :file',
    'invalid_file_format' => 'Formato de arquivo inválido. Apenas arquivos CSV são aceitos.',
    'starting_import' => 'Iniciando importação do arquivo: :file',
    'import_completed' => 'Importação concluída! :rows registros processados em :chunks chunks.',
    'check_queue_status' => 'Verifique o status da fila ":queue" para acompanhar o progresso.',
    'import_failed' => 'Falha na importação: :error',
    'invalid_csv_header' => 'Cabeçalho do CSV inválido. Esperado: data, temperatura',
];
