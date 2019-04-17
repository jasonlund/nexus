<?php
namespace App\Services;

use App\Rules\RichTextRequired;
use App\Rules\UniqueCaseInsensitive;
use Bouncer;
use Hash;
use Storage;
use App\Models\User;
use Illuminate\Validation\Rule;

class UsersService
{
    public static function validationRules($action = null)
    {
        $ignoreUsername = $ignoreEmail = null;

        if(request()->route('user')){
            $ignoreUsername = request()->route('user')->username;
            $ignoreEmail = request()->route('user')->email;
        }else if(auth()->check()) {
            $ignoreUsername = auth()->user()->username;
            $ignoreEmail = auth()->user()->email;
        }

        $rules = collect([
            'avatar' => ['nullable', 'sometimes', 'image'],
            'comment' => ['nullable', 'string'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($ignoreEmail, 'email')],
            'expired_at' => ['nullable', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['string', 'min:8', 'confirmed', 'case_diff', 'numbers', 'letters'],
            'role' => ['required', 'string'],
            'token' => ['required'],
            'username' => ['required', 'min:3', 'max:16', 'alpha_dash',
                new UniqueCaseInsensitive(User::class, $ignoreUsername)],
        ]);

        switch ($action) {
            case "ban":
                $rules = $rules->only(['comment', 'expired_at']);
                break;
            case "password.reset":
                $rules = $rules->map(function($item, $key){
                    if($key === 'password')
                        array_unshift($item, 'required');
                    else if($key === 'email')
                        array_pop($item);
                    return $item;
                });
                $rules = $rules->only(['token', 'email', 'password']);
                break;
            case "register":
                $rules = $rules->map(function($item, $key){
                    if($key === 'password')
                        array_unshift($item, 'required');
                    return $item;
                });
                $rules = $rules->only(['name', 'username', 'email', 'password']);
                break;
            case "update.self":
                $rules = $rules->map(function($item, $key){
                    if($key === 'password')
                        array_unshift($item, 'nullable');
                    return $item;
                });
                $rules = $rules->only(['name', 'username', 'email', 'password']);
                break;
            case "update":
                $rules = $rules->map(function($item, $key){
                    if($key === 'password')
                        array_unshift($item, 'nullable');
                    return $item;
                });
                $rules = $rules->only(['name', 'username', 'email', 'password', 'role']);
                break;
            case "avatar":
                $rules = $rules->only(['avatar']);
                break;
        }

        return $rules->toArray();
    }

    public function update($user, $data)
    {
        $attr = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email']
        ];

        if(array_key_exists('password', $data) && $data['password']){
            $attr['password'] = Hash::make($data['password']);
        }

        return $user->update($attr);
    }

    public function assignRole($user, $role)
    {
        if(!$user->isAn($role)){
            if($role === 'user')
                Bouncer::sync($user)->roles([]);
            else
                Bouncer::sync($user)->roles([$role]);
        }
    }

    public function delete($user, $authedUser = null)
    {
        $user->roles()->sync([]);

        if($authedUser) {
            auth()->setUser($user);
            auth()->logout();
            auth()->setUser($authedUser);
        }else{
            auth()->logout();
        }

        $user->delete();
    }

    public function ban($user, $data)
    {
        $user->ban($data);
    }

    public function unban($user)
    {
        $user->unban();
    }

    public function avatar($user, $data)
    {
        if($user->avatar_path)
            Storage::disk('public')->delete($user->avatar_path);

        $file_path = $data->file('avatar') !== null ? $data->file('avatar')->store('avatars', 'public') : null;

        return $user->update([
            'avatar_path' => $file_path
        ]);
    }
}
