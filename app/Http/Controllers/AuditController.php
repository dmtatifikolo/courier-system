<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Http\Resources\AuditResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Twilio\Rest\Client;

class AuditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view audit log');

        $audits = null;

        if ($request->has('search')) {
            $audits = \OwenIt\Auditing\Models\Audit::where('user_type', 'LIKE', '%' . $request->search . '%')
                ->orWhere('event', 'LIKE', '%' . $request->search . '%')
                ->orWhere('auditable_type', 'LIKE', '%' . $request->search . '%')
                ->orWhereHas('user', function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate();
        } else {
            $audits = \OwenIt\Auditing\Models\Audit::with('user')
                ->orderBy('created_at', 'desc')->paginate();
        }

        $auditsArray = (AuditResource::collection($audits)->toResponse(app('request')))->getData(True);

        return $this->sendPaginatedResponse(
            $auditsArray,
            (count($auditsArray['data']) == 0) ? "No records found" : "Records found."
        );
    }

    public function exportData(Request $request)
    {
        $this->authorize('view audit log');

        $audits = null;

        // $query = Role::select(
        //     'id as ID',
        //     'name as Name',
        // );

        // if ($request->has('search')) {
        //     $query->where('name', 'LIKE', '%' . $request->search . '%');
        // }

        // $roles = $query->get()->toArray();

        if ($request->has('search')) {
            $audits = \OwenIt\Auditing\Models\Audit::where('user_type', 'LIKE', '%' . $request->search . '%')
                ->orWhere('event', 'LIKE', '%' . $request->search . '%')
                ->orWhere('auditable_type', 'LIKE', '%' . $request->search . '%')
                ->orWhereHas('user', function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->get()->toArray();
        } else {
            $audits = \OwenIt\Auditing\Models\Audit::with('user')
                ->orderBy('created_at', 'desc')->get()->toArray();
        }

        $headerArray = isset($audits[0]) ? array_keys($audits[0]) : [];

        $export = new ExcelExport($audits, $headerArray);

        return Excel::download($export, 'data.xlsx');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view audit log');

        $audit = \OwenIt\Auditing\Models\Audit::with('user')->find($id);

        if (!isset($audit)) {
            return $this->sendError("Item not found", $errorMessages = [], 404);
        }

        return $this->sendResponse(
            new AuditResource($audit),
            !isset($audit) ? "No record found" : "Record found."
        );
    }
}
