<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use function Tinify\fromFile;
use function Tinify\setKey;

class UserController extends Controller
{
    public function index(Request $request) {
        try {
            $validatedData = $request->validate([
                'page' => 'integer|min:1',
                'count' => 'integer|min:1',
                'offset' => 'integer|min:0'
            ]);
        }
        catch (ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'fails' => $error->errors()
            ], 422);
        }

        $users = DB::table('users')
            ->orderBy('registration_timestamp', 'desc')
            ->orderBy('id', 'desc');

        $page = 1;

        $total_users = $users->count();

        $count = $total_users;

        $total_pages = 1;

        $prev_link = null;

        $next_link = null;

        if (array_key_exists('count', $validatedData)) {
            $count = $validatedData['count'];

            $exist_page = array_key_exists('page', $validatedData);

            $exist_offset = array_key_exists('offset', $validatedData);

            if (!$exist_page && !$exist_offset) {
                return response()->json([
                    'success' => true,
                    'message' => 'Validation failed',
                    'fails' => [
                        'error' => 'count or offset fields should be specified'
                    ]
                ], 422);
            }
            elseif ($exist_offset) {
                $offset = $validatedData['offset'];
                $total_pages = ceil(($total_users - $offset) / $count) + 1;

                $users = $users->offset($offset)->limit($count);

                $page = 2;

                if($offset != 0) {
                    $prev_link = "api/users?offset=0&count=$offset";
                }

                $offset += $count;

                if ($total_pages > $page) {
                    $next_link = "api/users?offset=$offset&count=$count";
                }
            }
            else {
                $page = $validatedData['page'];

                $offset = ($page - 1) * $count;

                if ($offset > $total_users) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Page not found',
                    ], 404);
                }

                $total_pages = ceil($total_users / $count);

                $users = $users->offset($offset)->limit($count);

                if ($total_pages > $page) {
                    $next_page = $page + 1;

                    $next_link = "api/users?page=$next_page&count=$count";
                }

                if ($page > 1) {
                    $prev_page = $page - 1;

                    $prev_link = "api/users?page=$prev_page&count=$count";
                }
            }
        }
        $users = $users->join('positions', 'position_id', '=', 'positions.id')
            ->select(DB::raw('users.id, users.name, email, phone, positions.name as position,
            position_id, UNIX_TIMESTAMP(users.registration_timestamp) as registration_timestamp, photo'))->get();


        return response()->json([
            'success' => true,
            'next_link' => $next_link,
            'prev_link' => $prev_link,
            "page" => $page,
            "total_pages" => $total_pages,
            "total_users" => $total_users,
            "count" => $count,
            'users' => $users
        ]);
    }

    public function create(Request $request) {
        if (!$request->session()->exists('Token')) {
            return response()->json([
                'success' => false,
                'message' => 'The Token expired'
            ], 401);
        }
        $phone_pattern = "regex:/^[\+]{0,1}380([0-9]{9})$/";
        $API_KEY = 'hMw0fH6JccgqCMxGj83hWSLrRSjB9Bkh';
        try {
            $request->validate([
                'phone' => 'unique:users',
                'email' => 'unique:users'
            ]);
        }
        catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'User with this phone or email already exist'
            ], 409);
        }


        try {
            $validatedData = $request->validate([
                'name' => 'required|string|min:2|max:60',
                'email' => "required|email:rfc|min:2|max:60",
                'phone' => "required|$phone_pattern",
                'position_id' => 'required|exists:positions,id',
                'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120'
            ]);
        }
        catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'fails' => $exception->errors()
            ], 422);
        };
        $imageName = time().Str::uuid().'.'.$request->photo->extension();

        $img = Image::make($request->photo);

        $img->fit(70, 70);

        setKey($API_KEY);

        $img->save(public_path('images').'/'.$imageName, 100, $request->photo->extension());

        $sourceData = fromFile(public_path('images').'/'.$imageName);

        $sourceData->toFile(public_path('images').'/'.$imageName);

        $validatedData['photo'] = $imageName;

        $user = new User($validatedData);

        $user->save();

        $request->session()->forget('Token');

        return response()->json();
    }
}
