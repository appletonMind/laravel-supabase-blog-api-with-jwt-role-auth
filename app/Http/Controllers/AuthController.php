<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {

        $data = Validator::make($request->all(), [
            'name' => 'required|string|max:30',
            'username' => 'required|string|min:3|max:20|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|exists:roles,id',  // validate that the role_id is valid
        ]);
    
        if ($data->fails()) {
            return response()->json(['error' => $data->errors()], 422);
        }

        
        $validatedData = $data->validated(); // valid data 

        $role = $validatedData['role'] ?? 'user'; // if "role" is not present, "user" is default
        $user = User::create([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $role, // add role here
        ]);
    
        $customClaims = [
            'user' => [
                'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'role' => $user->role
]
        ];

       $token = JWTAuth::customClaims($customClaims)->fromUser($user);

        // generate the token for the User
    
        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            'token' => $token,
        ], 201);
    }

    /**
     * Login user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Call to services for login
            $credentials = $request->only('email', 'password');

            $validator = Validator::make($credentials, [
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            if ($validator->fails()) {
                throw new \Exception('Validation Error', 422);
            }
    
            // attemp to authenticate the user
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new \Exception('Unauthorized', 401);
            }
    
            // get the authenticated user
            $user = JWTAuth::user();

             // If a jwt remember_token already exists, we delete it
        if ($user->jwt_remember_token) {
            $user->jwt_remember_token = null; // Delete the old remember_token
            $user->save();
        }

    
            // Add more data to the JWT (custom)
            $customClaims = [
                'user' => [
                    'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,

    ]
            ];
    
            // Generate a token with the custom data
            $token = JWTAuth::customClaims($customClaims)->fromUser($user);

            $remember = $request->has('remember') && $request->boolean('remember');

            if ($remember) {
                $user->jwt_remember_token = $token;
                $user->save();
            }
            
    
            // We return the token and user data
            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
        } catch (\Exception $e) {
            // If an error occurs, we return an error response.
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Logout of a user.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        // Get the authenticated user
    $user = JWTAuth::user();
        JWTAuth::invalidate(JWTAuth::getToken());
        $user->jwt_remember_token = null;
        $user->save();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }



    public function verify(): JsonResponse
    {
        try {
            // We try to get the user from the token
            $user = JWTAuth::parseToken()->authenticate();
            $token = JWTAuth::getToken()->get();
    
            // If the remember_token is not present in the database, we do not perform the comparison.
            if ($user->jwt_remember_token) {
                // If the remember_token is present and does not match, we return an error
                if ($user->jwt_remember_token !== $token) {
                    return response()->json(['error' => 'Sesión no válida.'], 401);
                }
            }
    
            // If we get here, the token is valid
            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            // If there is an error, we return that the token is invalid or has expired
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        }
    }
    


    public function updateUser(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
    
        $data = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:30',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'current_password' => 'required_with:password|string',
        ]);
    
        if ($data->fails()) {
            return response()->json(['error' => $data->errors()], 422);
        }
    
        $validated = $data->validated();
    
        // If you try to change your password, we first validate the current one.
        if (isset($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['error' => ['current_password' => ['La contraseña actual no es correcta.']]], 403);
            }
    
            $user->password = Hash::make($validated['password']);
        }
    
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
    
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
    
        $user->save();
    
        return response()->json([
            'message' => 'Datos actualizados correctamente.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }
    
    
    public function getUser(Request $request)
{
    // Only the Admin can access
    $user = $request->user();
    return response()->json($user);
}

public function getUserById($id)
{
    // Get user data by ID
    $user = User::find($id);
    
    if (!$user) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }

    return response()->json($user);
}



}
