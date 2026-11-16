<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TecnicoController extends Controller
{
    /** Lista de usuarios a los que se puede asignar un ticket. */
    public function index(): AnonymousResourceCollection
    {
        return UsuarioResource::collection(User::orderBy('name')->get());
    }
}
