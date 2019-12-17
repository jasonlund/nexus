<?php

namespace App\Services;

use App\Rules\UniqueCaseInsensitive;
use Bouncer;
use Hash;
use Storage;
use App\Models\User;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Image;
use App\Rules\Recaptcha;

class UsersService
{
    /**
     * Get the request validation rules given an optional action.
     *
     * @param   string|null  $action
     *
     * @return  array
     */
    public static function validationRules($action = null)
    {
        $ignoreUsername = $ignoreEmail = null;

        if (request()->route('user')) {
            $ignoreUsername = request()->route('user')->username;
            $ignoreEmail = request()->route('user')->email;
        } else if (auth()->check()) {
            $ignoreUsername = auth()->user()->username;
            $ignoreEmail = auth()->user()->email;
        }

        $rules = collect([
            'accept' => ['bail', 'required', 'boolean', 'accepted'],
            'avatar' => ['bail', 'nullable', 'sometimes', 'image', 'max:1024'],
            'comment' => ['bail', 'nullable', 'string', 'max:1000'],
            'email' => ['bail', 'required', 'email', 'max:255', Rule::unique('users')->ignore($ignoreEmail, 'email')],
            'expired_at' => ['bail', 'nullable', 'date'],
            'location' => ['bail', 'sometimes', 'max:100'],
            'name' => ['bail', 'required', 'string', 'max:100'],
            'password' => ['bail', 'string', 'min:8', 'confirmed', 'case_diff', 'numbers', 'letters', 'max:30'],
            'recaptcha' => ['bail', 'required', 'string', new Recaptcha],
            'role' => ['bail', 'required', 'string'],
            'signature' => ['bail', 'nullable', 'string', 'max:1000'],
            'timezone' => ['bail', 'sometimes', 'timezone', 'max:255'],
            'username' => [
                'bail', 'required', 'min:3', 'max:20', 'alpha_dash',
                new UniqueCaseInsensitive(User::class, $ignoreUsername)
            ],
        ]);

        switch ($action) {
            case "ban":
                $rules = $rules->only(['comment', 'expired_at']);
                break;
            case "password.reset":
                $rules = $rules->map(function ($item, $key) {
                    if ($key === 'password')
                        array_unshift($item, 'required');
                    else if ($key === 'email')
                        array_pop($item);
                    return $item;
                });
                $rules = $rules->only(['email', 'password']);
                break;
            case "register":
                $rules = $rules->map(function ($item, $key) {
                    if ($key === 'password')
                        array_unshift($item, 'required');
                    return $item;
                });
                $rules = $rules
                    ->only(['name', 'username', 'email', 'password', 'recaptcha', 'accept']);
                break;
            case "login":
                $rules = $rules
                    ->only(['recaptcha']);
                break;
            case "update.self":
                $rules = $rules->map(function ($item, $key) {
                    if ($key === 'password')
                        array_unshift($item, 'nullable');
                    return $item;
                });
                $rules = $rules->only([
                    'name', 'username', 'password', 'signature',
                    'timezone', 'location'
                ]);
                break;
            case "update":
                $rules = $rules->map(function ($item, $key) {
                    if ($key === 'password')
                        array_unshift($item, 'nullable');
                    return $item;
                });
                $rules = $rules->only([
                    'name', 'username', 'email', 'password', 'role', 'timezone',
                    'location'
                ]);
                break;
            case "avatar":
                $rules = $rules->only(['avatar']);
                break;
        }

        return $rules->toArray();
    }

    /**
     * Generate a response for all Users and paginate if necessary.
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $paginated = true;
        $query = User::query();
        if (request()->has('role') && in_array(request('role'), ['admin', 'super-moderator', 'moderator'])) {
            $paginated = false;
            $query->whereIs(request('role'));
        } else if (request()->has('active')) {
            $paginated = false;
            $query->where('last_active_at', '>=', now()->subMinutes(10));
        }

        $query->orderBy('username');

        return $paginated ? paginated_response($query, 'UserSimpleTransformer')
            : collection_response($query, 'UserSimpleTransformer');
    }

    /**
     * Update the given User with the given data.
     *
     * @param   User   $user
     * @param   array  $data
     *
     * @return  User
     */
    public function update($user, $data)
    {
        $attr = [
            'name' => $data['name'],
            'username' => $data['username']
        ];

        if (array_key_exists('email', $data) && request()->route('users.update')) {
            $attr['email'] = $data['email'];
        }

        if (array_key_exists('signature', $data)) {
            $attr['signature'] = strip_html_whitespace($data['signature']) !== '' ? $data['signature'] : null;
        } else {
            $attr['signature'] = null;
        }

        if (array_key_exists('timezone', $data)) {
            $attr['timezone'] = $data['timezone'];
        }

        if (array_key_exists('location', $data)) {
            $attr['location'] = $data['location'];
        }

        if (array_key_exists('password', $data) && $data['password']) {
            $attr['password'] = Hash::make($data['password']);
        }

        $user->update($attr);

        return $user;
    }

    /**
     * Assign the given Role to the given User
     *
     * @param   User    $user
     * @param   string  $role
     *
     * @return  void
     */
    public function assignRole($user, $role)
    {
        if (!$user->isAn($role)) {
            if ($role === 'user')
                Bouncer::sync($user)->roles([]);
            else
                Bouncer::sync($user)->roles([$role]);
        }
    }

    /**
     * Delete the given User & void any current tokens.
     *
     * @param   User       $user
     * @param   User|null  $authedUser
     *
     * @return  void
     */
    public function delete($user, $authedUser = null)
    {
        $user->roles()->sync([]);

        if ($authedUser) {
            auth()->setUser($user);
            auth()->logout();
            auth()->setUser($authedUser);
        } else {
            auth()->logout();
        }

        $user->delete();
    }

    /**
     * Ban the given User with the given data.
     *
     * @param   User   $user
     * @param   array  $data
     *
     * @return  void
     */
    public function ban($user, $data)
    {
        $user->ban($data);
    }

    /**
     * Unban the given user.
     *
     * @param   User  $user
     *
     * @return  void
     */
    public function unban($user)
    {
        $user->unban();
    }

    /**
     * Update or remove the avatar of the given User with the given data.
     *
     * @param   User   $user
     * @param   array  $data
     *
     * @return  User
     */
    public function avatar($user, $data)
    {
        if ($user->avatar_path)
            Storage::delete($user->avatar_path);

        if ($data->file('avatar') === null) {
            $file_path = null;
        } else {
            $file = $data->file('avatar');
            $image = Image::make($file)
                ->fit(300);
            $file_path = 'avatars/' . $file->hashName();
            Storage::put($file_path, $image->stream());
        }

        $user->update([
            'avatar_path' => $file_path
        ]);

        return $user;
    }

    /**
     * Log the activity of the given user.
     *
     * @param   User  $user
     *
     * @return  User
     */
    public function logActive($user)
    {
        return $user->update([
            'last_active_at' => Carbon::now()
        ]);
    }
}
