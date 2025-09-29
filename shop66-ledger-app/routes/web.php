<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Redirect to Filament admin panel
    return redirect('/admin');
});
