<?php

use Illuminate\Support\Facades\Route;

Route::get("/health-check", function () {
  return sprintf("%s: OK", config("app.name"));
});
