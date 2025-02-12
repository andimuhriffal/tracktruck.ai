<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FuelLevelController;
use App\Http\Controllers\Api\GPSController;
use App\Http\Controllers\GPSDataController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/gps-data', [GPSDataController::class, 'store']);
Route::get('/gps-data', [GPSDataController::class, 'show']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('throttle:100,1')->group(function () {
    Route::post('/detect', function (Request $request) {
        // Data yang diterima dari request
        $driverDetected = $request->input('driver_detected');
        $passengerDetected = $request->input('passenger_detected');
        $beltStatus = $request->input('belt_status');
        $drowsinessDetected = $request->input('drowsiness_detected');

        // Pengecekan apakah data yang sama sudah ada
        $existingDetection = DB::table('detections')
            ->where('driver_detected', $driverDetected)
            ->where('passenger_detected', $passengerDetected)
            ->where('belt_status', $beltStatus)
            ->where('drowsiness_detected', $drowsinessDetected)
            ->first();

        // Jika data belum ada, lakukan insert
        if (!$existingDetection) {
            DB::table('detections')->insert([
                'driver_detected' => $driverDetected,
                'passenger_detected' => $passengerDetected,
                'belt_status' => $beltStatus,
                'drowsiness_detected' => $drowsinessDetected,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['status' => 'success']);
        }

        // Jika data sudah ada, kembalikan respons bahwa data sudah ada
        return response()->json(['status' => 'duplicate']);
    });
});


