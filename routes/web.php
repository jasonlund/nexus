<?php

Route::get('/test', function() {
    throw new Exception('this is a test');
});

Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});
