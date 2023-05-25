<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Filters\V1\UserFilter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\V1\User\UserCollection;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new UserFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $users = User::where($AndFilterItems)->where($OrFilterItems);

      $includePerson = $request->query('includePerson');
      if ($includePerson) {
        $users = $users->with('person');
      }

      return new UserCollection($users->orderBy('id')->paginate(25)->appends($request->query()));
    }

    public function updateUser(Request $request) {
      $validateData = $request->validate([
        'id' => 'required',
        'password' => 'string',
        'rol' => 'required',
        'person_id' => 'required',
      ]);

      $userData = [
        'rol' => $validateData['rol'],
        'person_id' => $validateData['person_id'],
      ];

      if (isset($validateData['password'])) {
        $userData['password'] = Hash::make($validateData['password']);
      }

      $user = User::where('id', '=', $validateData['id'])->update($userData);

      return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
      $user->delete();
    }
}
