<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

return [
    [
        'id' => 'getting-started',
        'title' => 'Primeiros Passos',
        'audience' => 'Todos os utilizadores',
        'items' => [
            'A AgriTwin apoia decisões agrícolas diárias para a Fotovoltaica Macotera SL ao combinar telemetria de campo, meteorologia, estado dos serviços OpenAgri e saídas de aconselhamento.',
            'Inicie sessão com o nome de utilizador atribuído pelo administrador. Se a conta exigir alteração de palavra-passe, abra o Perfil e defina uma nova palavra-passe antes de continuar.',
            'Altere o idioma da interface no seletor do cabeçalho. A escolha mantém-se entre páginas através da sessão do navegador e do cookie.',
            'Os acessos são baseados em funções: Administrador, Gestor Técnico, Gestor da Exploração, Consultor / Investigador e Visualizador. As páginas restritas mostram mensagens de configuração ou acesso quando a função atual não permite executar uma ação.',
        ],
    ],
    [
        'id' => 'dashboard',
        'title' => 'Painel de Controlo',
        'audience' => 'Gestor da Exploração, Consultor / Investigador, Visualizador',
        'items' => [
            'O Painel de Controlo é o centro operacional do estado atual da exploração, com stress hídrico, rega, pressão de pragas, azoto, meteorologia e prontidão OpenAgri.',
            'Os cartões meteorológicos distinguem dados do serviço meteorológico externo das leituras das estações no campo. Verifique a origem antes de usar os valores em decisões.',
            'Os cartões das estações resumem número de estações, bloco agrícola, leituras mais recentes e confiança. Confiança reduzida significa que os dados devem ser revistos antes de agir.',
            'A prontidão dos serviços OpenAgri principais cobre Gatekeeper, Weather, Irrigation e Pest & Disease. O cartão indica quantos serviços principais estão atualmente disponíveis.',
        ],
    ],
    [
        'id' => 'analytics',
        'title' => 'Análise Estatística',
        'audience' => 'Gestor da Exploração, Gestor Técnico, Consultor / Investigador',
        'items' => [
            'Use os filtros de intervalo, estação e cultura para focar os gráficos nas últimas 24 horas, 7 dias ou 30 dias quando existirem dados.',
            'Os gráficos meteorológicos combinam temperatura, humidade, vento e precipitação para interpretar em conjunto as condições de rega e pragas.',
            'Os gráficos de sensores comparam humidade e temperatura do solo por estação. Setas de tendência e cores de limiar identificam blocos que exigem atenção.',
            'Os painéis de rega, pragas e KPIs mostram volume, energia, risco, latência, continuidade e atualidade para revisão operacional.',
        ],
    ],
    [
        'id' => 'sensor-stations',
        'title' => 'Estações de Sensores',
        'audience' => 'Gestor da Exploração, Gestor Técnico, Visualizador',
        'items' => [
            'As estações são nomeadas por local e bloco, por exemplo MAC-ALM-A para o Bloco A de amendoeiras. Cada estação agrupa várias leituras de um ativo de campo.',
            'As unidades incluem humidade do solo VWC %, temperatura do solo degC, temperatura do ar degC, humidade relativa %, precipitação mm, vento km/h, radiação solar W/m2, caudal de rega m3/h e energia kWh.',
            'A atualidade baseia-se no carimbo temporal mais recente. Valores antigos ou incompletos reduzem a confiança e devem ser comparados com as condições no campo.',
            'Problemas comuns incluem falta de energia, cobertura rádio fraca, pluviómetros obstruídos, sondas desligadas e sincronização atrasada.',
        ],
    ],
    [
        'id' => 'weather-openagri',
        'title' => 'Meteorologia e OpenAgri Weather',
        'audience' => 'Gestor da Exploração, Gestor Técnico',
        'items' => [
            'As leituras no campo vêm das estações locais; os valores do serviço meteorológico externo chegam pelo caminho OpenAgri Weather ou por uma fonte meteorológica pública aprovada.',
            'O estado do serviço Weather mostra se o ponto de acesso está alcançável e se existem observações em tempo real disponíveis.',
            'As credenciais do fornecedor meteorológico são configuradas nas Definições por utilizadores autorizados. As chaves guardadas nunca são apresentadas depois de gravadas.',
            'A meteorologia influencia recomendações de rega, estimativas de evapotranspiração, pressão de pragas e interpretação de risco de doença. Se faltarem credenciais, a plataforma mostra essa necessidade claramente.',
        ],
    ],
    [
        'id' => 'irrigation',
        'title' => 'Rega',
        'audience' => 'Gestor da Exploração, Consultor / Investigador',
        'items' => [
            'As recomendações de rega combinam humidade do solo, meteorologia, balanço hídrico, caudal e energia quando esses dados estão disponíveis.',
            'Os indicadores de balanço hídrico e défice de rega mostram se um bloco se aproxima de limiares de humidade abaixo do preferido.',
            'Os valores de caudal e energia da bomba ajudam a confirmar se a janela recomendada é viável e eficiente.',
            'Os rótulos de confiança indicam se as entradas estão atuais e completas. Confiança mais baixa exige confirmação no campo antes de executar a ação.',
        ],
    ],
    [
        'id' => 'pest-disease',
        'title' => 'Pragas e Doenças',
        'audience' => 'Gestor da Exploração, Consultor / Investigador',
        'items' => [
            'Os níveis de risco combinam temperatura, humidade, humidade foliar, precipitação e contexto da cultura. Use-os como apoio à decisão, não como única fonte de inspeção.',
            'Os graus-dia de crescimento, quando disponíveis, apoiam a interpretação de fases de pragas e o calendário de observação.',
            'Risco moderado ou elevado deve desencadear observação no campo, verificação de armadilhas e revisão de aconselhamento agronómico local.',
            'Os resultados dependem da qualidade das entradas. Meteorologia em falta ou dados antigos de humidade foliar podem reduzir a confiança.',
        ],
    ],
    [
        'id' => 'farm-calendar',
        'title' => 'Calendário Agrícola',
        'audience' => 'Gestor da Exploração, Consultor / Investigador',
        'items' => [
            'O Calendário Agrícola regista operações, observações, ativos e registos da exploração que explicam alterações nas condições.',
            'As operações incluem verificações de rega, sensores, observação de pragas, inspeção da bomba e outras atividades agrícolas.',
            'A integração FarmCalendar permite que os serviços OpenAgri partilhem contexto operacional quando configurada.',
            'Mantenha os registos concisos e com data/hora para que relatórios e análises sejam auditáveis.',
        ],
    ],
    [
        'id' => 'reports',
        'title' => 'Relatórios',
        'audience' => 'Gestor da Exploração, Consultor / Investigador, Administrador',
        'items' => [
            'Os relatórios resumem aconselhamento de rega, pragas e doenças, estações de sensores, estado dos serviços e evidência operacional.',
            'As ações de exportação preparam saídas para impressão ou JSON com marcas AgriTwin, Fotovoltaica Macotera SL e OBSIDIAN.',
            'Os relatórios incluem dados operacionais necessários para revisão e evitam segredos ou credenciais.',
            'Verifique os rótulos de origem dos dados antes de distribuir um relatório externamente.',
        ],
    ],
    [
        'id' => 'openagri-services',
        'title' => 'Serviços OpenAgri',
        'audience' => 'Gestor Técnico, Administrador',
        'items' => [
            'Os serviços OpenAgri principais são Gatekeeper / Access Control, Weather Data Service, Irrigation Management Service e Pest & Disease Management Service.',
            'Os serviços operacionais adicionais são FarmCalendar e Reporting Service. Geodatabase / OCSM é opcional e afeta apenas instalações que o configurem.',
            'Os estados incluem Serviço disponível, Serviço indisponível, Configuração necessária, Credenciais necessárias e Configuração do fornecedor externo necessária.',
            'As ligações de ponto de acesso são URLs de serviço, as ligações de documentação da API explicam a utilização e as ligações de repositório apontam para o código-fonte.',
        ],
    ],
    [
        'id' => 'settings',
        'title' => 'Definições',
        'audience' => 'Administrador, Gestor Técnico',
        'admin' => true,
        'items' => [
            'As Definições mostram modo da plataforma, URLs OpenAgri, preferência meteorológica, caminho base da API, fuso horário, intervalos de atualização e estado da base de dados.',
            'As chaves API meteorológicas podem ser guardadas, validadas e removidas por utilizadores autorizados. Os segredos permanecem locais e nunca são devolvidos no código da página nem nas respostas da API.',
            'Os URLs de serviço podem ser revistos nas Definições, enquanto credenciais sensíveis devem permanecer geridas por ambiente ou na área local segura.',
            'Use o painel de diagnóstico da base de dados para confirmar anfitrião, porta, nome da base de dados e estado da ligação sem expor palavras-passe.',
        ],
    ],
    [
        'id' => 'user-management',
        'title' => 'Gestão de Utilizadores',
        'audience' => 'Administrador',
        'admin' => true,
        'items' => [
            'Os Administradores podem criar utilizadores, atribuir funções, redefinir palavras-passe, ativar ou desativar contas e definir idioma preferido.',
            'As palavras-passe são guardadas como hashes. Palavras-passe temporárias devem ser alteradas no primeiro acesso.',
            'As funções Gestor Técnico, Gestor da Exploração, Consultor / Investigador e Visualizador recebem níveis progressivamente mais restritos.',
            'Desative contas sem uso em vez de apagar histórico operacional.',
        ],
    ],
    [
        'id' => 'api-swagger',
        'title' => 'API / Swagger',
        'audience' => 'Gestor Técnico, Administrador, Consultor / Investigador',
        'items' => [
            'O Swagger apresenta a descrição OpenAPI dos endpoints AgriTwin, incluindo saúde, meteorologia, estações, análise, serviços OpenAgri e KPIs.',
            'Utilizadores técnicos podem consultar formatos de pedido e resposta e testar endpoints de leitura quando as permissões o permitirem.',
            'O JSON OpenAPI destina-se a ferramentas de integração e não contém credenciais.',
            'Ações de escrita exigem a função autenticada adequada e proteção CSRF quando são usadas por formulários do navegador.',
        ],
    ],
    [
        'id' => 'troubleshooting',
        'title' => 'Resolução de Problemas',
        'audience' => 'Todos os utilizadores, Gestor Técnico',
        'items' => [
            'Se a base de dados estiver indisponível, execute scripts/start_local_stack.sh e depois scripts/check_local_stack.sh. Não elimine volumes da base de dados durante a recuperação normal.',
            'Se um serviço OpenAgri estiver indisponível, execute scripts/openagri/check_openagri_services.sh e reveja ponto de acesso, credenciais e estado do contentor.',
            'Se forem necessárias credenciais meteorológicas, um utilizador autorizado deve configurar a chave do fornecedor nas Definições.',
            'Se os gráficos não carregarem, atualize a página e confirme que o JavaScript está ativo. Se o idioma não persistir, verifique os cookies do navegador para /agritwin/.',
            'Para problemas de acesso ou palavra-passe, contacte um Administrador para redefinir a conta ou confirmar permissões.',
        ],
    ],
    [
        'id' => 'glossary',
        'title' => 'Glossário',
        'audience' => 'Todos os utilizadores',
        'items' => [
            'ADS: apoio à decisão agrícola. API: interface de programação de aplicações. OpenAPI e Swagger: formatos e ferramentas para documentação de APIs.',
            'Gatekeeper controla autenticação e acesso aos serviços. Weather Service, Irrigation Management e Pest & Disease Management fornecem funções OpenAgri principais.',
            'FarmCalendar regista operações e observações. Estação de sensores significa um ativo de campo que recolhe várias medições.',
            'VWC é teor volumétrico de água. ET0 é evapotranspiração de referência. Kc é coeficiente da cultura. NDVI e NDRE são índices de vegetação.',
            'KPI significa indicador-chave de desempenho. Modo local de contingência significa que a plataforma usa dados operacionais locais enquanto uma ligação em tempo real está indisponível. Credenciais necessárias significa que é necessário um segredo configurado. Serviço disponível significa que a verificação do ponto de acesso foi bem-sucedida.',
        ],
    ],
];
