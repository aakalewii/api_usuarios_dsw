<?php

namespace App\Enums;

enum Rol: string
{
    case ADMINISTRADOR = 'Administrador';
    case GESTOR = 'Gestor';
    case CLIENTE = 'Cliente';
}