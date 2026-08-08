<?php
require __DIR__ . '/modules/servers/virtutel_nbn/pages/site-shell.php';
http_response_code(404);
kx_site_render('notfound');
