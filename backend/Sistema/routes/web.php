<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Rotas da Aplicação
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar todas as rotas para uma aplicação.
| É muito simples. Apenas diga ao Lumen as URIs às quais ele deve responder
| e forneça o Closure para chamar quando essa URI for solicitada.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Grupo de rotas da API
$router->group(['prefix' => 'api'], function () use ($router) {
    
    // Rotas públicas (Login)
    $router->post('auth/login', 'AuthController@login');

    // Rotas protegidas por JWT
    $router->group(['middleware' => 'auth'], function () use ($router) {
        
        // Autenticação
        $router->get('auth/me', 'AuthController@me');
        $router->post('auth/logout', 'AuthController@logout');

        // CRUD Alunos
        $router->get('alunos', 'AlunoController@index');
        $router->post('alunos', 'AlunoController@store');
        $router->get('alunos/{id}', 'AlunoController@show');
        $router->put('alunos/{id}', 'AlunoController@update');
        $router->delete('alunos/{id}', 'AlunoController@destroy');

        // Dashboard e Benefícios (RN09)
        $router->get('dashboard/beneficios', 'DashboardController@beneficios');
        
        // Presenças (Manual e consulta)
        $router->get('presencas', 'PresencaController@index');
        $router->post('presencas/manual', 'PresencaController@manualUpdate');

        // Auditoria (Apenas Admin)
        $router->get('auditoria', 'AuditoriaController@index');

        // Atividades
        $router->get('atividades', 'AtividadeController@index');
        $router->post('atividades', 'AtividadeController@store');
        $router->delete('atividades/{id}', 'AtividadeController@destroy');

        // Justificativas
        $router->get('justificativas', 'JustificativaController@index');
        $router->post('justificativas', 'JustificativaController@store');
        $router->post('justificativas/{id}/decide', 'JustificativaController@decide');

        // Rekognition - Reconhecimento Facial
        $router->post('rekognition/register-face', 'RekognitionController@registerFace');
        $router->get('rekognition/faces', 'RekognitionController@listRegisteredFaces');
    });

    // Rota pública para registrar presença por reconhecimento facial
    $router->post('api/rekognition/facial-presence', 'RekognitionController@registerFacialPresence');
});
