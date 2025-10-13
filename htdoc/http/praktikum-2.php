<?php


$request = curl_init();

curl_setopt_array(
    $request,
    [
        CURLOPT_URL => "https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost",
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => [
            "key:8F3pm71P955d0ab5aea9e6e8IdRXmHne",
            "content-type:application/x-www-form-urlencoded"
        ],
        CURLOPT_POSTFIELDS => http_build_query([
            "origin"=>"2",
            "destination"=>"12",
            "weight"=>"1700",
            "courier"=>"jne:sicepat:ide:sap:jnt:ninja",
            "price"=>"lowest"
        ]),
        CURLOPT_RETURNTRANSFER => true,
    ]
);


$response = curl_exec($request);
$error = curl_error($request);

curl_close($request);
if ($error) {
    echo "cURL Error #:" . $error;
}else{
    $data = json_decode($response,true);
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

?>