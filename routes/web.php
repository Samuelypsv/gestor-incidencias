<?php

use Illuminate\Support\Facades\Route;

// El panel es una SPA en React: cualquier ruta que no sea de la API devuelve la misma vista
Route::view('/{ruta?}', 'app')->where('ruta', '^(?!api).*$');
