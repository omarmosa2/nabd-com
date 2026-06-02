<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function index()
    {
        $clinics = Clinic::all();
        return response()->json(['data' => $clinics]);
    }

    public function show(Clinic $clinic)
    {
        return response()->json(['data' => $clinic]);
    }
}
