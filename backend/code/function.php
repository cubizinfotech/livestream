<?php

    function loadEnv($path) {
        if (!file_exists($path)) {
            throw new Exception(".env file not found");
        }
        
        $variables = parse_ini_file($path);

        foreach ($variables as $key => $value) {
            putenv("$key=$value");
        }
    }

?>
