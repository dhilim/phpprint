<?php

// Initialize a file URL to the variable
// $url = 'https://www.africau.edu/images/default/sample.pdf';

$url = htmlspecialchars($_GET["url"]);
$printer = isset($_GET["printer"]) ? htmlspecialchars($_GET["printer"]) : null;

if (!$url) {
    echo 'no file url available!';
    exit;
}

$file_name = 'file.pdf';

// $printerName = "Microsoft Print to PDF";
$printerName = $printer ?? "struk_printer";

// Use file_get_contents() function to get the file
// from url and use file_put_contents() function to
// save the file by using base name
if (file_put_contents($file_name, file_get_contents($url))) {
    // echo "File downloaded successfully. Prepare to print...";
    echo json_response(200, 'Print success');

    try {
        system('PDFtoPrinter.exe file.pdf "' . $printerName . '" pages=1-1');
    } catch (\Throwable $th) {
        echo json_encode("message: " . $th->getMessage());
    }
} else {
    // echo "File downloading failed.";
    echo json_response(400, "Trouble with downloading file url");
}

function json_response($code = 200, $message = null)
{
    // clear the old headers
    header_remove();
    // set the actual code
    cors();
    http_response_code($code);
    // set the header to make sure cache is forced
    header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
    // treat this as json
    header('Content-Type: application/json');
    $status = array(
        200 => '200 OK',
        400 => '400 Bad Request',
        422 => 'Unprocessable Entity',
        500 => '500 Internal Server Error'
    );
    // ok, validation error, or failure
    header('Status: ' . $status[$code]);
    // return the encoded json
    return json_encode(array(
        'status' => $code < 300, // success or not?
        'message' => $message
    ));
}

function cors()
{
    // Specify domains from which requests are allowed
    header('Access-Control-Allow-Origin: *');

    // Specify which request methods are allowed
    header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

    // Additional headers which may be sent along with the CORS request
    header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');

    // Set the age to 1 day to improve speed/caching.
    header('Access-Control-Max-Age: 86400');

    // Exit early so the page isn't fully loaded for options requests
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
        exit();
    }
}
