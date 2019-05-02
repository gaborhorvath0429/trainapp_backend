<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use JWTAuthException;

class UserController extends Controller
{
    private function getToken($email, $password)
    {
        $token = null;
        //$credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid',
                    'token' => $token,
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }
        return $token;
    }

    public function login(Request $request)
    {
        $user = \App\User::where('email', $request->email)->get()->first();
        if ($user && \Hash::check($request->password, $user->password)) // The passwords match...
        {
            $token = self::getToken($request->email, $request->password);
            $user->api_token = $token;
            $user->save();
            return response(['success' => true, 'data' => ['id' => $user->id, 'api_token' => $user->api_token, 'name' => $user->name, 'email' => $user->email]]);
        } else {
            return response(['success' => false, 'error' => 'Hibás bejelentkezési adatok!'], 400);
        }
    }

    public function register(Request $request)
    {
        $payload = [
            'password' => \Hash::make($request->password),
            'email' => $request->email,
            'name' => $request->name,
            'api_token' => '',
        ];

        $user = new \App\User($payload);

        try {
            $user->saveOrFail();

            $token = self::getToken($request->email, $request->password); // generate user token

            if (!is_string($token)) {
                return response()->json(['success' => false, 'data' => 'Token generation failed'], 201);
            }

            $user = \App\User::where('email', $request->email)->get()->first();

            $user->api_token = $token; // update user token

            $user->save();

            return response(['success' => true, 'data' => ['name' => $user->name, 'id' => $user->id, 'email' => $request->email, 'api_token' => $token]]);
        } catch (\Exception $e) {
            return response(['success' => false, 'error' => 'Email cím foglalt!'], 400);
        }
    }
}
