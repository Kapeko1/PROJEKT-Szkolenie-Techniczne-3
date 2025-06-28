<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'E-commerce API for managing categories, products, and orders',
    title: 'E-commerce API',
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Local development server'
)]
abstract class Controller
{
    //
}
