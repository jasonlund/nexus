<?php
Route::get('/test', function() {
    $html = '<p></p><div class="noembed-iframe"><iframe width=" 480" height="270" src="https://www.youtube.com/embed/dQw4w9WgXcQ?feature=oembed" frameborder="0" allowfullscreen="allowfullscreen"></iframe></div><p></p><p></p><div class="noembed-iframe"><iframe src="https://player.twitch.tv/?%21branding=&amp;autoplay=false&amp;video=v437403455" width="500" height="281" frameborder="0" scrolling="no" allowfullscreen=""></iframe></div><p></p>';

    dd(\App\Services\PurifyService::clean($html));
});
