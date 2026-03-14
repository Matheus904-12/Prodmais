<?php

return [
    'elasticsearch' => [
        'hosts' => [
            'http://elasticsearch:9200' // Endereço do servidor Elasticsearch (nome do serviço Docker)
        ]
    ],
    'data_paths' => [
        'lattes_xml' => __DIR__ . '/../data/lattes_xml',
        'logs' => __DIR__ . '/../data/logs.sqlite',
        'uploads' => __DIR__ . '/../data/uploads'
    ],
    'app' => [
        'index_name' => 'prodmais_umc', // Nome do índice no Elasticsearch (UMC)
        'version' => '2.0.0'
    ]
];
