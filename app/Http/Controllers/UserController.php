<?php

namespace App\Http\Controllers;

//use App\Exports\ExcelExport;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
// use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view user');

        $users = null;

        if ($request->has('search')) {
            $users = User::where('name', 'LIKE', '%' . $request->search . '%')
                ->where('username', 'LIKE', '%' . $request->search . '%')
                ->orWhere('email', 'LIKE', '%' . $request->search . '%')
                ->orWhereHas('roles', function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->search . '%');
                })
                ->paginate();
        } else {
            $users = User::paginate();
        }

        $usersArray = (UserResource::collection($users)->toResponse(app('request')))->getData(True);

        return $this->sendPaginatedResponse(
            $usersArray,
            (count($usersArray['data']) == 0) ? "No records found" : "Records found."
        );
    }

    // public function exportData(Request $request)
    // {
    //     $this->authorize('view user');

    //     $users = null;

    //     $query = User::join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
    //             ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
    //             ->select(
    //                 'users.id as ID',
    //                 'users.name as Name',
    //                 'email as Email',
    //                 'username as Username',
    //                 'title as Title',
    //                 'mobile_number as Mobile Number',
    //                 'roles.name as Role',
    //             );

    //     if ($request->has('search')) {
    //         $query->where('users.name', 'LIKE', '%' . $request->search . '%')
    //             ->where('username', 'LIKE', '%' . $request->search . '%')
    //             ->orWhere('email', 'LIKE', '%' . $request->search . '%')
    //             ->orWhere('title', 'LIKE', '%' . $request->search . '%')
    //             ->orWhere('mobile_number', 'LIKE', '%' . $request->search . '%')
    //             ->orWhereHas('roles', function ($query) use ($request) {
    //                 $query->where('name', 'LIKE', '%' . $request->search . '%');
    //             });
    //     }

    //     $users = $query->get()->toArray();

    //     $headerArray = isset($users[0]) ? array_keys($users[0]) : [];

    //     $export = new ExcelExport($users, $headerArray);

    //     return Excel::download($export, 'data.xlsx');
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create user');

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:users,name',
                'username' => 'required|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'roleId' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);
            }

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password')),
            ]);

            $role = Role::where("id", $request->input('roleId'))->get();

            $user->syncRoles([$role]);

            return $this->sendResponse(new UserResource($user), "User has been created.", 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500, $e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view user');

        $user = User::find($id);

        if (!isset($user)) {
            return $this->sendError("User not found", $errorMessages = [], 404);
        }

        return $this->sendResponse(
            new UserResource($user),
            !isset($user) ? "No record found" : "Record found."
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $this->authorize('update user');

        $user = User::find($id);

        if (!isset($user)) {
            return $this->sendError("User not found", $errorMessages = [], 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'username' => 'required|unique:users,username,' . $id,
                'email' => 'required|email|unique:users,email,' . $id,
                'roleId' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);
            }

            $user->name = $request->input('name');
            $user->username = $request->input('username');
            $user->email = $request->input('email');

            if ($request->has('password') && $request->filled('password')) {
                $password = $request->input('password');
                if (isset($password)) {
                    $user->password = Hash::make($request->input('password'));
                }
            }

            $user->save();

            $role = Role::where("id", $request->input('roleId'))->get();

            $user->syncRoles([$role]);

            return $this->sendResponse(new UserResource($user), "User has been updated.", 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500, $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete user');

        try {
            $user = User::find($id);

            if (!isset($user)) {
                return $this->sendError("User not found", $errorMessages = [], 404);
            }

            $user->delete();

            return $this->sendResponse(new UserResource($user), "User has been deleted.", 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500, $e);
        }
    }
}
