<?php

namespace App\Http\Controllers;

use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view region');

        $regions = null;

        if ($request->has('search')) {
            $regions = Region::where('name', 'LIKE', '%' . $request->search . '%')
                ->paginate();
        } else {
            $regions = Region::paginate();
        }

        $regionsArray = (RegionResource::collection($regions)->toResponse(app('request')))->getData(True);

        return $this->sendPaginatedResponse(
            $regionsArray,
            (count($regionsArray['data']) == 0) ? "No records found" : "Records found."
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
        $this->authorize('create region');

        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:regions,name',
            ]);

            if ($validator->fails()) {
                return $this->sendError("The given data was invalid.", $validator->errors(), 422);
            }

            $region = Region::create([
                'name' => $request->input('name'),
            ]);

            // $region->code = $region->getCode();
            $region->save();

            return $this->sendResponse(new RegionResource($region), "Region has been created.", 201);
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
        $this->authorize('view region');

        $region = Region::find($id);

        if (!isset($region)) {
            return $this->sendError("Region not found", [], 404);
        }

        return $this->sendResponse(
            new RegionResource($region),
            !isset($region) ? "No record found" : "Record found."
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
        $this->authorize('update region');

        $region = Region::find($id);

        if (!isset($region)) {
            return $this->sendError("Region not found", [], 404);
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

            $region->name = $request->input('name');

            $region->save();

            return $this->sendResponse(new RegionResource($region), "Region has been updated.", 201);
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
        $this->authorize('delete region');

        try {
            $region = Region::find($id);

            if (!isset($region)) {
                return $this->sendError("Region not found", $errorMessages = [], 404);
            }

            // if($company->supplierContracts()->exists()){
            //     return $this->sendError("Company has been referenced. It cannot be deleted.", $errorMessages = [], 409);
            // }

            $region->delete();

            return $this->sendResponse(new RegionResource($region), "Region has been deleted.", 200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500, $e);
        }
    }
}
