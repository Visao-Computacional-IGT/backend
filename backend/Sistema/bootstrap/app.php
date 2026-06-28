<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Criar a Aplicação
|--------------------------------------------------------------------------
|
| Aqui carregaremos o ambiente e criaremos a instância da aplicação
| que serve como peça central deste framework. Usaremos esta
| aplicação como um contêiner "IoC" e roteador para este framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Registrar Vinculações do Contêiner
|--------------------------------------------------------------------------
|
| Agora registraremos algumas vinculações no contêiner de serviço. Vamos
| registrar o manipulador de exceções e o kernel do console. Você pode adicionar
| suas próprias vinculações aqui se desejar ou criar outro arquivo.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Registrar Arquivos de Configuração
|--------------------------------------------------------------------------
|
| Agora registraremos o arquivo de configuração "app". Se o arquivo existir no
| seu diretório de configuração, ele será carregado; caso contrário, carregaremos
| a versão padrão. Você pode registrar outros arquivos abaixo conforme necessário.
|
*/

$app->configure('app');
$app->configure('auth');
$app->configure('database');

/*
|--------------------------------------------------------------------------
| Registrar Middleware
|--------------------------------------------------------------------------
|
| A seguir, registraremos o middleware com a aplicação. Estes podem
| ser middlewares globais que rodam antes e depois de cada requisição em uma
| rota ou middlewares que serão atribuídos a algumas rotas específicas.
|
*/

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);

/*
|--------------------------------------------------------------------------
| Registrar Provedores de Serviço
|--------------------------------------------------------------------------
|
| Aqui registraremos todos os provedores de serviço da aplicação que
| são usados para vincular serviços ao contêiner. Provedores de serviço são
| totalmente opcionais, então você não é obrigado a descomentar esta linha.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Carregar as Rotas da Aplicação
|--------------------------------------------------------------------------
|
| A seguir incluiremos o arquivo de rotas para que todas possam ser adicionadas à
| aplicação. Isso fornecerá todas as URLs às quais a aplicação
| pode responder, bem como os controladores que podem lidar com elas.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
