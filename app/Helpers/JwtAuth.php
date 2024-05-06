<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

use App\Models\User;
use Firebase\JWT\JWT;


class JwtAuth
{

    public $key;

    public function __construct()
    {
        $this->key = 'Esta es una clave super secreta DSP'; // es una clave randon
    }

    public function setKey($key)
    {
        $this->key = $key;
    }
    public function getKey()
    {
        return $this->key;
    }

    public function singup($usuario, $password, $getToken = null)
    {

        $user = User::where([

            'user' => $usuario,
            'password' => $password

        ])->first();

        $singup = false;

        if (is_object($user)) {
            $singup = true;
        }
        if ($singup) {
            $token = array(
                'sub' => $user->id,
                'nombres' => $user->nombres,
                'cargo' => $user->cargo,
                'usuario' => $user->user,
                'rol' => $user->rol,
                'estado' => $user->estado,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
            if (is_null($getToken)) {
                $data = array(
                    'status' => 'success',
                    'token' => $jwt,
                    'identity' => $decode
                );
            } else {
                $data = $decode;
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto'
            );
        }
        return $data;
    }
    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;
        try {
            $jwt = str_replace('"', '', $jwt);
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }
        if (!empty($decode) && is_object($decode) && isset($decode->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity == true) {
            return $decode;
        }
        return $auth;
    }
}
