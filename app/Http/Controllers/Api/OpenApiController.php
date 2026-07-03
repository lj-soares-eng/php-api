<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class OpenApiController extends Controller
{
    public function schema(): Response
    {
        $path = base_path('specs/001-users-api/contracts/users-api.openapi.yaml');

        return response(File::get($path), Response::HTTP_OK, [
            'Content-Type' => 'application/yaml',
        ]);
    }

    public function docs(): Response
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Users REST API</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
    SwaggerUIBundle({
        url: '/api/schema/',
        dom_id: '#swagger-ui',
    });
</script>
</body>
</html>
HTML;

        return response($html, Response::HTTP_OK, [
            'Content-Type' => 'text/html',
        ]);
    }
}
