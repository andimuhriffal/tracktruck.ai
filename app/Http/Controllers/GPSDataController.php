<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GPSData;
use Illuminate\Support\Facades\Log;

class GPSDataController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Received request:', $request->all());

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'required|numeric',
            'status' => 'required|string',
        ]);

        try {
            $data = new GPSData();
            $data->latitude = $request->latitude;
            $data->longitude = $request->longitude;
            $data->speed = $request->speed;
            $data->status = $request->status;
            $data->timestamp = now();
            $data->save();

            Log::info('Data saved successfully');
            return response()->json(['message' => 'Data saved successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error saving data: ' . $e->getMessage());
            return response()->json(['message' => 'Error saving data'], 500);
        }
    }

    public function show()
    {
        $latestData = GPSData::latest()->first();
        return response()->json($latestData);
    }
}