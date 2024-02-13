<?php

namespace App\Http\Controllers;

//use App\Exports\ExcelExport;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
//use Maatwebsite\Excel\Facades\Excel;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;



class AccessController extends Controller
{
    public function createPermission(Request $request){
        $this->authorize('view permission');

        try {
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:permissions,name',
            ]);

            if($validator->fails()){
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);

            }

            $permission = Permission::create($request->all());

            return $this->sendResponse(new PermissionResource($permission), "Permission has been created.", 201);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }



    }

    public function updatePermission(Request $request){
        $this->authorize('update permission');

        try {
            $validator = Validator::make($request->all(),[
                'id' => 'required|exists:permissions,id',
                'name' => 'required|unique:permissions,name,'.$request->id,
            ]);

            if($validator->fails()){
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);

            }

            $permission = Permission::find($request->id);

            $permission->name = $request->name;
            $permission->save();

            return $this->sendResponse(new PermissionResource($permission), "Permission has been updated.", 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }
    }

    public function deletePermission($id){
        $this->authorize('delete permission');

        try {
            $permission = Permission::find($id);

            if (!isset($permission)) {
                return $this->sendError("Permission not found" , $errorMessages = [], 404);
            }

            $users = User::permission($permission->name)->get();

            if(!$users->isEmpty() )
            {
                return $this->sendError("Permission cannot be deleted because it is assigned to user(s).", [], 422);
            }

            $permission->delete();

            return $this->sendResponse(new PermissionResource($permission), "Permission has been deleted.", 200);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }


    }

    public function getPermission($id){
        try{
            $permission = Permission::find($id);

            if (!isset($permission)) {
                return $this->sendError("Permission not found" , $errorMessages = [], 404);
            }

            return $this->sendResponse(new PermissionResource($permission), "Record found.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }
    }

    /**
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAllPermissions(Request $request){
        $permissions = null;

        if($request->has('search')){
            $permissions = Permission::where('name', 'LIKE', '%' . $request->search . '%')
                ->paginate();
        }
        else{
            $permissions = Permission::paginate();
        }

        $permissionArray = (PermissionResource::collection($permissions)->toResponse(app('request')))->getData(True);

        return $this->sendPaginatedResponse(
            $permissionArray,
            (count($permissionArray['data']) == 0) ? "No records found" : "Records found."
        );
    }

    // public function exportPermissionData(Request $request)
    // {
    //     $this->authorize('view permission');

    //     $permissions = null;

    //     $query = Permission::select(
    //             'id as ID',
    //             'name as Name',
    //         );

    //     if ($request->has('search')) {
    //         $query->where('name', 'LIKE', '%' . $request->search . '%');
    //     }

    //     $permissions = $query->get()->toArray();

    //     $headerArray = isset($permissions[0]) ? array_keys($permissions[0]) : [];

    //     $export = new ExcelExport($permissions, $headerArray);

    //     return Excel::download($export, 'data.xlsx');
    // }

    public function createRole(Request $request){
        $this->authorize('create role');

        try {
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:roles,name',
                'permissions' => 'required'
            ]);

            if($validator->fails()){
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);

            }

            $role = Role::create($request->all());

            //sync permissions to role
            $permissionsArray = Permission::findMany(array_column($request->permissions, 'id'));

            $role->syncPermissions($permissionsArray);

            return $this->sendResponse(new RoleResource($role), "Role has been created.", 201);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }



    }

    public function updateRole(Request $request){
        $this->authorize('update role');

        try {
            $validator = Validator::make($request->all(),[
                'id' => 'required|exists:roles,id',
                'name' => 'required|unique:roles,name,'.$request->id,
                'permissions' => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);

            }

            $role = Role::find($request->id);

            $role->name = $request->name;
            $role->save();

            //sync permissions to role
            $permissionsArray = Permission::findMany(array_column($request->permissions, 'id'));

            $role->syncPermissions($permissionsArray);

            return $this->sendResponse(new RoleResource($role), "Role has been updated.", 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }
    }

    public function deleteRole($id){
        $this->authorize('delete role');

        try {
            $role = Role::find($id);

            if (!isset($role)) {
                return $this->sendError("Role not found" , $errorMessages = [], 404);
            }

            $users = User::role($role->name)->get();

            if(!$users->isEmpty() )
            {
                return $this->sendError("Role cannot be deleted because it is assigned to user(s).", [], 422);
            }

            $role->delete();

            return $this->sendResponse(new RoleResource($role), "Role has been deleted.", 200);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }


    }

    public function getRole($id){
        try{
            $role = Role::find($id);

            if (!isset($role)) {
                return $this->sendError("Role not found" , $errorMessages = [], 404);
            }

            return $this->sendResponse(new RoleResource($role), "Record found.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }
    }

    /**
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAllRoles(Request $request){
        $roles = null;

        if($request->has('search')){
            $roles = Role::where('name', 'LIKE', '%' . $request->search . '%')
                ->paginate();
        }
        else{
            $roles = Role::paginate();
        }

        $roleArray = (RoleResource::collection($roles)->toResponse(app('request')))->getData(True);

        return $this->sendPaginatedResponse(
            $roleArray,
            (count($roleArray['data']) == 0) ? "No records found" : "Records found."
        );

    }

    // public function exportRoleData(Request $request)
    // {
    //     $this->authorize('view role');

    //     $roles = null;

    //     $query = Role::select(
    //         'id as ID',
    //         'name as Name',
    //     );

    //     if ($request->has('search')) {
    //         $query->where('name', 'LIKE', '%' . $request->search . '%');
    //     }

    //     $roles = $query->get()->toArray();

    //     $headerArray = isset($roles[0]) ? array_keys($roles[0]) : [];

    //     $export = new ExcelExport($roles, $headerArray);

    //     return Excel::download($export, 'data.xlsx');
    // }

    public function assignPermissionsToRole(Request $request){
        $this->authorize('update role');

        try{
            $validator = Validator::make($request->all(),[
                'roleId' => 'required|exists:roles,id',
                'permissions' => 'required'
            ]);

            if($validator->fails()){
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);

            }

            $role = Role::find($request->roleId);

            $permissionsArray = Permission::findMany($request->permissions);

            $role->syncPermissions($permissionsArray);

            return $this->sendResponse(new RoleResource($role), "Permission(s) ".$permissionsArray->count()." have been assigned to this role.");

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }
    }

    public function assignRolesToUser(Request $request){
        $this->authorize('update user');

        try{
            $validator = Validator::make($request->all(),[
                'userId' => 'required | exists:users,id,deleted_at,NULL',
                'roles' => 'required'
            ]);

            if($validator->fails()){
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);

            }

            $user = User::find($request->userId);

            $rolesArray = Role::findMany($request->roles);

            $user->syncRoles($rolesArray);

            return $this->sendResponse(new UserResource($user), "Roles(s) ".$rolesArray->count()." have been assigned to this user.");

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }
    }

    public function getUser($id){
        try{
            $user = User::find($id);

            if (!isset($user)) {
                return $this->sendError("User not found" , $errorMessages = [], 404);
            }

            return $this->sendResponse(new UserResource($user), "Record found.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }
    }

    public function getUserByUsername($username){
        try{
            $user = User::where('username', $username)->first();

            if (!isset($user)) {
                return $this->sendError("User not found" , $errorMessages = [], 404);
            }

            return $this->sendResponse(new UserResource($user), "Record found.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() ,[], 500, $e);
        }
    }

    public function clearCache()
    {
        Artisan::call('cache:forget spatie.permission.cache');

        Artisan::call('cache:clear');

        return "Cache is cleared";
    }

}
