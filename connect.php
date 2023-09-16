<?php

function connect() 
{
    $connect = new PDO("mysql:host=localhost;dbname=timado1p_numeracion", "timado1", "timado1p");
   // $connect = new PDO("mysql:host=localhost;dbname=timado1p_numeracion", "timado1p_usuario", "Practicas2021");
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );/**Para php 8, php< no hace falta */
 
    return $connect;
}
