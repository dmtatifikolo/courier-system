<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view location');

        $locations = null;

        if ($request->has('search')) {
            $locations = Location::where('name', 'LIKE', '%' . $request->search . '%')
                ->paginate();
        } else {
            $locations = Location::paginate();
        }

        $locationsArray = (LocationResource::collection($locations)->toResponse(app('request')))->getData(True);

        return $this->sendPaginatedResponse(
            $locationsArray,
            (count($locationsArray['data']) == 0) ? "No records found" : "Records found."
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create location');

        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:locations,name',
            ]);

            if ($validator->fails()) {
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);
            }

            $location = Location::create([
                'name' => $request->input('name'),
            ]);

            // $location->code = $location->getCode();
            $location->save();

            return $this->sendResponse(new LocationResource($location), "Location has been created.", 201);
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
        $this->authorize('view location');

        $location = Location::find($id);

        if (!isset($location)) {
            return $this->sendError("Location not found", [], 404);
        }

        return $this->sendResponse(
            new LocationResource($location),
            !isset($location) ? "No record found" : "Record found."
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
        $this->authorize('update location');

        $location = Location::find($id);

        if (!isset($location)) {
            return $this->sendError("Location not found", [], 404);
        }

        // if($company->supplierContracts()->exists()){
        //     return $this->sendError("Company has been referenced. It cannot be updated.", $errorMessages = [], 409);
        // }

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);
            }

            $location->name = $request->input('name');

            $location->save();

            return $this->sendResponse(new LocationResource($location), "Location has been updated.", 201);
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
        $this->authorize('delete location');

        try {
            $location = Location::find($id);

            if (!isset($location)) {
                return $this->sendError("location not found", $errorMessages = [], 404);
            }

            // if($company->supplierContracts()->exists()){
            //     return $this->sendError("Company has been referenced. It cannot be deleted.", $errorMessages = [], 409);
            // }

            $location->delete();

            return $this->sendResponse(new LocationResource($location), "location has been deleted.", 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500, $e);
        }
    }
}
