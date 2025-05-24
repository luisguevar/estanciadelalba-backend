<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\EstanciaController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\HuespedController;
use App\Http\Controllers\LibroReclamacionController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\TarifaHoteleraController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\web\ReservaController as WebReservaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([

    /*  'middleware' => 'auth:api', */
    'prefix' => 'auth'

], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
});


Route::group(['prefix' => 'usuarios'], function ($router) {

    Route::group(['prefix' => 'admin'], function () {
        Route::get('/all',  [UsuarioController::class, 'index'])->name('index');
        /*  Route::post("/add", "Usuario\UsuarioController@store");
        Route::post("/update/{id}", "Usuario\UsuarioController@update"); */
    });
});


Route::group(['prefix' => 'tarifa-hotelera'], function ($router) {
    Route::get('/all', [TarifaHoteleraController::class, 'index'])->name('tarifa-hotelera.index');
    Route::post('/store', [TarifaHoteleraController::class, 'store'])->name('tarifa-hotelera.store');
    Route::get('/allTiposHabitacion', [TarifaHoteleraController::class, 'indexTipoHabitacion'])->name('tipos-habitaciones.index');
    Route::post('/storeTiposHabitacion', [TarifaHoteleraController::class, 'storeTipoHabitacion'])->name('tipos-habitaciones.store');
    Route::get('/HabitacionesConPrecios', [TarifaHoteleraController::class, 'HabitacionesConPrecios'])->name('tarifa-hotelera.HabitacionesConPrecios');
});

Route::group(['prefix' => 'huesped'], function ($router) {
    Route::get('/all', [HuespedController::class, 'index'])->name('huesped.index');
    Route::get('/show', [HuespedController::class, 'show'])->name('huesped.show');
    Route::put('/update/{id}', [HuespedController::class, 'update'])->name('huesped.update');
    Route::post('/registrarHuespedEstancia', [HuespedController::class, 'registrarHuespedEstancia'])->name('huesped.registrarHuespedEstancia');
    Route::put('/actualizarHuespedEstancia', [HuespedController::class, 'actualizarHuespedEstancia'])->name('huesped.actualizarHuespedEstancia');
});

Route::group(['prefix' => 'pais'], function ($router) {
    Route::get('/all', [PaisController::class, 'index'])->name('pais.index');
});

Route::group(['prefix' => 'estancia'], function ($router) {
    Route::get('/all', [EstanciaController::class, 'index'])->name('estancia.index');
    Route::get('/show', [EstanciaController::class, 'show'])->name('estancia.show');
    Route::put('/update/{id}', [EstanciaController::class, 'update'])->name('estancia.update');
    Route::put('/updatePago', [EstanciaController::class, 'updatePago'])->name('estancia.updatePago');
    Route::post('/guardarPago', [EstanciaController::class, 'guardarPago'])->name('estancia.guardarPago');
});

Route::group(['prefix' => 'reserva'], function ($router) {
    Route::get('/all', [ReservaController::class, 'index'])->name('reserva.index');
    Route::get('/calendarData', [ReservaController::class, 'calendarData'])->name('reserva.calendarData');
    Route::get('/verificarDisponibilidad', [ReservaController::class, 'verificarDisponibilidad'])->name('reserva.verificarDisponibilidad');
});

Route::group(['prefix' => 'recepcion'], function ($router) {
    Route::get('/all', [RecepcionController::class, 'index'])->name('recepcion.index');
    Route::get('/buscadorHuesped', [RecepcionController::class, 'BuscadorHuesped'])->name('buscadorHuesped.index');
    Route::get('/buscarHabitacion', [RecepcionController::class, 'BuscarHabitacion'])->name('buscarHabitacion.index');
    Route::post('/validadorHuesped', [RecepcionController::class, 'ValidadorHuesped'])->name('validadorHuesped.index');
    Route::post('/registrarIngreso', [RecepcionController::class, 'RegistrarIngreso'])->name('registrarIngreso.index');
    Route::get('/ObtenerPrecioHab', [RecepcionController::class, 'ObtenerPrecioHab'])->name('buscarHabitacion.ObtenerPrecioHab');
    Route::put('/putCambioHabitacion', [RecepcionController::class, 'putCambioHabitacion'])->name('putCambioHabitacion.index');
    Route::post('/postEstanciaHabitacion', [RecepcionController::class, 'postEstanciaHabitacion'])->name('postEstanciaHabitacion.index');
    Route::put('/putEstanciaHabitacion', [RecepcionController::class, 'putEstanciaHabitacion'])->name('putEstanciaHabitacion.index');
});

Route::group(['prefix' => 'panel'], function ($router) {
    Route::get('/all', [PanelController::class, 'index'])->name('panel.index');
    Route::get('/allingresos', [PanelController::class, 'resumenIngresos'])->name('panel.resumenIngresos');
});

Route::group(['prefix' => 'habitacion'], function ($router) {

    Route::get('/show', [HabitacionController::class, 'show'])->name('habitacion.show');
    Route::post('/post', [HabitacionController::class, 'store'])->name('habitacion.store');
    Route::put('/put', [HabitacionController::class, 'update'])->name('habitacion.update');
    Route::post('/postTemporada', [HabitacionController::class, 'postTemporada'])->name('habitacion.postTemporada');
    Route::post('/postHabitacionWeb', [HabitacionController::class, 'postHabitacionWeb'])->name('habitacion.postHabitacionWeb');
});


Route::group(['prefix' => 'reporte'], function ($router) {
    Route::get('/allUsuarios', [ReporteController::class, 'allUsuarios'])->name('reporte.allUsuarios');
    Route::get('/allHabitaciones', [ReporteController::class, 'allHabitaciones'])->name('reporte.allHabitaciones');
    Route::get('/ingreso-salidas', [ReporteController::class, 'ingresoSalidas'])->name('reporte.ingreso-salidas');
    Route::get('/cambios-habitacion', [ReporteController::class, 'cambiosHabitacion'])->name('reporte.cambios-habitacion');
    Route::get('/huesped-responsable', [ReporteController::class, 'huespedResponsable'])->name('reporte.huesped-responsable');
    Route::get('/ingresos-tipo', [ReporteController::class, 'ingresosTipoHabitacion'])->name('reporte.ingresos-tipo');
    Route::get('/estado-habitaciones', [ReporteController::class, 'estadosHabitacion'])->name('reporte.estado-habitaciones');
    Route::get('/cancelaciones', [ReporteController::class, 'cancelaciones'])->name('reporte.cancelaciones');
    Route::post('/generar-pdf', [ReporteController::class, 'descargarPDF']);
});

Route::group(['prefix' => 'facturacion'], function ($router) {
    Route::post('/generar-comprobante', [FacturacionController::class, 'GenerarComprobante']);
    Route::post('/obtener-comprobante', [FacturacionController::class, 'ObtenerComprobante']);
    Route::get('/buscar-estancias', [FacturacionController::class, 'BuscarEstancias']);
});

Route::group(['prefix' => 'web'], function ($router) {
    Route::group(['prefix' => 'reserva'], function ($router) {

        Route::get('/habitaciones', [WebReservaController::class, 'habitaciones'])->name('habitaciones.index');
        Route::get('/all', [WebReservaController::class, 'index'])->name('panel.index');
        Route::post('/save', [WebReservaController::class, 'RegistrarReserva'])->name('panel.RegistrarReserva');
        Route::get('/showHabitaciones', [WebReservaController::class, 'ListarHabitaciones'])->name('web.ListarHabitaciones');
        Route::post('/ConfirmarReserva', [WebReservaController::class, 'ConfirmarReserva'])->name('web.ConfirmarReserva');
        Route::post('/PostLibroReclamaciones', [WebReservaController::class, 'PostLibroReclamaciones'])->name('web.PostLibroReclamaciones');
    });
});

Route::group(['prefix' => 'comprobante'], function ($router) {

    Route::get('/allComprobantes', [ComprobanteController::class, 'index'])->name('reporte.allComprobantes');
});

Route::group(['prefix' => 'reclamos'], function ($router) {
    Route::get('/allReclamos', [LibroReclamacionController::class, 'index'])->name('reclamos.index');
    Route::post('/registrar', [LibroReclamacionController::class, 'store'])->name('reclamos.store');
    Route::get('/{id}', [LibroReclamacionController::class, 'show'])->name('reclamos.show');
    Route::put('/{id}/estado', [LibroReclamacionController::class, 'update'])->name('huesped.update');
    Route::get('/{id}/descargar', [LibroReclamacionController::class, 'pdf'])->name('reclamos.pdf');
    Route::put('/enviar-respuesta', [LibroReclamacionController::class, 'enviarRespuestaPdf'])->name('reclamos.enviarRespuestaPdf');
});
