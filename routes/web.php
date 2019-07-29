<?php

Route::get('/test', function() {
    abort(500, 'this is a test');
});

Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});
