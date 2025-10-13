<?php
$curl = curl_init();


curl_setopt_array($curl, [
    CURLOPT_URL => "https://rajaongkir.komerce.id/api/v1/destination/district/1",
    CURLOPT_HTTPHEADER => ["accept: application/json","key: 8F3pm71P955d0ab5aea9e6e8IdRXmHne"],
    CURLOPT_RETURNTRANSFER => true,
]);


$response = curl_exec($curl);
curl_close($curl);

$data=json_decode($response,true);

echo "<pre>";
print_r($data);
echo "</pre>";
?>