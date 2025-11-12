<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        version: '1.0.0',
        title: 'API de Préstamos',
        description: 'Documentación generada automáticamente con OpenAPI/Swagger para la API de préstamos.',
        contact: new OA\Contact(
            name: 'API prestamos',
            email: 'isaaccaliz305@gmail.com'
        )
    ),
    servers: [
        new OA\Server(
            url: 'http://localhost:8000',
            description: 'Servidor local de desarrollo'
        )
    ],
    tags: [
        new OA\Tag(name: 'Auth', description: 'Endpoints de autenticación'),
        new OA\Tag(name: 'Clientes', description: 'Gestión de clientes'),
        new OA\Tag(name: 'Sistema', description: 'Endpoints del sistema')
    ],
    components: new OA\Components(
        securitySchemes: [
            new OA\SecurityScheme(
                securityScheme: 'bearerAuth',
                type: 'http',
                scheme: 'bearer',
                bearerFormat: 'JWT'
            )
        ]
    )
)]
class OpenApiSpec {}
