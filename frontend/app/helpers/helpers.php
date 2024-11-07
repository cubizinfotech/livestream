<?php

if (!function_exists('callPostAPI')) {
    function callPostAPI($url, $data)
    {
        // Initialize cURL session
        $ch = curl_init($url);

        // Configure cURL options for a POST request
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Send data as URL-encoded form
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response instead of outputting it
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (use with caution)

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $res = [
                'status' => false,
                'message' => "cURL Error: " . curl_error($ch),
            ];
        } else {
            $res = [
                'status' => true,
                'message' => $response,
            ];
        }

        // Close the cURL session
        curl_close($ch);

        return $res;
    }
}

if (!function_exists('callFileAPI')) {
    function callFileAPI($url, $destinationPath)
    {
        $ch = curl_init($url);
        $fp = fopen($destinationPath, 'wb');

        // Set Curl options for large file transfer
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0); // Set timeout to 0 for no timeout
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 8192); // Set buffer size to 8KB
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $res = [
                'status' => false,
                'message' => "cURL Error: " . curl_error($ch),
            ];
        } else {
            $res = [
                'status' => true,
                'message' => $response,
            ];
        }

        // Close resources
        curl_close($ch);
        fclose($fp);

        return $res;
    }
}
