<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsletterController;

Route::post('/', [NewsletterController::class, 'store']);