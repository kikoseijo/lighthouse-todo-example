<?php

namespace App\Http\GraphQL\Mutations;


// use App\Repositories\UserRepository;
use App\User;
use Illuminate\Support\Facades\Validator;
// use Ksoft\Klaravel\Larapp;

class CreateUser
{
    public function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);
        // return Validator::make($data, User::$rules);
    }

    public function handle(array $data)
    {
        /**
         * Prepare the data...
         * and create.
         */
        return User::create($data);

        // return Larapp::interact(
        //     UserRepository::class.'@create',
        //     [$data]
        // );
    }
}
